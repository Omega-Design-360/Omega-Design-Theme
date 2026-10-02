<?php
/**
 * Theme Asset Helpers
 *
 * Paths here are relative to the theme's assets/ directory, e.g.
 * 'css/admin-pages.css' or 'js/title-toggle.js'. Versions are the file's
 * filemtime() so a saved edit is picked up on the next load instead of
 * being cached by the browser under the static OMEGA_DESIGN_ASSET_VERSION
 * query string.
 *
 * @package OmegaDesign\traits
 */

namespace OmegaDesign\traits;

defined('ABSPATH') || exit;

trait assets {

    protected static function asset_path($relative) {
        return OMEGA_DESIGN_ASSETS . '/' . ltrim($relative, '/');
    }

    protected static function asset_uri($relative) {
        return OMEGA_DESIGN_ASSETS_URI . '/' . ltrim($relative, '/');
    }

    protected static function has_asset($relative) {
        return file_exists(self::asset_path($relative));
    }

    protected static function asset_version($relative) {
        $path = self::asset_path($relative);
        return file_exists($path) ? filemtime($path) : OMEGA_DESIGN_ASSET_VERSION;
    }

    /**
     * Enqueues a stylesheet from assets/, skipping it when the file is
     * missing. Returns whether it was enqueued.
     */
    protected static function enqueue_style($handle, $relative, $deps = [], $media = 'all') {
        if (!self::has_asset($relative)) {
            return false;
        }

        wp_enqueue_style($handle, self::asset_uri($relative), $deps, self::asset_version($relative), $media);
        return true;
    }

    /**
     * Enqueues a script from assets/, skipping it when the file is missing.
     * $args is passed straight through as wp_enqueue_script()'s 5th
     * argument (true/false for in_footer, or an args array).
     */
    protected static function enqueue_script($handle, $relative, $deps = [], $args = true) {
        if (!self::has_asset($relative)) {
            return false;
        }

        wp_enqueue_script($handle, self::asset_uri($relative), $deps, self::asset_version($relative), $args);
        return true;
    }

    /**
     * Handle of assets/js/editor-shared.js (window.OmegaDesignEditor), the
     * helpers every Omega Design block editor script builds on - registered
     * on first use, for a script to list as a dependency.
     */
    protected static function editor_shared_script() {
        $handle = 'omega-design-editor-shared';
        if (!wp_script_is($handle, 'registered')) {
            wp_register_script($handle, self::asset_uri('js/editor-shared.js'), [], self::asset_version('js/editor-shared.js'), true);
        }
        return $handle;
    }

    /**
     * Prints $css through a file-less style handle, so it's output with the
     * rest of the enqueued styles.
     */
    protected static function enqueue_inline_style($handle, $css) {
        wp_register_style($handle, false, [], OMEGA_DESIGN_ASSET_VERSION);
        wp_enqueue_style($handle);
        wp_add_inline_style($handle, $css);
    }

    /**
     * The admin Settings page stylesheet, shared by every Customizer card
     * control. With $with_variables, also prints the theme.json-derived
     * --wp--preset--* variables, which wp-admin screens never print on
     * their own, so the preview swatches track the real color scheme.
     */
    protected static function enqueue_admin_pages_style($with_variables = true) {
        self::enqueue_style('omega-design-admin-pages', 'css/admin-pages.css');

        if ($with_variables) {
            wp_add_inline_style('omega-design-admin-pages', wp_get_global_stylesheet(['variables']));
        }
    }
}
