<?php
/**
 * Mega Menu – registers a dedicated "Mega Menu" post type (its own admin
 * screen under Omega Design > Mega Menus: title, block editor content,
 * per-menu Custom CSS - the same shape as an old-school walker-based mega
 * menu plugin, but wired into the block-based Site Editor Navigation
 * instead of a classic nav-menu walker), and on the front end turns any
 * navigation-link/submenu with one attached (via the Inspector control and
 * "+" inserter entry added in assets/js/editor.js) into a hover/click
 * trigger for that Mega Menu's content.
 *
 * Which Mega Menu a given nav item opens is entirely editor-driven: no
 * label matching, no PHP list to keep in sync. `megaMenuPattern` is a plain
 * block attribute ("mega_menu:{post_id}") read straight off $block['attrs']
 * the same way includes/core/responsive_styles.php already reads
 * omegaHover/dimensions off Group/Columns with no server-side attribute
 * registration required.
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class megamenu {

    private static $instance = null;

    const POST_TYPE       = 'mega_menu';
    const META_CSS        = '_omega_mega_menu_css';
    const CSS_NONCE       = 'omega_mega_menu_css_nonce';
    const SEED_MAP_OPTION = 'omega_megamenu_seed_map';

    /**
     * Panels collected while rendering the current request's main site
     * navigation, keyed by panel class so the same Mega Menu picked on two
     * different nav items collapses into a single rendered panel.
     * panel_class => "mega_menu:{post_id}"
     */
    private $collected_panels = [];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'register_post_type']);
        add_action('init', [$this, 'maybe_seed_examples'], 20);
        add_action('add_meta_boxes_' . self::POST_TYPE, [$this, 'add_css_meta_box']);
        add_action('save_post_' . self::POST_TYPE, [$this, 'save_css_meta']);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('enqueue_block_assets', [$this, 'enqueue_assets']);
        add_filter('render_block', [$this, 'collect_trigger'], 10, 2);
        add_filter('render_block', [$this, 'append_panels'], 20, 2);
        // Classic menu support (Appearance > Menus / any wp_nav_menu()
        // location), for sites/menus that don't use the block Navigation.
        add_action('admin_head-nav-menus.php', [$this, 'add_classic_menu_metabox']);
        add_filter('wp_nav_menu_objects', [$this, 'process_classic_menu_items']);
        add_filter('nav_menu_css_class', [$this, 'add_classic_trigger_class'], 10, 2);
        add_filter('walker_nav_menu_start_el', [$this, 'inject_classic_submenu_toggle'], 10, 2);

        // The old label-matching system auto-created a "Mega Menu" wp_navigation
        // post and tracked it in this option; both are unused dead weight now
        // that panels are attribute-driven, so clean them up once.
        add_action('init', [$this, 'cleanup_legacy_navigation']);
    }

    public function init() {}

    /* ── Post type ──────────────────────────────────────────────── */

    /**
     * Admin-only (all capabilities map to manage_options): the Custom CSS
     * field below is trusted, unsanitized front-end output - same trust
     * model as core's own Additional CSS in the Customizer - so only
     * genuine site admins should be able to create or edit these.
     */
    public function register_post_type() {
        register_post_type(self::POST_TYPE, [
            'labels' => [
                'name'                => __('Mega Menus', 'omega-design'),
                'singular_name'       => __('Mega Menu', 'omega-design'),
                'add_new'             => __('Add New', 'omega-design'),
                'add_new_item'        => __('Add New Mega Menu', 'omega-design'),
                'edit_item'           => __('Edit Mega Menu', 'omega-design'),
                'new_item'            => __('New Mega Menu', 'omega-design'),
                'view_item'           => __('View Mega Menu', 'omega-design'),
                'search_items'        => __('Search Mega Menus', 'omega-design'),
                'not_found'           => __('No mega menus found. Create one, then attach it to a navigation item in the Site Editor.', 'omega-design'),
                'menu_name'           => __('Mega Menus', 'omega-design'),
            ],
            'public'          => false,
            'show_ui'         => true,
            // Not attached automatically (as a string parent slug) because a
            // CPT's auto-added submenu registers before menus.php's own
            // add_submenu_page() calls run, making it the *first* submenu -
            // which also hijacks the top-level "Omega Design" link itself to
            // point at this screen instead of the Dashboard. menus.php adds
            // this one explicitly instead, so it lands after "Menus" and
            // "Dashboard" stays first (and stays the top-level link target).
            'show_in_menu'    => false,
            'show_in_rest'    => true,
            'supports'        => ['title', 'editor', 'revisions'],
            'rewrite'         => false,
            'map_meta_cap'    => true,
            // A dedicated singular/plural pair (not the default 'post'),
            // so WP auto-derives unique meta-cap names for edit_post /
            // read_post / delete_post ('edit_omega_mega_menu', etc.).
            // Reusing a real global capability name (e.g. 'manage_options')
            // for those singular slots corrupts WordPress's own
            // $post_type_meta_caps lookup table used by every map_meta_cap()
            // call site-wide, since that table is keyed by capability value
            // -> for those three slots specifically, NOT by post type. That
            // silently turns every current_user_can('manage_options') check
            // anywhere on the site into a per-post capability check with no
            // post to check against, which always resolves to false - taking
            // down every admin-only menu on the site, not just this one. The
            // *plural* caps below aren't part of that lookup table, so
            // pointing them at 'manage_options' is safe.
            'capability_type' => ['omega_mega_menu', 'omega_mega_menus'],
            'capabilities'    => [
                'edit_posts'             => 'manage_options',
                'edit_others_posts'      => 'manage_options',
                'edit_published_posts'   => 'manage_options',
                'edit_private_posts'     => 'manage_options',
                'publish_posts'          => 'manage_options',
                'read_private_posts'     => 'manage_options',
                'create_posts'           => 'manage_options',
                'delete_posts'           => 'manage_options',
                'delete_others_posts'    => 'manage_options',
                'delete_published_posts' => 'manage_options',
                'delete_private_posts'   => 'manage_options',
            ],
        ]);
    }

    /**
     * One-time convenience: turns the theme's 5 built-in "Mega Menu"
     * category patterns into ready-to-use Mega Menu posts, so there's
     * something to attach immediately instead of a blank list. Runs at
     * 'init' priority 20 so includes/customizer/patterns.php (priority 10)
     * has already registered them by the time this reads the registry.
     */
    public function maybe_seed_examples() {
        if (get_option('omega_megamenu_seeded_v1')) {
            // Already seeded under an earlier version of this method, from
            // before SEED_MAP_OPTION existed at all - back-fill it once by
            // matching titles, so parts/header.html's "mega_menu:seed:..."
            // aliases still resolve on a site that activated the theme
            // before this fix, not just on a fresh install.
            $this->maybe_backfill_seed_map();
            return;
        }
        update_option('omega_megamenu_seeded_v1', 1);

        if (!class_exists('WP_Block_Patterns_Registry')) {
            return;
        }

        $registry = \WP_Block_Patterns_Registry::get_instance();
        $seed_map = get_option(self::SEED_MAP_OPTION, []);

        foreach ($registry->get_all_registered() as $pattern) {
            if (empty($pattern['categories']) || !in_array('omega-design-megamenu', $pattern['categories'], true)) {
                continue;
            }

            $post_id = wp_insert_post([
                'post_type'    => self::POST_TYPE,
                'post_status'  => 'publish',
                'post_title'   => preg_replace('/^Mega Menu\s*-\s*/', '', $pattern['title']),
                'post_content' => $pattern['content'],
            ]);

            // Keyed by the pattern's own stable name/slug (e.g.
            // "omega-design/megamenu-columns", in $pattern['name'] - NOT
            // the array's own key, which get_all_registered() indexes
            // numerically, not by slug), not the post ID that
            // wp_insert_post() just returned - auto-increment IDs depend
            // on however many other posts already exist in whichever
            // database this runs against, so a fresh install elsewhere
            // would get entirely different numbers. Anything that needs
            // to reference "the seeded Columns mega menu" portably (e.g.
            // parts/header.html's own demo nav links) resolves through
            // this map via resolve_seed_alias() instead of a hardcoded ID.
            if ($post_id && !is_wp_error($post_id) && !empty($pattern['name'])) {
                $seed_map[$pattern['name']] = $post_id;
            }
        }

        update_option(self::SEED_MAP_OPTION, $seed_map);
    }

    /**
     * One-time migration for a site that already ran the old
     * maybe_seed_examples() before SEED_MAP_OPTION was introduced: matches
     * each Mega Menu pattern's title against existing mega_menu posts to
     * rebuild the same slug => post ID map fresh seeding now produces
     * directly. `false !== get_option(..., false)` distinguishes "never
     * built" from "built, legitimately empty" so this only ever runs once.
     */
    private function maybe_backfill_seed_map() {
        if (false !== get_option(self::SEED_MAP_OPTION, false)) {
            return;
        }

        if (!class_exists('WP_Block_Patterns_Registry')) {
            return;
        }

        $registry = \WP_Block_Patterns_Registry::get_instance();
        $seed_map = [];

        foreach ($registry->get_all_registered() as $pattern) {
            if (empty($pattern['categories']) || !in_array('omega-design-megamenu', $pattern['categories'], true) || empty($pattern['name'])) {
                continue;
            }

            $title   = preg_replace('/^Mega Menu\s*-\s*/', '', $pattern['title']);
            $matches = get_posts([
                'post_type'      => self::POST_TYPE,
                'post_status'    => 'publish',
                'title'          => $title,
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ]);

            if (!empty($matches)) {
                $seed_map[$pattern['name']] = (int) $matches[0];
            }
        }

        update_option(self::SEED_MAP_OPTION, $seed_map);
    }

    /**
     * Resolves a "mega_menu:seed:{pattern-slug}" value (used only by
     * parts/header.html's demo nav links) to the real "mega_menu:{id}"
     * value for whatever this install actually seeded that pattern as -
     * see maybe_seed_examples(). Any other value passes through
     * unchanged. Returns '' if seeding hasn't run yet or that particular
     * pattern was never registered, so callers can treat it the same as
     * "no Mega Menu attached" rather than erroring.
     */
    private function resolve_seed_alias($value) {
        if (0 !== strpos($value, 'mega_menu:seed:')) {
            return $value;
        }

        $pattern_slug = substr($value, strlen('mega_menu:seed:'));
        $seed_map     = get_option(self::SEED_MAP_OPTION, []);

        return isset($seed_map[$pattern_slug]) ? 'mega_menu:' . (int) $seed_map[$pattern_slug] : '';
    }

    public function cleanup_legacy_navigation() {
        $nav_id = (int) get_option('omega_megamenu_nav_id');
        if ($nav_id && 'trash' !== get_post_status($nav_id)) {
            wp_trash_post($nav_id);
        }
        delete_option('omega_megamenu_nav_id');
    }

    /* ── Per-menu Custom CSS ────────────────────────────────────── */

    public function add_css_meta_box() {
        add_meta_box(
            'omega_mega_menu_css',
            __('Custom CSS for this Mega Menu', 'omega-design'),
            [$this, 'render_css_meta_box'],
            self::POST_TYPE,
            'normal',
            'default'
        );
    }

    public function render_css_meta_box($post) {
        wp_nonce_field(self::CSS_NONCE, self::CSS_NONCE);
        $css         = get_post_meta($post->ID, self::META_CSS, true);
        $panel_class = 'omega-panel--' . $post->ID;
        ?>
        <p class="description">
            <?php
            printf(
                /* translators: %s: this mega menu's panel CSS class */
                esc_html__('Optional. Loaded on the front end only when this mega menu is showing. Scope your selectors to %s if you only want a rule to apply here.', 'omega-design'),
                '<code>.' . esc_html($panel_class) . '</code>'
            );
            ?>
        </p>
        <textarea name="omega_mega_menu_css" style="width:100%;height:200px;font-family:monospace;" spellcheck="false"><?php echo esc_textarea($css); ?></textarea>
        <?php
    }

    public function save_css_meta($post_id) {
        if (!isset($_POST[self::CSS_NONCE]) || !wp_verify_nonce($_POST[self::CSS_NONCE], self::CSS_NONCE)) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        if (isset($_POST['omega_mega_menu_css'])) {
            update_post_meta($post_id, self::META_CSS, wp_unslash($_POST['omega_mega_menu_css']));
        }
    }

    /* ── Assets ─────────────────────────────────────────────────── */

    public function enqueue_assets() {
        // filemtime() as the *primary* version, not a fallback - `$ver ??
        // filemtime(...)` never actually ran filemtime() at all, since
        // OMEGA_DESIGN_VERSION is a constant that's never null, so every
        // CSS/JS edit here kept enqueuing under the exact same unchanged
        // version string and browsers had every reason to keep serving a
        // stale cached copy instead of re-fetching. Matches hooks.php's own
        // asset_version() helper, which gets this right already.

        $css_path = get_template_directory() . '/assets/css/megamenu.css';
        if (file_exists($css_path)) {
            wp_enqueue_style(
                'omega-design-megamenu',
                get_template_directory_uri() . '/assets/css/megamenu.css',
                [],
                filemtime($css_path)
            );
        }

        if (!is_admin()) {
            $js_path = get_template_directory() . '/assets/js/megamenu.js';
            if (file_exists($js_path)) {
                wp_enqueue_script(
                    'omega-design-megamenu',
                    get_template_directory_uri() . '/assets/js/megamenu.js',
                    [],
                    filemtime($js_path),
                    true
                );
            }
        }
    }

    /* ── Trigger detection ─────────────────────────────────────────
     * Runs at priority 10, before append_panels (priority 20), so every
     * nav-link/submenu inside the main navigation has already registered
     * its panel by the time the navigation block's own filter fires.
     */

    public function collect_trigger($block_content, $block) {
        $name = $block['blockName'] ?? '';

        if ('core/navigation-link' !== $name && 'core/navigation-submenu' !== $name) {
            return $block_content;
        }

        $value = trim((string) ($block['attrs']['megaMenuPattern'] ?? ''));
        if ('' === $value) {
            return $block_content;
        }

        $value = $this->resolve_seed_alias($value);
        if ('' === $value) {
            return $block_content;
        }

        $panel_class = $this->panel_class($value);
        $this->collected_panels[$panel_class] = $value;

        return preg_replace(
            '/class="(wp-block-navigation-item\b[^"]*)"/',
            'class="$1 omega-megamenu-trigger ' . esc_attr($panel_class) . '"',
            $block_content,
            1
        );
    }

    /* ── Panel output ───────────────────────────────────────────────
     * Appended to the main header navigation's own rendered output, so no
     * separate template-part area is needed - the panels always live right
     * where the nav that triggers them lives.
     */

    public function append_panels($block_content, $block) {
        if ('core/navigation' !== ($block['blockName'] ?? '')) {
            return $block_content;
        }

        $classes = $block['attrs']['className'] ?? '';
        if (strpos($classes, 'site-navigation') === false || empty($this->collected_panels)) {
            return $block_content;
        }

        $panels = '';
        foreach ($this->collected_panels as $panel_class => $value) {
            $content = $this->render_panel_content($value);
            if ('' === trim($content)) {
                continue;
            }
            $panels .= '<div class="omega-megamenu-panel ' . esc_attr($panel_class) . '">' . $content . '</div>';
        }

        $this->collected_panels = [];

        if ('' === $panels) {
            return $block_content;
        }

        return $block_content . '<div class="omega-megamenu-panels">' . $panels . '</div>';
    }

    /* ── Classic menu support (Appearance > Menus) ─────────────────
     * A "Mega Menus" box in the classic nav-menus.php screen, the same way
     * core's own "Pages"/"Custom Links" boxes work: check a Mega Menu, hit
     * "Add to Menu". Added as a *top-level* item it's its own trigger.
     * Nested as a *child* of another item, that parent becomes the trigger
     * instead - its other children are dropped so only the Mega Menu's
     * content shows, matching how a Navigation Submenu block with
     * megaMenuPattern set hides its own native submenu list (megamenu.css).
     */

    public function add_classic_menu_metabox() {
        if (!current_user_can('manage_options')) {
            return;
        }
        add_meta_box(
            'omega-mega-menus',
            __('Mega Menus', 'omega-design'),
            [$this, 'render_classic_menu_metabox'],
            'nav-menus',
            'side',
            'default'
        );
    }

    public function render_classic_menu_metabox() {
        $menus = get_posts([
            'post_type'      => self::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'asc',
        ]);

        if (empty($menus)) {
            printf(
                '<p>%s</p>',
                esc_html__('No Mega Menus yet. Create one under Omega Design > Mega Menus first.', 'omega-design')
            );
            return;
        }
        ?>
        <div id="posttype-omega-mega-menu" class="posttypediv">
            <div id="tabs-panel-omega-mega-menu-all" class="tabs-panel tabs-panel-active">
                <ul id="omega-mega-menu-checklist" class="categorychecklist form-no-clear">
                    <?php foreach ($menus as $menu) : $id = (int) $menu->ID; ?>
                        <li>
                            <label class="menu-item-title">
                                <input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="<?php echo esc_attr($id); ?>">
                                <?php echo esc_html($menu->post_title); ?>
                            </label>
                            <input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="custom">
                            <input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="<?php echo esc_attr($menu->post_title); ?>">
                            <input type="hidden" class="menu-item-url" name="menu-item[-1][menu-item-url]" value="<?php echo esc_attr('#mega-menu-' . $id); ?>">
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <p class="button-controls">
                <span class="add-to-menu">
                    <button
                        type="submit"
                        class="button-secondary submit-add-to-menu right"
                        value="<?php esc_attr_e('Add to Menu', 'omega-design'); ?>"
                        name="add-post-type-menu-item"
                        id="submit-posttype-omega-mega-menu"
                    ><?php esc_html_e('Add to Menu', 'omega-design'); ?></button>
                    <span class="spinner"></span>
                </span>
            </p>
        </div>
        <p class="description">
            <?php esc_html_e('Add as a top-level item to use it on its own, or drag it under another item (Bulk/Manual view) to replace that item\'s dropdown with this Mega Menu.', 'omega-design'); ?>
        </p>
        <?php
    }

    private function classic_mega_menu_id($url) {
        if (is_string($url) && preg_match('/^#mega-menu-(\d+)$/', $url, $m)) {
            return (int) $m[1];
        }
        return 0;
    }

    /**
     * Runs once on the full flat item list before the walker builds any
     * HTML. A Mega Menu item nested under another item marks its parent as
     * the trigger and gets dropped along with any other siblings, so the
     * walker never emits a normal sub-menu <ul> there - the parent's own
     * <li> becomes the trigger instead (see the two filters below).
     */
    public function process_classic_menu_items($items) {
        $by_id = [];
        foreach ($items as $item) {
            $by_id[$item->ID] = $item;
        }

        $drop_ids = [];

        foreach ($items as $item) {
            $mega_id = $this->classic_mega_menu_id($item->url);
            if (!$mega_id) {
                continue;
            }

            $parent_id = (int) $item->menu_item_parent;

            if ($parent_id > 0 && isset($by_id[$parent_id])) {
                $by_id[$parent_id]->omega_mega_menu_id = $mega_id;
                foreach ($items as $sibling) {
                    if ((int) $sibling->menu_item_parent === $parent_id) {
                        $drop_ids[$sibling->ID] = true;
                    }
                }
            } else {
                $item->omega_mega_menu_id = $mega_id;
            }
        }

        if (empty($drop_ids)) {
            return $items;
        }

        return array_values(array_filter($items, function ($item) use ($drop_ids) {
            return !isset($drop_ids[$item->ID]);
        }));
    }

    public function add_classic_trigger_class($classes, $item) {
        if (!empty($item->omega_mega_menu_id)) {
            // Stale leftover from before process_classic_menu_items() ran -
            // no children actually remain to open a sub-menu for.
            $classes = array_diff($classes, ['menu-item-has-children']);
            $classes[] = 'omega-megamenu-trigger';
            $classes[] = $this->panel_class('mega_menu:' . (int) $item->omega_mega_menu_id);
        }
        return $classes;
    }

    /**
     * A toggle button for any item with a dropdown (plain sub-menu or Mega
     * Menu), as its own element right after the label - never part of the
     * <a> itself, so tapping the label always just follows its link, and
     * only tapping this arrow expands/collapses the dropdown (mobile;
     * desktop keeps working via hover/focus, see classic-header.css). WP's
     * own walker appends the real <ul class="sub-menu"> immediately after
     * whatever this returns, when the item has one, so the button lands
     * between the label and it. Mega Menu items never get a real sub-menu
     * (process_classic_menu_items() already dropped their siblings), so
     * the panel itself is appended here instead, in that same slot.
     */
    public function inject_classic_submenu_toggle($item_output, $item) {
        $has_mega  = !empty($item->omega_mega_menu_id);
        $has_plain = !$has_mega && in_array('menu-item-has-children', (array) $item->classes, true);

        if (!$has_mega && !$has_plain) {
            return $item_output;
        }

        $item_output .= '<button type="button" class="omega-classic-header__submenu-toggle" aria-expanded="false" aria-label="' . esc_attr__('Toggle submenu', 'omega-design') . '"><span></span></button>';

        if ($has_mega) {
            $value   = 'mega_menu:' . (int) $item->omega_mega_menu_id;
            $content = $this->render_panel_content($value);
            if ('' !== trim($content)) {
                $item_output .= '<div class="omega-megamenu-panel omega-megamenu-panel--inline ' . esc_attr($this->panel_class($value)) . '">' . $content . '</div>';
            }
        }

        return $item_output;
    }

    private function panel_class($value) {
        if (0 === strpos($value, 'mega_menu:')) {
            return 'omega-panel--' . absint(substr($value, strlen('mega_menu:')));
        }
        return 'omega-panel--' . substr(md5($value), 0, 12);
    }

    /**
     * Renders a "mega_menu:{post_id}" value into HTML: the post's own block
     * content via do_blocks() (so nested dynamic blocks, e.g. a Latest
     * Posts query, work exactly as they would anywhere else), prefixed with
     * its Custom CSS field if one was set.
     */
    private function render_panel_content($value) {
        if (0 !== strpos($value, 'mega_menu:')) {
            return '';
        }

        $id = absint(substr($value, strlen('mega_menu:')));
        if (!$id || self::POST_TYPE !== get_post_type($id) || 'publish' !== get_post_status($id)) {
            return '';
        }

        $post = get_post($id);
        $html = do_blocks($post->post_content);

        $css = get_post_meta($id, self::META_CSS, true);
        if (is_string($css) && '' !== trim($css)) {
            $html = '<style>' . $css . '</style>' . $html;
        }

        return $html;
    }
}

megamenu::get_instance();
