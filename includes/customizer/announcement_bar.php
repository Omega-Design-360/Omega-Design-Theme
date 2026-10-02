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

use OmegaDesign\traits\assets;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class announcement_bar {

    use singleton;
    use assets;

    private function __construct() {
        add_action('wp_body_open', [$this, 'render_bar']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public static function is_enabled() {
        return (bool) get_theme_mod('omega_announcement_enabled', false);
    }

    private static function is_dismissible() {
        return (bool) get_theme_mod('omega_announcement_dismissible', true);
    }

    public function enqueue_assets() {
        if (!self::is_enabled()) {
            return;
        }

        self::enqueue_style('omega-design-announcement-bar', 'css/announcement-bar.css');

        if (self::is_dismissible()) {
            self::enqueue_script('omega-design-announcement-bar', 'js/announcement-bar.js', [self::core_script()], true);
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

        $style = self::bar_style(
            get_theme_mod('omega_announcement_bg', ''),
            get_theme_mod('omega_announcement_text_color', '')
        );

        echo '<div id="omega-announcement-bar" class="omega-announcement-bar"' . ($style ? ' style="' . esc_attr($style) . '"' : '') . '>';
        echo '<div class="omega-announcement-bar__inner">';
        echo $content; // phpcs:ignore -- trusted admin-authored HTML, see docblock.
        if (self::is_dismissible()) {
            echo self::dismiss_button_html();
        }
        echo '</div></div>';
    }

    /**
     * Inline background/text color declarations for the admin-set colors.
     */
    private static function bar_style($bg, $text_color) {
        $style = '';
        if ($bg) {
            $style .= 'background-color:' . esc_attr($bg) . ';';
        }
        if ($text_color) {
            $style .= 'color:' . esc_attr($text_color) . ';';
        }
        return $style;
    }

    private static function dismiss_button_html() {
        return '<button type="button" class="omega-announcement-bar__dismiss" aria-label="' . esc_attr__('Dismiss', 'omega-design') . '">&times;</button>';
    }
}

announcement_bar::get_instance();
