<?php
/**
 * Theme Uploads Directory Helpers
 *
 * Everything the theme writes to disk lives under
 * wp-content/uploads/omega-design/ (OMEGA_DESIGN_UPLOADS_THEME_DIR).
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class uploads {

    const DENY_ALL_HTACCESS = "Options -Indexes\nDeny from all";

    /**
     * Theme upload subdirectories created on theme activation.
     */
    public static function directories() {
        return [
            OMEGA_DESIGN_UPLOADS_THEME_DIR,
            OMEGA_DESIGN_UPLOADS_TEMP_DIR,
            OMEGA_DESIGN_UPLOADS_BACKUP_DIR,
            OMEGA_DESIGN_UPLOADS_IMAGES_DIR,
            OMEGA_DESIGN_UPLOADS_FONTS_DIR,
            OMEGA_DESIGN_UPLOADS_LOGS_DIR,
            OMEGA_DESIGN_UPLOADS_EXPORTS_DIR,
        ];
    }

    /**
     * Create required directories on theme activation.
     */
    public static function create_directories() {
        foreach (self::directories() as $directory) {
            if (file_exists($directory)) {
                continue;
            }

            wp_mkdir_p($directory);
            self::protect_directory($directory, self::DENY_ALL_HTACCESS);
        }
    }

    /**
     * Drops a "silence is golden" index.php and an .htaccess with $htaccess
     * into $dir, leaving either one alone if it already exists.
     */
    public static function protect_directory($dir, $htaccess) {
        self::write_if_missing($dir . '/index.php', '<?php // Silence is golden.');
        self::write_if_missing($dir . '/.htaccess', $htaccess);
    }

    private static function write_if_missing($file, $contents) {
        if (!file_exists($file)) {
            file_put_contents($file, $contents);
        }
    }

    /**
     * Get upload directory path, creating it if needed.
     */
    public static function get_dir($subdir = '') {
        $path = self::append_subdir(OMEGA_DESIGN_UPLOADS_THEME_DIR, $subdir);

        if (!file_exists($path)) {
            wp_mkdir_p($path);
        }

        return $path;
    }

    /**
     * Get upload directory URL.
     */
    public static function get_url($subdir = '') {
        return self::append_subdir(OMEGA_DESIGN_UPLOADS_THEME_URL, $subdir);
    }

    private static function append_subdir($base, $subdir) {
        return empty($subdir) ? $base : $base . '/' . ltrim($subdir, '/');
    }

    private static function file_path($filename, $subdir = '') {
        return self::get_dir($subdir) . '/' . sanitize_file_name($filename);
    }

    /**
     * Save file to theme uploads directory. Returns the saved path, or false.
     */
    public static function save($file_data, $filename, $subdir = '') {
        $upload_dir = self::get_dir($subdir);
        $file_path  = self::file_path($filename, $subdir);

        if (file_exists($file_path) && OMEGA_DESIGN_DEV_MODE) {
            copy($file_path, $upload_dir . '/backup_' . time() . '_' . $filename);
        }

        return false !== file_put_contents($file_path, $file_data) ? $file_path : false;
    }

    /**
     * Delete file from theme uploads directory.
     */
    public static function delete($filename, $subdir = '') {
        $file_path = self::file_path($filename, $subdir);

        if (file_exists($file_path) && is_writable($file_path)) {
            return unlink($file_path);
        }

        return false;
    }

    /**
     * Get file contents from theme uploads directory.
     */
    public static function get($filename, $subdir = '') {
        $file_path = self::file_path($filename, $subdir);

        if (file_exists($file_path) && is_readable($file_path)) {
            return file_get_contents($file_path);
        }

        return false;
    }

    /**
     * Write a debug log entry to the theme uploads logs folder (dev mode only).
     */
    public static function log($data, $type = 'debug') {
        if (!OMEGA_DESIGN_DEV_MODE) {
            return;
        }

        $log_dir = OMEGA_DESIGN_UPLOADS_LOGS_DIR;
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }

        $log_file  = $log_dir . '/' . gmdate('Y-m-d') . '-' . $type . '.log';
        $log_entry = '[' . gmdate('Y-m-d H:i:s') . '] ' . print_r($data, true) . PHP_EOL;
        error_log($log_entry, 3, $log_file);
    }
}
