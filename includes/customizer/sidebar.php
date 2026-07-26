<?php
/**
 * Sidebar Customizer Settings
 * 
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class sidebar {
    
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
    }
    
    public function init() {}
    
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
     * Register sidebar customizer settings
     */
    public function register_sidebar_settings($wp_customize) {
        // Add Sidebar Section
        $wp_customize->add_section('omega_sidebar_settings', [
            'title'       => __('Sidebar', 'omega-design'),
            'description' => __('Controls the widget-ready sidebar shown on category archive pages. Add widgets to it under Appearance > Widgets > Blog Sidebar.', 'omega-design'),
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
            'description' => __('Turn the sidebar off to give category archives a full-width layout.', 'omega-design'),
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
            'description' => __('Which side of the post listing the sidebar appears on.', 'omega-design'),
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