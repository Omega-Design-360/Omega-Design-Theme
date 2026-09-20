<?php
/**
 * Announcement / Utility Bar - a second, independent bar (phone number,
 * promo message, social links, etc.) stacked above whatever header is
 * active. Hooked on wp_body_open() rather than tied into
 * classic_header::maybe_render_classic_header(), so it renders the same
 * way regardless of whether the site is using Block Navigation or one of
 * the 5 classic styles - always the first thing in <body>, naturally
 * stacking above either.
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class announcement_bar {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_body_open', [$this, 'render_bar']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function init() {}

    public static function is_enabled() {
        return (bool) get_theme_mod('omega_announcement_enabled', false);
    }

    public function enqueue_assets() {
        if (!self::is_enabled()) {
            return;
        }

        $css_path = get_template_directory() . '/assets/css/announcement-bar.css';
        if (file_exists($css_path)) {
            wp_enqueue_style(
                'omega-design-announcement-bar',
                get_template_directory_uri() . '/assets/css/announcement-bar.css',
                [],
                filemtime($css_path)
            );
        }

        if (get_theme_mod('omega_announcement_dismissible', true)) {
            $js_path = get_template_directory() . '/assets/js/announcement-bar.js';
            if (file_exists($js_path)) {
                wp_enqueue_script(
                    'omega-design-announcement-bar',
                    get_template_directory_uri() . '/assets/js/announcement-bar.js',
                    [],
                    filemtime($js_path),
                    true
                );
            }
        }
    }

    /**
     * Content is trusted, unsanitized admin-authored HTML (phone/social
     * links need real markup) - same trust model as the Mega Menu's own
     * Custom CSS field and core's Additional CSS: only a manage_options
     * user can ever reach the form that sets it.
     */
    public function render_bar() {
        if (!self::is_enabled()) {
            return;
        }

        $content = get_theme_mod('omega_announcement_content', '');
        if ('' === trim(wp_strip_all_tags($content))) {
            return;
        }

        $bg          = get_theme_mod('omega_announcement_bg', '');
        $text_color  = get_theme_mod('omega_announcement_text_color', '');
        $dismissible = (bool) get_theme_mod('omega_announcement_dismissible', true);

        $style = '';
        if ($bg) {
            $style .= 'background-color:' . esc_attr($bg) . ';';
        }
        if ($text_color) {
            $style .= 'color:' . esc_attr($text_color) . ';';
        }

        echo '<div id="omega-announcement-bar" class="omega-announcement-bar"' . ($style ? ' style="' . esc_attr($style) . '"' : '') . '>';
        echo '<div class="omega-announcement-bar__inner">';
        echo $content; // phpcs:ignore -- trusted admin-authored HTML, see docblock.
        if ($dismissible) {
            echo '<button type="button" class="omega-announcement-bar__dismiss" aria-label="' . esc_attr__('Dismiss', 'omega-design') . '">&times;</button>';
        }
        echo '</div></div>';
    }
}

announcement_bar::get_instance();
