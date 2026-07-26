<?php

/**
 * Theme Functions and Definitions
 * 
 * @package OmegaDesign
 * @author Your Name
 * @link https://yourwebsite.com
 */

defined('ABSPATH') || exit;

/**
 * Theme Basic Information
 */
define('OMEGA_DESIGN_VERSION', '1.0.0');
define('OMEGA_DESIGN_SLUG', 'omega-design');
define('OMEGA_DESIGN_TEXTDOMAIN', 'omega-design');
define('OMEGA_DESIGN_AUTHOR', 'Amjad Shahzad');
define('OMEGA_DESIGN_AUTHOR_URI', 'https://yourwebsite.com');

/**
 * Core Theme Paths
 */
define('OMEGA_DESIGN_DIR', get_template_directory());
define('OMEGA_DESIGN_URI', get_template_directory_uri());

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

/**
 * Remove WordPress core default color/gradient CSS variables from frontend output.
 * "defaultPalette: false" in theme.json only hides them from the editor UI;
 * this filter removes them from the default origin so no CSS vars are emitted.
 */
add_filter( 'wp_theme_json_data_default', function ( $theme_json ) {
    return $theme_json->update_with( [
        'version'  => 3,
        'settings' => [
            'color' => [
                'palette'         => [],
                'gradients'       => [],
                'duotone'         => [],
                'defaultPalette'  => false,
                'defaultGradients'=> false,
                'defaultDuotone'  => false,
            ],
        ],
    ] );
} );

/**
 * Create required directories on theme activation
 */
function omega_design_create_upload_directories() {
    $directories = [
        OMEGA_DESIGN_UPLOADS_THEME_DIR,
        OMEGA_DESIGN_UPLOADS_TEMP_DIR,
        OMEGA_DESIGN_UPLOADS_BACKUP_DIR,
        OMEGA_DESIGN_UPLOADS_IMAGES_DIR,
        OMEGA_DESIGN_UPLOADS_FONTS_DIR,
        OMEGA_DESIGN_UPLOADS_LOGS_DIR,
        OMEGA_DESIGN_UPLOADS_EXPORTS_DIR,
    ];
    
    foreach ($directories as $directory) {
        if (!file_exists($directory)) {
            wp_mkdir_p($directory);
            
            $index_file = $directory . '/index.php';
            if (!file_exists($index_file)) {
                file_put_contents($index_file, '<?php // Silence is golden.');
            }
            
            $htaccess_file = $directory . '/.htaccess';
            if (!file_exists($htaccess_file)) {
                file_put_contents($htaccess_file, "Options -Indexes\nDeny from all");
            }
        }
    }
}
add_action('after_switch_theme', 'omega_design_create_upload_directories');

/**
 * Get upload directory path
 */
function omega_design_get_upload_dir($subdir = '') {
    $path = OMEGA_DESIGN_UPLOADS_THEME_DIR;
    if (!empty($subdir)) {
        $path .= '/' . ltrim($subdir, '/');
    }
    
    if (!file_exists($path)) {
        wp_mkdir_p($path);
    }
    
    return $path;
}

/**
 * Get upload directory URL
 */
function omega_design_get_upload_url($subdir = '') {
    $url = OMEGA_DESIGN_UPLOADS_THEME_URL;
    if (!empty($subdir)) {
        $url .= '/' . ltrim($subdir, '/');
    }
    return $url;
}

/**
 * Get asset URL with version cache busting
 */
function omega_design_asset_url($path, $type = 'css') {
    $base_url = OMEGA_DESIGN_ASSETS_URI . '/' . $type;
    $url = $base_url . '/' . ltrim($path, '/');
    return add_query_arg('ver', OMEGA_DESIGN_ASSET_VERSION, $url);
}

/**
 * Get an asset URL versioned by the file's own modified time, so replacing
 * the file (e.g. swapping an icon/logo image) is reflected immediately
 * without needing a manual theme version bump or a hard browser refresh.
 */
function omega_design_versioned_asset_url($relative_path) {
    $relative_path = '/' . ltrim($relative_path, '/');
    $file_path = OMEGA_DESIGN_ASSETS . $relative_path;
    $version = file_exists($file_path) ? filemtime($file_path) : OMEGA_DESIGN_ASSET_VERSION;

    return add_query_arg('ver', $version, OMEGA_DESIGN_ASSETS_URI . $relative_path);
}

/**
 * Save file to theme uploads directory
 */
