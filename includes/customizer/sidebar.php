<?php
/**
 * Sidebar Customizer Settings
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

use OmegaDesign\traits\customizer_section;
use OmegaDesign\traits\editor_meta;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class sidebar {

    use singleton;
    use editor_meta;
    use customizer_section;

    const META_MODE     = 'omega_sidebar_mode';
    const META_TEMPLATE = 'omega_sidebar_template';

    private function __construct() {
        add_action('customize_register', [$this, 'register_sidebar_settings']);
        add_action('customize_controls_enqueue_scripts', [$this, 'enqueue_control_assets']);
        add_filter('default_wp_template_part_areas', [$this, 'register_sidebar_area']);

        $this->register_editor_meta_hooks();

        // Swaps in the resolved sidebar template part (or removes it
        // entirely) wherever a template references {"slug":"sidebar"}.
        // See filter_sidebar_template_part() for how this stays non-recursive.
        add_filter('render_block_core/template-part', [$this, 'filter_sidebar_template_part'], 10, 3);

        add_filter('body_class', [$this, 'filter_body_classes']);
        add_action('wp_head', [$this, 'output_layout_styles']);
    }

    /**
     * The sidebar template parts admins can pick between, both as a global
     * default (Customize > Omega Design > Sidebar) and per-page (the block
     * editor's Page Settings panel). Kept as a fixed list rather than
     * querying registered template parts at runtime, since these two are
     * the only ones this theme ships and validating a fixed whitelist is
     * simpler than sanitizing an arbitrary slug pulled from the DB.
     */
    public function get_template_choices() {
        return [
            'sidebar'      => __('Default (Blog Sidebar)', 'omega-design'),
            'sidebar-shop' => __('Shop Sidebar', 'omega-design'),
        ];
    }

    /**
     * Post types that get the per-page "Sidebar" controls in the editor.
     * Matches background_color.php's own list exactly - the "Sidebar"
     * control renders inside the SAME assembled "Page Settings" panel that
     * script builds (see background-color.js's PageSettingsPanel), so the
     * panel simply never mounts on any post type this list omits.
     */
    protected function post_types_filter() {
        return 'omega_design_sidebar_post_types';
    }

    /**
     * Where the sidebar can appear site-wide, and the theme_mod that gates
     * each one. A per-page override (see should_show_sidebar()) always
     * wins over these; these are just the defaults for pages that don't
     * set one.
     */
    public function get_sidebar_locations_map() {
        return [
            'category'  => 'omega_sidebar_on_category',
            'search'    => 'omega_sidebar_on_search',
            'blog_home' => 'omega_sidebar_on_blog_home',
            'single'    => 'omega_sidebar_on_single',
            'page'      => 'omega_sidebar_on_page',
        ];
    }

    /**
     * Which location key the current front-end request falls under, or
     * null if it's not one the sidebar system recognizes at all (e.g. the
     * front page, a WooCommerce shop/product page, a 404).
     */
    public function current_location_key() {
        if (is_category() || is_tag() || is_tax()) {
            return 'category';
        }
        if (is_search()) {
            return 'search';
        }
        if (is_home()) {
            return 'blog_home';
        }
        if (is_singular('post')) {
            return 'single';
        }
        if (is_page()) {
            return 'page';
        }
        return null;
    }

    protected function meta_fields() {
        return [
            self::META_MODE     => self::string_meta_field([$this, 'sanitize_mode']),
            self::META_TEMPLATE => self::string_meta_field([$this, 'sanitize_template']),
        ];
    }

    public function sanitize_mode($value) {
        return self::sanitize_key_choice($value, ['show', 'hide'], '', false);
    }

    public function sanitize_template($value) {
        return self::sanitize_key_choice($value, $this->get_template_choices(), '');
    }

    protected function enqueue_editor_screen_assets() {
        if (self::enqueue_editor_script('omega-design-sidebar-toggle', 'js/sidebar-toggle.js')) {
            wp_localize_script('omega-design-sidebar-toggle', 'OmegaSidebarTemplates', $this->get_template_choices());
        }
    }

    /**
     * The post being viewed when it's a singular page, else 0 - the only
     * case a per-page sidebar override can apply.
     */
    private static function singular_post_id() {
        return is_singular() ? get_queried_object_id() : 0;
    }

    /**
     * Register sidebar area in template parts
     */
    public function register_sidebar_area($areas) {
        // Check if sidebar area already exists
        $exists = false;
        foreach ($areas as $area) {
            if ($area['area'] === 'sidebar') {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $areas[] = [
                'area'        => 'sidebar',
                'area_tag'    => 'aside',
                'label'       => __('Sidebar', 'omega-design'),
                'description' => __('Sidebar template parts', 'omega-design'),
                'icon'        => 'sidebar',
            ];
        }

        return $areas;
    }

    /**
     * Resolves whether the sidebar should render on the page currently
     * being requested. A per-page override set in the block editor's Page
     * Settings panel always wins; otherwise falls back to the global
     * per-location theme_mod for the current page type.
     */
    public function should_show_sidebar() {
        if (!$this->is_sidebar_enabled()) {
            return false;
        }

        $post_id = self::singular_post_id();

        if ($post_id) {
            switch ((string) get_post_meta($post_id, self::META_MODE, true)) {
                case 'show':
                    return true;
                case 'hide':
                    return false;
            }
        }

        $key = $this->current_location_key();
        if (!$key) {
            return false;
        }

        $map    = $this->get_sidebar_locations_map();
        $option = $map[$key] ?? null;
        if (!$option) {
            return false;
        }

        // "category" was the only location this feature originally
        // supported, so it stays on by default; every newer location
        // starts opt-in to avoid changing existing sites' front ends.
        $default = ('category' === $key);
        return (bool) get_theme_mod($option, $default);
    }

    /**
     * Which sidebar template part slug should actually render. A per-page
     * override wins; otherwise the site-wide default template_choices
     * setting.
     */
    public function resolve_sidebar_slug() {
        $post_id = self::singular_post_id();

        if ($post_id) {
            $template = get_post_meta($post_id, self::META_TEMPLATE, true);
            if ($template && $this->is_template_choice($template)) {
                return $template;
            }
        }

        $default = get_theme_mod('omega_sidebar_default_template', 'sidebar');
        return $this->is_template_choice($default) ? $default : 'sidebar';
    }

    private function is_template_choice($slug) {
        return isset($this->get_template_choices()[$slug]);
    }

    /**
     * Intercepts every {"slug":"sidebar"} (or "sidebar-shop") template-part
     * render: drops it entirely when should_show_sidebar() says no, or
     * swaps in the resolved slug's own markup when a different sidebar
     * template has been chosen for this page.
     *
     * Non-recursive by construction: render_block() re-fires this same
     * filter for the substituted block, but by then resolve_sidebar_slug()
     * returns the SAME slug we just rendered, so the "swap" branch is
     * skipped on the second pass and the freshly rendered $block_content is
     * returned as-is.
     */
    public function filter_sidebar_template_part($block_content, $parsed_block, $block) {
        $slug = $parsed_block['attrs']['slug'] ?? '';

        if (!$this->is_template_choice($slug)) {
            return $block_content;
        }

        if (!$this->should_show_sidebar()) {
            return '';
        }

        $resolved = $this->resolve_sidebar_slug();

        if ($resolved !== $slug) {
            return render_block([
                'blockName'    => 'core/template-part',
                'attrs'        => [
                    'slug'    => $resolved,
                    'theme'   => get_stylesheet(),
                    'tagName' => 'aside',
                ],
                'innerBlocks'  => [],
                'innerHTML'    => '',
                'innerContent' => [],
            ]);
        }

        return $block_content;
    }

    /**
     * Marks the current page's sidebar visibility/position on <body> so
     * style.css's ".omega-sidebar-layout__*" rules (shared by every
     * template) can hide the aside column or flip which side it's on
     * without any template needing per-request PHP of its own.
     */
    public function filter_body_classes($classes) {
        $classes[] = $this->should_show_sidebar() ? 'omega-sidebar-visible' : 'omega-sidebar-hidden';
        $classes[] = 'omega-sidebar-pos-' . $this->get_sidebar_position();
        return $classes;
    }

    /**
     * The sidebar's width is the one setting that can't be expressed as a
     * body class, so it goes out as a single CSS custom property instead.
     */
    public function output_layout_styles() {
        $width = (int) get_theme_mod('omega_sidebar_width', 30);
        $width = max(20, min(50, $width));
        echo '<style id="omega-sidebar-vars">:root{--omega-sidebar-width:' . $width . '%;}</style>' . "\n";
    }

    /**
     * Register sidebar customizer settings
     */
    public function register_sidebar_settings($wp_customize) {
        // Add Sidebar Section
        $wp_customize->add_section('omega_sidebar_settings', [
            'title'       => __('Sidebar', 'omega-design'),
            'description' => __('Controls the widget-ready sidebar. Choose which page types show it below, add widgets under Appearance > Widgets > Blog Sidebar, and override any single post or page from its own Page Settings panel in the editor.', 'omega-design'),
            'priority'    => 20,
            'panel'       => 'omega_design_panel',
        ]);

        // Enable/Disable Sidebar
        $wp_customize->add_setting('omega_enable_sidebar', [
            'default'           => true,
            'sanitize_callback' => 'wp_validate_boolean',
        ]);

        $wp_customize->add_control('omega_enable_sidebar', [
            'label'       => __('Enable Sidebar', 'omega-design'),
            'description' => __('Master switch. Turn off to give every page a full-width layout, regardless of the settings below.', 'omega-design'),
            'section'     => 'omega_sidebar_settings',
            'type'        => 'checkbox',
        ]);

        // Sidebar Position
        $wp_customize->add_setting('omega_sidebar_position', [
            'default'           => 'right',
            'sanitize_callback' => [$this, 'sanitize_position'],
        ]);

        self::add_card_control($wp_customize, 'omega_sidebar_position', 'omega_sidebar_position', [__CLASS__, 'render_position_cards'], [
            'label'       => __('Sidebar Position', 'omega-design'),
            'description' => __('Which side of the content the sidebar appears on, everywhere it shows.', 'omega-design'),
            'section'     => 'omega_sidebar_settings',
        ]);

        // Sidebar Width
        $wp_customize->add_setting('omega_sidebar_width', [
            'default'           => '30',
            'sanitize_callback' => 'absint',
        ]);

        $wp_customize->add_control('omega_sidebar_width', [
            'label'       => __('Sidebar Width (%)', 'omega-design'),
            'description' => __('How much horizontal space the sidebar takes up, between 20% and 50%.', 'omega-design'),
            'section'     => 'omega_sidebar_settings',
            'type'        => 'number',
            'input_attrs' => [
                'min'  => 20,
                'max'  => 50,
                'step' => 1,
            ],
        ]);

        // Default sidebar template
        $wp_customize->add_setting('omega_sidebar_default_template', [
            'default'           => 'sidebar',
            'sanitize_callback' => [$this, 'sanitize_template'],
        ]);

        $wp_customize->add_control('omega_sidebar_default_template', [
            'label'       => __('Default Sidebar Content', 'omega-design'),
            'description' => __('Which sidebar template shows by default. A single post or page can override this from its own Page Settings panel.', 'omega-design'),
            'section'     => 'omega_sidebar_settings',
            'type'        => 'select',
            'choices'     => $this->get_template_choices(),
        ]);

        // Where the sidebar shows by default.
        foreach ($this->get_location_labels() as $setting_id => $meta) {
            $wp_customize->add_setting($setting_id, [
                'default'           => $meta['default'],
                'sanitize_callback' => 'wp_validate_boolean',
            ]);

            $wp_customize->add_control($setting_id, [
                'label'       => $meta['label'],
                'description' => $meta['description'],
                'section'     => 'omega_sidebar_settings',
                'type'        => 'checkbox',
            ]);
        }
    }

    /**
     * Label/description/default for each location theme_mod, shared by
     * both the native Customizer form and the theme's own Settings page
     * (menus.php's settings_page()) so the copy never drifts between them.
     */
    public function get_location_labels() {
        return [
            'omega_sidebar_on_category' => [
                'label'       => __('Category & Tag Archives', 'omega-design'),
                'description' => __('e.g. yoursite.com/category/news/', 'omega-design'),
                'default'     => true,
            ],
            'omega_sidebar_on_blog_home' => [
                'label'       => __('Blog Home', 'omega-design'),
                'description' => __('The main posts listing page.', 'omega-design'),
                'default'     => false,
            ],
            'omega_sidebar_on_search' => [
                'label'       => __('Search Results', 'omega-design'),
                'description' => '',
                'default'     => false,
            ],
            'omega_sidebar_on_single' => [
                'label'       => __('Single Posts', 'omega-design'),
                'description' => '',
                'default'     => false,
            ],
            'omega_sidebar_on_page' => [
                'label'       => __('Pages', 'omega-design'),
                'description' => __('Individual pages can still override this from their own Page Settings panel.', 'omega-design'),
                'default'     => false,
            ],
        ];
    }

    /**
     * Sanitize position setting
     */
    public function sanitize_position($input) {
        $valid = ['left', 'right'];
        if (in_array($input, $valid)) {
            return $input;
        }
        return 'right';
    }

    /**
     * Get sidebar position
     */
    public function get_sidebar_position() {
        return get_theme_mod('omega_sidebar_position', 'right');
    }

    /**
     * Is sidebar enabled
     */
    public function is_sidebar_enabled() {
        return get_theme_mod('omega_enable_sidebar', true);
    }

    /**
     * The theme's own Settings page (menus.php) draws the same preview
     * cards with this same markup/CSS (.omega-sidebar-pos-grid, admin-
     * pages.css) - this reuses that exact CSS in the Customizer's controls
     * panel too, so the native "select" control (a bare dropdown, no
     * layout preview at all) isn't the only place this setting can be
     * changed from.
     */
    public function enqueue_control_assets() {
        self::enqueue_admin_pages_style(false);
    }

    /**
     * Shared markup for both the Settings page (menus.php) and the native
     * Customizer control below - a small layout diagram (a tinted "aside"
     * block beside a few content lines, in the actual chosen order) per
     * side, instead of a bare <select>. $link_callback receives each
     * position's key and must echo whatever attributes bind that <input>
     * to its context - a plain name="omega_sidebar_position" for the POST
     * form, or the Customizer's own name + $this->link() for two-way JS
     * binding.
     */
    public static function render_position_cards($current, $link_callback) {
        $positions = [
            'left'  => __('Sidebar Left', 'omega-design'),
            'right' => __('Sidebar Right', 'omega-design'),
        ];

        self::render_radio_card_grid('omega-sidebar-pos', $positions, $current, $link_callback, [__CLASS__, 'render_position_card_body']);
    }

    public static function render_position_card_body($key, $label) {
        self::render_card_radio_dot('omega-sidebar-pos');
        ?>
        <span class="omega-sidebar-pos-card__preview omega-sidebar-pos-card__preview--<?php echo esc_attr($key); ?>">
            <span class="omega-sidebar-pos-card__aside"></span>
            <span class="omega-sidebar-pos-card__main">
                <span class="omega-sidebar-pos-card__line"></span>
                <span class="omega-sidebar-pos-card__line omega-sidebar-pos-card__line--short"></span>
                <span class="omega-sidebar-pos-card__line omega-sidebar-pos-card__line--short"></span>
            </span>
        </span>
        <span class="omega-sidebar-pos-card__title"><?php echo esc_html($label); ?></span>
        <?php
    }
}

sidebar::get_instance();
