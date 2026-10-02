<?php
/**
 * Public Asset URL Helpers
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

use OmegaDesign\traits\assets;

defined('ABSPATH') || exit;

class asset_urls {

    use assets;

    /**
     * Asset URL with the theme's version as cache-buster, e.g.
     * asset_urls::get('admin.css', 'css').
     */
    public static function get($path, $type = 'css') {
        return add_query_arg('ver', OMEGA_DESIGN_ASSET_VERSION, self::asset_uri($type . '/' . ltrim($path, '/')));
    }

    /**
     * Asset URL versioned by the file's own modified time, so replacing the
     * file (e.g. swapping an icon/logo image) is reflected immediately
     * without a theme version bump or a hard browser refresh.
     */
    public static function versioned($relative_path) {
        return add_query_arg('ver', self::asset_version($relative_path), self::asset_uri($relative_path));
    }
}
