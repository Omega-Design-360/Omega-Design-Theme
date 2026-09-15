<?php
/**
 * Block Patterns Handler
 *
 * Registers the theme's block pattern categories (including the Mega Menu
 * category that appears in the Site Editor pattern area) and loads pattern
 * definition files from the theme's /pattern directory.
 *
 * Note: WordPress only auto-discovers patterns from a /patterns (plural)
 * folder. This theme ships its patterns in /pattern (singular), so they are
 * registered manually here from the file headers.
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class patterns {

    private static $instance = null;

    /**
     * Pattern category slug shown in the editor pattern area.
     */
    const CATEGORY_MEGAMENU = 'omega-design-megamenu';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'register_pattern_categories']);
        add_action('init', [$this, 'register_patterns']);
    }

    /**
     * Loader entry point.
     */
    public function init() {}

    /**
     * Register the theme's custom block pattern categories.
     *
     * The Mega Menu category groups all mega menu layouts together inside
     * the Site Editor > Patterns screen and the block inserter.
     */
    public function register_pattern_categories() {
        if (!function_exists('register_block_pattern_category')) {
            return;
        }

        register_block_pattern_category(
            self::CATEGORY_MEGAMENU,
            [
                'label'       => __('Mega Menu', 'omega-design'),
                'description' => __('Full-width category mega menu layouts for the site navigation.', 'omega-design'),
            ]
        );

        register_block_pattern_category(
            'omega-design-general',
            [
                'label'       => __('Omega Design', 'omega-design'),
                'description' => __('General layout patterns provided by the Omega Design theme.', 'omega-design'),
            ]
        );

        register_block_pattern_category(
            'omega-design-sections',
            [
                'label'       => __('Omega Design - Sections', 'omega-design'),
                'description' => __('Individual sections split out of the full landing pages, so any one of them can be inserted on its own, on any page.', 'omega-design'),
            ]
        );
    }

    /**
     * Register every pattern file found in the theme's /pattern directory.
     *
     * Each pattern file uses standard WordPress pattern file headers
     * (Title, Slug, Categories, etc.) so they read the same as core
     * /patterns files even though they are registered manually.
     */
    public function register_patterns() {
        if (!function_exists('register_block_pattern')) {
            return;
        }

        $pattern_dir = defined('OMEGA_DESIGN_PATTERN')
            ? OMEGA_DESIGN_PATTERN
            : get_template_directory() . '/pattern';

        if (!is_dir($pattern_dir)) {
            return;
        }

        $files = glob(trailingslashit($pattern_dir) . '*.php');
        if (empty($files)) {
            return;
        }

        foreach ($this->get_patterns_data($pattern_dir, $files) as $slug => $properties) {
            register_block_pattern($slug, $properties);
        }
    }

    /**
     * Building every pattern's content means running ~30 PHP files (some of
     * them full multi-section landing pages) through ob_start()/include on
     * EVERY single admin request - including, critically, the REST request
     * the core "start this new page from a pattern" popup itself makes to
     * fetch the list it renders previews from, which is exactly the
     * request that popup is waiting on. Caching the built result (as a
     * transient, so it survives across requests with no object-cache
     * plugin required) turns that into a single fast DB read instead.
     *
     * The cache key bakes in every pattern file's own mtime, so editing,
     * adding, or removing a pattern file automatically invalidates it and
     * the very next request rebuilds fresh - nothing to manually bust.
     */
    private function get_patterns_data($pattern_dir, $files) {
        $cache_key = 'omega_patterns_' . md5(implode('|', array_map(function ($file) {
            return $file . ':' . filemtime($file);
        }, $files)));

        $cached = get_transient($cache_key);
        if (is_array($cached)) {
            return $cached;
        }

        $default_headers = [
            'title'         => 'Title',
            'slug'          => 'Slug',
            'description'   => 'Description',
            'categories'    => 'Categories',
            'keywords'      => 'Keywords',
            'blockTypes'    => 'Block Types',
            'viewportWidth' => 'Viewport Width',
            'inserter'      => 'Inserter',
        ];

        $patterns_data = [];

        foreach ($files as $file) {
            $headers = get_file_data($file, $default_headers);

            if (empty($headers['slug']) || empty($headers['title'])) {
                continue;
            }

            // Capture the markup output of the pattern file.
            ob_start();
            include $file;
            $content = ob_get_clean();

            if ('' === trim($content)) {
                continue;
            }

            $properties = [
                'title'   => $headers['title'],
                'content' => $content,
            ];

            if (!empty($headers['description'])) {
                $properties['description'] = $headers['description'];
            }

            if (!empty($headers['categories'])) {
                $properties['categories'] = array_map('trim', explode(',', $headers['categories']));
            }

            if (!empty($headers['keywords'])) {
                $properties['keywords'] = array_map('trim', explode(',', $headers['keywords']));
            }

            if (!empty($headers['blockTypes'])) {
                $properties['blockTypes'] = array_map('trim', explode(',', $headers['blockTypes']));
            }

            if (!empty($headers['viewportWidth'])) {
                $properties['viewportWidth'] = (int) $headers['viewportWidth'];
            }

            if ('' !== $headers['inserter']) {
                $properties['inserter'] = in_array(
                    strtolower($headers['inserter']),
                    ['yes', 'true', '1'],
                    true
                );
            }

            $patterns_data[$headers['slug']] = $properties;
        }

        // A day is generous purely as a safety net (in case a pattern file
        // is somehow touched without its mtime changing) - the mtime-based
        // key above is what actually keeps this fresh in the normal case.
        set_transient($cache_key, $patterns_data, DAY_IN_SECONDS);

        return $patterns_data;
    }
}

patterns::get_instance();
