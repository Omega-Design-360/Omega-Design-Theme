<?php

/**
 * Theme Functions and Definitions
 * 
 * @package OmegaDesign
 * @author Amjad Shahzad
 * @link https://omegadesign.io/amjad-shahzad/
 */

defined('ABSPATH') || exit;

/**
 * Core Theme Paths
 */
define('OMEGA_DESIGN_DIR', get_template_directory());
define('OMEGA_DESIGN_URI', get_template_directory_uri());

/**
 * Text domain for all __()/_e() calls across the theme - matches the "Text
 * Domain:" header in style.css.
 */
define('OMEGA_DESIGN_TEXTDOMAIN', 'omega-design');

/**
 * Theme Basic Information
 *
 * OMEGA_DESIGN_VERSION is read from style.css's own "Version:" header (the
 * one WordPress itself already treats as this theme's canonical version) so
 * there is exactly one place to bump on a release - get_file_data() is a
 * plain header-parsing helper (no theme cache/object involved), safe to
 * call this early in the load order.
 */
$omega_design_theme_data = get_file_data(OMEGA_DESIGN_DIR . '/style.css', ['Version' => 'Version']);
define('OMEGA_DESIGN_VERSION', !empty($omega_design_theme_data['Version']) ? $omega_design_theme_data['Version'] : '1.0.0');
unset($omega_design_theme_data);

/**
 * Child Theme Support (if child theme is used)
 */
if (!defined('OMEGA_DESIGN_CHILD_DIR')) {
    define('OMEGA_DESIGN_CHILD_DIR', get_stylesheet_directory());
    define('OMEGA_DESIGN_CHILD_URI', get_stylesheet_directory_uri());
}

/**
 * Component Paths
 */
define('OMEGA_DESIGN_INCLUDES', OMEGA_DESIGN_DIR . '/includes');
define('OMEGA_DESIGN_ADMIN', OMEGA_DESIGN_DIR . '/admin');
define('OMEGA_DESIGN_BLOCKS', OMEGA_DESIGN_DIR . '/blocks');
define('OMEGA_DESIGN_PATTERN', OMEGA_DESIGN_DIR . '/pattern');
define('OMEGA_DESIGN_PARTS', OMEGA_DESIGN_DIR . '/parts');
define('OMEGA_DESIGN_TEMPLATES', OMEGA_DESIGN_DIR . '/templates');
define('OMEGA_DESIGN_WIDGETS', OMEGA_DESIGN_DIR . '/widgets');
define('OMEGA_DESIGN_ASSETS', OMEGA_DESIGN_DIR . '/assets');

/**
 * Asset URLs
 */
define('OMEGA_DESIGN_ASSETS_URI', OMEGA_DESIGN_URI . '/assets');
define('OMEGA_DESIGN_CSS_URI', OMEGA_DESIGN_ASSETS_URI . '/css');
define('OMEGA_DESIGN_JS_URI', OMEGA_DESIGN_ASSETS_URI . '/js');
define('OMEGA_DESIGN_IMAGES_URI', OMEGA_DESIGN_ASSETS_URI . '/images');
define('OMEGA_DESIGN_ICONS_URI', OMEGA_DESIGN_ASSETS_URI . '/icons');

/**
 * WordPress Uploads Directory
 */
if (!defined('OMEGA_DESIGN_UPLOADS_DIR')) {
    $upload_dir = wp_upload_dir();
    define('OMEGA_DESIGN_UPLOADS_DIR', $upload_dir['basedir']);
    define('OMEGA_DESIGN_UPLOADS_URL', $upload_dir['baseurl']);
}

/**
 * Year/Month based uploads
 */
define('OMEGA_DESIGN_UPLOADS_CURRENT_YEAR', gmdate('Y'));
define('OMEGA_DESIGN_UPLOADS_CURRENT_MONTH', gmdate('m'));
define('OMEGA_DESIGN_UPLOADS_YEAR_DIR', OMEGA_DESIGN_UPLOADS_DIR . '/' . gmdate('Y'));
define('OMEGA_DESIGN_UPLOADS_YEAR_MONTH_DIR', OMEGA_DESIGN_UPLOADS_DIR . '/' . gmdate('Y') . '/' . gmdate('m'));

/**
 * Custom upload subdirectories
 */
define('OMEGA_DESIGN_UPLOADS_THEME_DIR', OMEGA_DESIGN_UPLOADS_DIR . '/omega-design');
define('OMEGA_DESIGN_UPLOADS_THEME_URL', OMEGA_DESIGN_UPLOADS_URL . '/omega-design');

/**
 * Class autoloader - registered before anything else so every theme class
 * and trait (including page_cache below) loads on demand.
 */
require_once OMEGA_DESIGN_INCLUDES . '/core/autoloader.php';
\OmegaDesign\core\autoloader::register();

/**
 * Full-page cache hit, served before the rest of the theme even loads -
 * see OmegaDesign\core\page_cache::serve_early(). Exits here on a hit;
 * on a miss (or any non-cacheable request) it returns immediately and the
 * theme boots as normal.
 */
