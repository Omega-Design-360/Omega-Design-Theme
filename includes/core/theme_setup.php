<?php
/**
 * Theme Setup - nav menu locations, upload directories on activation, and
 * core default palette removal.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class theme_setup {

    use singleton;

    private function __construct() {
        add_filter('wp_theme_json_data_default', [$this, 'remove_default_palette']);
        add_action('after_switch_theme', [uploads::class, 'create_directories']);
        add_action('after_setup_theme', [$this, 'register_nav_menus']);
    }

    /**
     * Remove WordPress core default color/gradient CSS variables from
     * frontend output. "defaultPalette: false" in theme.json only hides them
     * from the editor UI; this removes them from the default origin so no
     * CSS vars are emitted.
     */
    public function remove_default_palette($theme_json) {
        return $theme_json->update_with([
            'version'  => 3,
            'settings' => [
                'color' => [
                    'palette'          => [],
                    'gradients'        => [],
                    'duotone'          => [],
                    'defaultPalette'   => false,
                    'defaultGradients' => false,
                    'defaultDuotone'   => false,
                ],
            ],
        ]);
    }

    /**
     * Register Mega Menu nav menu locations.
     */
    public function register_nav_menus() {
        register_nav_menus([
            'megamenu' => __('Mega Menu', 'omega-design'),
            'primary'  => __('Primary Menu', 'omega-design'),
            'footer'   => __('Footer Menu', 'omega-design'),
        ]);
    }
}
