<?php
/**
 * Classic Footer - 5 hand-written, mobile-friendly footer layouts, each
 * its own real, findable file under template-parts/classic-footer/ -
 * mirrors classic_header.php exactly (same architecture, same reasons).
 * Every piece of content is resolved dynamically at render time
 * (site title/tagline via bloginfo(), copyright via a template with a
 * {year} token, links via an admin-picked classic menu) - nothing about
 * a specific site's name, pages, or content is ever assumed.
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class classic_footer {

    private static $instance = null;

    const STYLES = ['classic-1', 'classic-2', 'classic-3', 'classic-4', 'classic-5'];

    const TEMPLATE_FILES = [
        'classic-1' => 'simple.php',
        'classic-2' => 'columns.php',
        'classic-3' => 'centered.php',
        'classic-4' => 'newsletter.php',
        'classic-5' => 'bold.php',
    ];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter('render_block_core/template-part', [$this, 'maybe_render_classic_footer'], 10, 2);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function init() {}

    public static function style_choices() {
        return [
            'block'     => __('Block Footer (Site Editor)', 'omega-design'),
            'classic-1' => __('Classic 1 - Simple', 'omega-design'),
            'classic-2' => __('Classic 2 - Columns', 'omega-design'),
            'classic-3' => __('Classic 3 - Centered', 'omega-design'),
            'classic-4' => __('Classic 4 - Newsletter CTA', 'omega-design'),
            'classic-5' => __('Classic 5 - Bold', 'omega-design'),
        ];
    }

    private function active_style() {
        $mode = get_theme_mod('omega_footer_mode', 'block');
        return in_array($mode, self::STYLES, true) ? $mode : '';
    }

    /**
     * CSS only - unlike the header, nothing here needs to collapse behind
     * a tap (no dropdowns, no hamburger): columns just stack vertically
     * on mobile via pure CSS, so there's no JS-driven toggle to get wrong.
     */
    public function enqueue_assets() {
        if ('' === $this->active_style()) {
            return;
        }

        $css_path = get_template_directory() . '/assets/css/classic-footer.css';
        if (file_exists($css_path)) {
            wp_enqueue_style(
                'omega-design-classic-footer',
                get_template_directory_uri() . '/assets/css/classic-footer.css',
                [],
                filemtime($css_path)
            );
        }
    }

    /**
     * "{year}" is the only placeholder - replaced with the current year at
     * render time, never baked into the stored setting, so the copyright
     * line never goes stale. Falls back to a fully generic default built
     * from this site's own bloginfo(), not any fixed site name.
     */
    private function copyright_html() {
        $template = get_theme_mod('omega_footer_copyright', '');
        if ('' === trim(wp_strip_all_tags($template))) {
            $template = sprintf(
                /* translators: %s: site name */
                __('&copy; {year} %s. All rights reserved.', 'omega-design'),
                get_bloginfo('name')
            );
        }
        return str_replace('{year}', gmdate('Y'), $template);
    }

    /**
     * Replaces the whole "footer" template part's rendered content - same
     * mechanism and same reasoning as classic_header.php's own
     * maybe_render_classic_header(), including the "Hide footer" check
     * running first regardless of which mode is active.
     */
    public function maybe_render_classic_footer($block_content, $block) {
        if ('footer' !== ($block['attrs']['slug'] ?? '')) {
            return $block_content;
        }

        if (footer_visibility::is_hidden_for_current_page()) {
            return '';
        }

        $style = $this->active_style();
        if ('' === $style) {
            return $block_content;
        }

        $template_file = self::TEMPLATE_FILES[$style] ?? '';
        $template_path = get_template_directory() . '/template-parts/classic-footer/' . $template_file;
        if ('' === $template_file || !file_exists($template_path)) {
            return $block_content;
        }

        $menu_id  = (int) get_theme_mod('omega_classic_footer_menu_id', 0);
        $nav_html = '';
        if ($menu_id && wp_get_nav_menu_object($menu_id)) {
            $nav_html = (string) wp_nav_menu([
                'menu'        => $menu_id,
                'echo'        => false,
                'container'   => false,
                'fallback_cb' => false,
            ]);
        }

        $tagline = get_theme_mod('omega_footer_tagline', '');
        if ('' === trim(wp_strip_all_tags($tagline))) {
            $tagline = get_bloginfo('description');
        }

        $cta_label = get_theme_mod('omega_footer_cta_label', '');
        $cta_url   = get_theme_mod('omega_footer_cta_url', '');
        $copyright = $this->copyright_html();

        ob_start();
        include $template_path;
        return (string) ob_get_clean();
    }
}

classic_footer::get_instance();