\OmegaDesign\core\page_cache::serve_early();
define('OMEGA_DESIGN_UPLOADS_TEMP_DIR', OMEGA_DESIGN_UPLOADS_THEME_DIR . '/temp');
define('OMEGA_DESIGN_UPLOADS_BACKUP_DIR', OMEGA_DESIGN_UPLOADS_THEME_DIR . '/backups');
define('OMEGA_DESIGN_UPLOADS_IMAGES_DIR', OMEGA_DESIGN_UPLOADS_THEME_DIR . '/images');
define('OMEGA_DESIGN_UPLOADS_FONTS_DIR', OMEGA_DESIGN_UPLOADS_THEME_DIR . '/fonts');
define('OMEGA_DESIGN_UPLOADS_LOGS_DIR', OMEGA_DESIGN_UPLOADS_THEME_DIR . '/logs');
define('OMEGA_DESIGN_UPLOADS_EXPORTS_DIR', OMEGA_DESIGN_UPLOADS_THEME_DIR . '/exports');

/**
 * WooCommerce Constants
 */
define('OMEGA_DESIGN_WOOCOMMERCE_ACTIVE', class_exists('WooCommerce'));
define('OMEGA_DESIGN_WOOCOMMERCE_UPLOADS', OMEGA_DESIGN_UPLOADS_DIR . '/woocommerce');
define('OMEGA_DESIGN_WOOCOMMERCE_TEMPLATES', OMEGA_DESIGN_TEMPLATES . '/woocommerce');

/**
 * Custom Post Types Uploads
 */
define('OMEGA_DESIGN_CPT_UPLOADS_DIR', OMEGA_DESIGN_UPLOADS_DIR . '/custom-post-types');
define('OMEGA_DESIGN_CPT_UPLOADS_URL', OMEGA_DESIGN_UPLOADS_URL . '/custom-post-types');

/**
 * Development mode detection
 */
if (!defined('OMEGA_DESIGN_DEV_MODE')) {
    define('OMEGA_DESIGN_DEV_MODE', defined('WP_DEBUG') && WP_DEBUG);
}

/**
 * Script Debug Mode
 */
if (!defined('OMEGA_DESIGN_SCRIPT_DEBUG')) {
    define('OMEGA_DESIGN_SCRIPT_DEBUG', defined('SCRIPT_DEBUG') && SCRIPT_DEBUG);
}

/**
 * Asset loading - minified or not
 */
if (OMEGA_DESIGN_DEV_MODE || OMEGA_DESIGN_SCRIPT_DEBUG) {
    define('OMEGA_DESIGN_ASSET_SUFFIX', '');
    define('OMEGA_DESIGN_ASSET_VERSION', time());
} else {
    define('OMEGA_DESIGN_ASSET_SUFFIX', '.min');
    define('OMEGA_DESIGN_ASSET_VERSION', OMEGA_DESIGN_VERSION);
}

/**
 * Cache Busting Mode
 */
define('OMEGA_DESIGN_CACHE_BUSTING', OMEGA_DESIGN_DEV_MODE ? time() : OMEGA_DESIGN_VERSION);

/**
 * Memory Limits
 */
define('OMEGA_DESIGN_MEMORY_LIMIT', wp_convert_hr_to_bytes(ini_get('memory_limit')));
define('OMEGA_DESIGN_MAX_EXECUTION_TIME', ini_get('max_execution_time'));

/**
 * Image Limits
 */
define('OMEGA_DESIGN_MAX_IMAGE_WIDTH', 1920);
define('OMEGA_DESIGN_MAX_IMAGE_HEIGHT', 1080);
define('OMEGA_DESIGN_THUMBNAIL_WIDTH', 150);
define('OMEGA_DESIGN_THUMBNAIL_HEIGHT', 150);

/**
 * Query Limits
 */
define('OMEGA_DESIGN_POSTS_PER_PAGE', get_option('posts_per_page', 10));
define('OMEGA_DESIGN_MAX_POSTS_PER_REQUEST', 100);

// Verify required constants exist before loading
foreach (['OMEGA_DESIGN_INCLUDES', 'OMEGA_DESIGN_VERSION', 'OMEGA_DESIGN_DEV_MODE'] as $omega_design_constant) {
    if (!defined($omega_design_constant)) {
        wp_die(sprintf('Required constant %s is not defined', $omega_design_constant));
    }
}
unset($omega_design_constant);

if (!class_exists('\OmegaDesign\core\core')) {
    wp_die(sprintf('Core file not found: %s', OMEGA_DESIGN_INCLUDES . '/core/core.php'));
}

// Old global function names (child themes / custom snippets), each a thin
// wrapper around the class that now provides it.
require_once OMEGA_DESIGN_INCLUDES . '/compat/functions.php';

// Initialize theme - the loader boots every module, including
// OmegaDesign\core\theme_setup (nav menus, upload directories, palette).
\OmegaDesign\core\core::get_instance()->init();

// Hook to signal theme is ready
do_action('omega_design_theme_loaded');
