<?php
/**
 * Customizer Class
 * 
 * @package OmegaDesign
 * @subpackage Customizer
 */

namespace OmegaDesign\Customizer;

defined('ABSPATH') || exit;

class customizer {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('customize_register', [$this, 'register']);
    }
    
    public function register($wp_customize) {
        // Your customizer settings here
    }
}