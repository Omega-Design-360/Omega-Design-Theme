<?php
/**
 * Elementor compatibility.
 *
 * - Elementor Pro Theme Builder: registers the core locations, and lets a
 *   Theme Builder header/footer replace the theme's own header/footer
 *   template parts on every page (block templates included).
 * - Elementor's "Full Width" template and Theme Builder templates call
 *   get_header()/get_footer() - see header.php/footer.php, which render the
 *   same header/footer template parts as the block templates.
 * - One-time defaults for Elementor's Site Settings, so Elementor starts
 *   out matching the theme (filled in only where still unset - nothing the
 *   site owner already changed is overwritten).
 *
 * Everything here is inert unless Elementor is active.
 *
 * @package OmegaDesign\compat
 */

namespace OmegaDesign\compat;

use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class elementor {

    use singleton;

    /** Set once the Site Settings defaults below have been applied. */
    const SYNC_OPTION = 'omega_design_elementor_synced';

    /** Elementor system color id => theme palette slug. */
    const COLOR_MAP = [
        'primary'   => 'primary',
        'secondary' => 'secondary',
        'text'      => 'body-text',
        'accent'    => 'accent',
    ];

    /** Template part slugs an Elementor Pro Theme Builder location can replace. */
    const LOCATIONS = ['header', 'footer'];

    private function __construct() {
        add_action('elementor/theme/register_locations', [$this, 'register_locations']);
        // After classic_header/classic_footer (10), so a Theme Builder
        // header/footer wins over either the block or the Classic one.
        add_filter('render_block_core/template-part', [$this, 'maybe_render_location'], 20, 2);
        add_action('elementor/init', [$this, 'maybe_apply_defaults']);
        add_action('after_switch_theme', [$this, 'reset_defaults_flag']);
    }

    /** Header, footer, single and archive - for Elementor Pro's Theme Builder. */
    public function register_locations($elementor_theme_manager) {
        $elementor_theme_manager->register_all_core_location();
    }

    /**
     * Swaps the header/footer template part for an Elementor Pro Theme
     * Builder header/footer when one applies to the current page. A part
     * the page hides (Hide header/footer) stays hidden.
     */
    public function maybe_render_location($block_content, $block) {
        $slug = $block['attrs']['slug'] ?? '';
        if ('' === $block_content || !in_array($slug, self::LOCATIONS, true) || !function_exists('elementor_theme_do_location')) {
            return $block_content;
        }

        ob_start();
        $rendered = elementor_theme_do_location($slug);
        $location_html = ob_get_clean();

        return $rendered ? $location_html : $block_content;
    }

    public function reset_defaults_flag() {
        delete_option(self::SYNC_OPTION);
    }

    /**
     * Gives Elementor's Site Settings the theme's own values, once:
     * system colors from the active color scheme, the theme's content
     * width, the "Full Width" template (theme header/footer, no sidebar or
     * duplicate title) as the default for Elementor-built pages, and the
     * theme's page title selector for Elementor's "Hide Title". Elementor's
     * "Disable Default Colors/Fonts" are switched on so its widgets inherit
     * the theme's colors and fonts (Poppins/Jost, already self-hosted)
     * instead of forcing Roboto from Google Fonts.
     */
    public function maybe_apply_defaults() {
        if (get_option(self::SYNC_OPTION)) {
            return;
        }

        $kit_id = (int) get_option('elementor_active_kit');
        if (!$kit_id || !get_post($kit_id)) {
            return; // Elementor creates its kit on activation - try again next load.
        }

        $settings = get_post_meta($kit_id, '_elementor_page_settings', true);
        $settings = is_array($settings) ? $settings : [];

        foreach (self::kit_defaults() as $key => $value) {
            if (!isset($settings[$key])) {
                $settings[$key] = $value;
            }
        }
        update_post_meta($kit_id, '_elementor_page_settings', $settings);

        foreach (['elementor_disable_color_schemes', 'elementor_disable_typography_schemes'] as $option) {
            if (false === get_option($option)) {
                update_option($option, 'yes');
            }
        }

        // Regenerate Elementor's cached kit CSS with the new values.
        if (class_exists('\Elementor\Plugin') && isset(\Elementor\Plugin::$instance->files_manager)) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }

        update_option(self::SYNC_OPTION, 1);
    }

    private static function kit_defaults() {
        $defaults = [
            'default_page_template' => 'elementor_header_footer',
            'page_title_selector'   => 'h1.entry-title, .wp-block-post-title',
        ];

        $palette = self::palette();
        $colors  = [];
        foreach (self::COLOR_MAP as $id => $slug) {
            if (isset($palette[$slug])) {
                $colors[] = ['_id' => $id, 'title' => ucfirst($id), 'color' => $palette[$slug]];
            }
        }
        if (count($colors) === count(self::COLOR_MAP)) {
            $defaults['system_colors'] = $colors;
        }

        $content_size = (int) wp_get_global_settings(['layout', 'contentSize']);
        if ($content_size > 0) {
            $defaults['container_width'] = ['unit' => 'px', 'size' => $content_size];
        }

        return $defaults;
    }

    /** The theme palette (active color scheme applied) as slug => color. */
    private static function palette() {
        $palette = [];
        foreach ((array) wp_get_global_settings(['color', 'palette', 'theme']) as $entry) {
            if (isset($entry['slug'], $entry['color'])) {
                $palette[$entry['slug']] = $entry['color'];
            }
        }
        return $palette;
    }
}
