<?php
/**
 * Theme Class Autoloader
 *
 * Maps OmegaDesign\{namespace}\{class} to includes/{namespace}/{class}.php.
 * Registered first thing in functions.php so every class and trait -
 * including page_cache, which runs before the rest of the theme boots -
 * can be loaded on demand.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class autoloader {

    const PREFIX = 'OmegaDesign\\';

    private static $registered = false;

    public static function register() {
        if (self::$registered) {
            return;
        }

        spl_autoload_register([__CLASS__, 'load']);
        self::$registered = true;
    }

    public static function load($class) {
        $file = self::resolve_file($class);

        if ($file && file_exists($file)) {
            require_once $file;
            return true;
        }

        return false;
    }

    /**
     * File path for a theme class, or null for any class outside the
     * OmegaDesign\ namespace.
     */
    private static function resolve_file($class) {
        $len = strlen(self::PREFIX);

        if (strncmp(self::PREFIX, $class, $len) !== 0) {
            return null;
        }

        return OMEGA_DESIGN_INCLUDES . '/' . str_replace('\\', '/', substr($class, $len)) . '.php';
    }
}
