<?php
namespace OmegaDesign\core;

use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class core {

    use singleton;

    const MIN_PHP = '7.4';
    const MIN_WP  = '5.8';

    private $loader = null;
    private $initialized = false;

    private function __construct() {
        $this->check_requirements();
    }

    public function init() {
        autoloader::register();

        if ($this->initialized) {
            return;
        }

        if (!$this->check_requirements()) {
            return;
        }

        $this->loader = loader::get_instance();
        $this->loader->init();

        $this->initialized = true;

        do_action('omega_design_initialized', $this);
    }

    private function check_requirements() {
        if (!self::meets_php_requirement()) {
            add_action('admin_notices', [$this, 'php_version_notice']);
            return false;
        }

        if (!self::meets_wp_requirement()) {
            add_action('admin_notices', [$this, 'wp_version_notice']);
            return false;
        }

        return true;
    }

    public static function meets_php_requirement() {
        return version_compare(PHP_VERSION, self::MIN_PHP, '>=');
    }

    public static function meets_wp_requirement() {
        global $wp_version;
        return version_compare($wp_version, self::MIN_WP, '>=');
    }

    public function php_version_notice() {
        /* translators: %s: current PHP version */
        self::render_error_notice(__('OmegaDesign requires PHP version 7.4 or higher. Your current version is %s.', 'omega-design'), PHP_VERSION);
    }

    public function wp_version_notice() {
        global $wp_version;
        /* translators: %s: current WordPress version */
        self::render_error_notice(__('OmegaDesign requires WordPress version 5.8 or higher. Your current version is %s.', 'omega-design'), $wp_version);
    }

    private static function render_error_notice($message, $version) {
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            sprintf(esc_html($message), esc_html($version))
        );
    }

    public function get_module($module) {
        return $this->loader ? $this->loader->get_module($module) : null;
    }

    public function get_loader() {
        return $this->loader;
    }
}

add_action('after_setup_theme', function() {
    \OmegaDesign\core\core::get_instance()->init();
});
