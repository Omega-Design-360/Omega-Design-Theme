<?php
/**
 * Sidebar Customizer Settings
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class sidebar {

    const META_MODE     = 'omega_sidebar_mode';
    const META_TEMPLATE = 'omega_sidebar_template';

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('customize_register', [$this, 'register_sidebar_settings']);
        add_filter('default_wp_template_part_areas', [$this, 'register_sidebar_area']);
        add_action('widgets_init', [$this, 'register_widget_area']);
        add_shortcode('omega_sidebar', [$this, 'render_widget_area']);

        add_action('init', [$this, 'register_meta']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);

        // Swaps in the resolved sidebar template part (or removes it
        // entirely) wherever a template references {"slug":"sidebar"}.
        // See filter_sidebar_template_part() for how this stays non-recursive.
        add_filter('render_block_core/template-part', [$this, 'filter_sidebar_template_part'], 10, 3);

        add_filter('body_class', [$this, 'filter_body_classes']);
        add_action('wp_head', [$this, 'output_layout_styles']);
    }

    public function init() {}

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
    public function get_supported_post_types() {
        return apply_filters('omega_design_sidebar_post_types', ['post', 'page']);
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

    public function register_meta() {
        foreach ($this->get_supported_post_types() as $post_type) {
            register_post_meta($post_type, self::META_MODE, [
                'show_in_rest'      => true,
                'single'            => true,
                'type'              => 'string',
                'default'           => '',
                'sanitize_callback' => [$this, 'sanitize_mode'],
                'auth_callback'     => function () {
                    return current_user_can('edit_posts');
                },
            ]);

            register_post_meta($post_type, self::META_TEMPLATE, [
                'show_in_rest'      => true,
                'single'            => true,
                'type'              => 'string',
                'default'           => '',
                'sanitize_callback' => [$this, 'sanitize_template'],
                'auth_callback'     => function () {
                    return current_user_can('edit_posts');
                },
            ]);
        }
    }

    public function sanitize_mode($value) {
        $value = sanitize_key((string) $value);
        return in_array($value, ['show', 'hide'], true) ? $value : '';
    }

    public function sanitize_template($value) {
        $value = sanitize_key((string) $value);
        return isset($this->get_template_choices()[$value]) ? $value : '';
    }

    public function enqueue_editor_assets() {
        $screen = get_current_screen();

        if (!$screen || !in_array($screen->post_type, $this->get_supported_post_types(), true)) {
            return;
        }

        wp_enqueue_script(
            'omega-design-sidebar-toggle',
            OMEGA_DESIGN_JS_URI . '/sidebar-toggle.js',
            ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-compose', 'wp-i18n'],
            $this->asset_version(),
            true
        );

        wp_localize_script('omega-design-sidebar-toggle', 'OmegaSidebarTemplates', $this->get_template_choices());
    }

    private function asset_version() {
        $path = OMEGA_DESIGN_ASSETS . '/js/sidebar-toggle.js';
        return file_exists($path) ? filemtime($path) : OMEGA_DESIGN_ASSET_VERSION;
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
     * Register the "Blog Sidebar" widget area so Appearance > Widgets is
     * usable, and its contents can be placed inside the sidebar template
     * part via the [omega_sidebar] shortcode.
     */
    public function register_widget_area() {
        register_sidebar([
            'name'          => __('Blog Sidebar', 'omega-design'),
            'id'            => 'omega-sidebar',
            'description'   => __('Widgets added here appear in the sidebar template part.', 'omega-design'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ]);
    }

    /**
     * Render the "Blog Sidebar" widget area. Used via the [omega_sidebar]
     * shortcode inside parts/sidebar.html.
     */
    public function render_widget_area() {
        if (!$this->is_sidebar_enabled() || !is_active_sidebar('omega-sidebar')) {
            return '';
        }

        ob_start();
        dynamic_sidebar('omega-sidebar');
        return ob_get_clean();
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

        $post_id = is_singular() ? get_queried_object_id() : 0;

        if ($post_id) {
            $mode = get_post_meta($post_id, self::META_MODE, true);
            if ('show' === $mode) {
                return true;
            }
            if ('hide' === $mode) {
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
        $post_id = is_singular() ? get_queried_object_id() : 0;

        if ($post_id) {
            $template = get_post_meta($post_id, self::META_TEMPLATE, true);
            if ($template && isset($this->get_template_choices()[$template])) {
                return $template;
            }
        }

        $default = get_theme_mod('omega_sidebar_default_template', 'sidebar');
        return isset($this->get_template_choices()[$default]) ? $default : 'sidebar';
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

        if (!isset($this->get_template_choices()[$slug])) {
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

        $wp_customize->add_control('omega_sidebar_position', [
            'label'       => __('Sidebar Position', 'omega-design'),
            'description' => __('Which side of the content the sidebar appears on, everywhere it shows.', 'omega-design'),
            'section'     => 'omega_sidebar_settings',
            'type'        => 'select',
            'choices'     => [
                'left'  => __('Left', 'omega-design'),
                'right' => __('Right', 'omega-design'),
            ],
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
}

sidebar::get_instance();