function omega_design_save_to_uploads($file_data, $filename, $subdir = '') {
    $upload_dir = omega_design_get_upload_dir($subdir);
    
    if (!file_exists($upload_dir)) {
        wp_mkdir_p($upload_dir);
    }
    
    $file_path = $upload_dir . '/' . sanitize_file_name($filename);
    
    if (file_exists($file_path) && OMEGA_DESIGN_DEV_MODE) {
        $backup_path = $upload_dir . '/backup_' . time() . '_' . $filename;
        copy($file_path, $backup_path);
    }
    
    $result = file_put_contents($file_path, $file_data);
    
    if ($result !== false) {
        return $file_path;
    }
    
    return false;
}

/**
 * Delete file from theme uploads directory
 */
function omega_design_delete_from_uploads($filename, $subdir = '') {
    $upload_dir = omega_design_get_upload_dir($subdir);
    $file_path = $upload_dir . '/' . sanitize_file_name($filename);
    
    if (file_exists($file_path) && is_writable($file_path)) {
        return unlink($file_path);
    }
    
    return false;
}

/**
 * Get file from theme uploads directory
 */
function omega_design_get_from_uploads($filename, $subdir = '') {
    $upload_dir = omega_design_get_upload_dir($subdir);
    $file_path = $upload_dir . '/' . sanitize_file_name($filename);
    
    if (file_exists($file_path) && is_readable($file_path)) {
        return file_get_contents($file_path);
    }
    
    return false;
}

/**
 * Write debug log to theme uploads folder
 */
function omega_design_log($data, $type = 'debug') {
    if (!OMEGA_DESIGN_DEV_MODE) {
        return;
    }
    
    $log_dir = OMEGA_DESIGN_UPLOADS_LOGS_DIR;
    if (!file_exists($log_dir)) {
        wp_mkdir_p($log_dir);
    }
    
    $log_file = $log_dir . '/' . gmdate('Y-m-d') . '-' . $type . '.log';
    $log_entry = '[' . gmdate('Y-m-d H:i:s') . '] ' . print_r($data, true) . PHP_EOL;
    error_log($log_entry, 3, $log_file);
}

// Verify required constants exist before loading
$required_constants = [
    'OMEGA_DESIGN_INCLUDES',
    'OMEGA_DESIGN_VERSION',
    'OMEGA_DESIGN_DEV_MODE',
];

foreach ($required_constants as $constant) {
    if (!defined($constant)) {
        wp_die(sprintf('Required constant %s is not defined', $constant));
    }
}

// Load the core class
$core_file = OMEGA_DESIGN_INCLUDES . '/core/core.php';
if (!file_exists($core_file)) {
    wp_die(sprintf('Core file not found: %s', $core_file));
}

require_once $core_file;

// Initialize theme
function omega_design_initialize() {
    if (class_exists('\OmegaDesign\core\core')) {
        return \OmegaDesign\core\core::get_instance()->init();
    }
    return null;
}
omega_design_initialize();

// Debug helper
function omega_design_debug($data) {
    if (OMEGA_DESIGN_DEV_MODE) {
        omega_design_log($data, 'debug');
    }
}

/**
 * Check WordPress version compatibility
 */
function omega_design_check_wp_compatibility() {
    global $wp_version;
    
    $required_wp_version = '5.8';
    if (version_compare($wp_version, $required_wp_version, '<')) {
        add_action('admin_notices', function() use ($required_wp_version) {
            ?>
            <div class="notice notice-warning">
                <p><?php echo sprintf(esc_html__('Omega Design Theme requires WordPress version %s or higher. Please update WordPress.', 'omega-design'), esc_html($required_wp_version)); ?></p>
            </div>
            <?php
        });
        return false;
    }
    return true;
}
add_action('init', 'omega_design_check_wp_compatibility');

/**
 * Check PHP version compatibility
 */
function omega_design_check_php_compatibility() {
    $required_php_version = '7.4';
    if (version_compare(PHP_VERSION, $required_php_version, '<')) {
        add_action('admin_notices', function() use ($required_php_version) {
            ?>
            <div class="notice notice-warning">
                <p><?php echo sprintf(esc_html__('Omega Design Theme requires PHP version %s or higher. Please contact your hosting provider to upgrade PHP.', 'omega-design'), esc_html($required_php_version)); ?></p>
            </div>
            <?php
        });
        return false;
    }
    return true;
}
add_action('init', 'omega_design_check_php_compatibility');

// Hook to signal theme is ready
do_action('omega_design_theme_loaded');

/**
 * Register Mega Menu nav menu locations
 */
add_action('after_setup_theme', function() {
    register_nav_menus([
        'megamenu' => __('Mega Menu', 'omega-design'),
        'primary'  => __('Primary Menu', 'omega-design'),
        'footer'   => __('Footer Menu', 'omega-design'),
    ]);
});