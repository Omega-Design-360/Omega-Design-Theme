<?php
/**
 * Registers the theme's native "Omega Icon" block (omega-design/icon) - a
 * self-contained icon picker pre-loaded with the theme's stroke icons and
 * their hover choreography (assets/css/omega-icon-choreography.css, ported
 * from mysite.css), so it shows up in the block inserter with no dependency
 * on a third-party icon plugin.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class blocks {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'register']);
    }

    public function init() {}

    public function register() {
        $dir = OMEGA_DESIGN_BLOCKS . '/omega-icon';

        if (!file_exists($dir . '/block.json')) {
            return;
        }

        $script_path = $dir . '/index.js';
        wp_register_script(
            'omega-icon-block-editor',
            OMEGA_DESIGN_URI . '/blocks/omega-icon/index.js',
            ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n'],
            file_exists($script_path) ? filemtime($script_path) : OMEGA_DESIGN_VERSION,
            true
        );
        wp_set_script_translations('omega-icon-block-editor', 'omega-design');

        $style_path = OMEGA_DESIGN_ASSETS . '/css/omega-icon-choreography.css';
        wp_register_style(
            'omega-icon-block',
            OMEGA_DESIGN_CSS_URI . '/omega-icon-choreography.css',
            [],
            file_exists($style_path) ? filemtime($style_path) : OMEGA_DESIGN_VERSION
        );

        register_block_type($dir);
    }
}
