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

use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class patterns {

    use singleton;

    /**
     * Pattern category slug shown in the editor pattern area.
     */
    const CATEGORY_MEGAMENU = 'omega-design-megamenu';

    private function __construct() {
        add_action('init', [$this, 'register_pattern_categories']);
        add_action('init', [$this, 'register_patterns']);
    }

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

        foreach (self::pattern_categories() as $slug => $properties) {
            register_block_pattern_category($slug, $properties);
        }
    }

    private static function pattern_categories() {
        return [
            self::CATEGORY_MEGAMENU => [
                'label'       => __('Mega Menu', 'omega-design'),
                'description' => __('Full-width category mega menu layouts for the site navigation.', 'omega-design'),
            ],
            'omega-design-general' => [
                'label'       => __('Omega Design', 'omega-design'),
                'description' => __('General layout patterns provided by the Omega Design theme.', 'omega-design'),
            ],
            'omega-design-sections' => [
                'label'       => __('Omega Design - Sections', 'omega-design'),
                'description' => __('Individual sections split out of the full landing pages, so any one of them can be inserted on its own, on any page.', 'omega-design'),
            ],
            'omega-design-shop' => [
                'label'       => __('Shop', 'omega-design'),
                'description' => __('WooCommerce shop sections - product grids with filters, category pills, a hero banner, an editorial grid and a compact list - for any page.', 'omega-design'),
            ],
        ];
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

        // Front-end page views never list or insert patterns - they only
        // need one if the page itself references it by slug (a core/pattern
        // block, e.g. in a user-edited template), so skip the ~30 pattern
        // registrations there and load them only when such a block renders.
        // wp-admin and REST (the editor's inserter/pattern modal) always get
        // the full set: rest_api_init fires before any REST route runs.
        if (!is_admin() && !wp_doing_ajax() && !(defined('WP_CLI') && WP_CLI)) {
            add_action('rest_api_init', [$this, 'register_patterns_now'], 0);
            add_filter('pre_render_block', [$this, 'register_for_pattern_block'], 10, 2);
            return;
        }

        $this->register_patterns_now();
    }

    private $patterns_registered = false;

    public function register_for_pattern_block($pre_render, $parsed_block) {
        if ('core/pattern' === ($parsed_block['blockName'] ?? '')
            && 0 === strpos((string) ($parsed_block['attrs']['slug'] ?? ''), 'omega-design/')
        ) {
            $this->register_patterns_now();
        }
        return $pre_render;
    }

    public function register_patterns_now() {
        if ($this->patterns_registered) {
            return;
        }
        $this->patterns_registered = true;

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
        $cache_key = self::patterns_cache_key($files);

        $cached = self::read_patterns_cache($cache_key);
        if (is_array($cached)) {
            return $cached;
        }

        $patterns_data = [];

        foreach ($files as $file) {
            $headers = get_file_data($file, self::PATTERN_HEADERS);

            if (empty($headers['slug']) || empty($headers['title'])) {
                continue;
            }

            // Capture the markup output of the pattern file. Kept inline
            // (not in a helper method) so every pattern file keeps running
            // in this same scope.
            ob_start();
            include $file;
            $content = ob_get_clean();

            if ('' === trim($content)) {
                continue;
            }

            $patterns_data[$headers['slug']] = self::pattern_properties($headers, $content);
        }

        self::write_patterns_cache($cache_key, $patterns_data);

        return $patterns_data;
    }

    /** Pattern file header => get_file_data() key. */
    const PATTERN_HEADERS = [
        'title'         => 'Title',
        'slug'          => 'Slug',
        'description'   => 'Description',
        'categories'    => 'Categories',
        'keywords'      => 'Keywords',
        'blockTypes'    => 'Block Types',
        'viewportWidth' => 'Viewport Width',
        'inserter'      => 'Inserter',
    ];

    /**
     * Changes whenever a pattern file - or a templates/shop-*.html file the
     * Shop section patterns are built from (see pattern_helpers::
     * shop_section()) - is added, removed or edited.
     */
    private static function patterns_cache_key(array $files) {
        $source_files = array_merge($files, (array) glob(get_template_directory() . '/templates/shop-*.html'));

        return 'omega_patterns_' . md5(implode('|', array_map(function ($file) {
            return $file . ':' . filemtime($file);
        }, $source_files)));
    }

    private static function read_patterns_cache($cache_key) {
        $cached = get_transient($cache_key);
        if (is_string($cached) && function_exists('gzuncompress')) {
            $inflated = @gzuncompress((string) base64_decode($cached, true));
            $cached   = false === $inflated ? false : @unserialize($inflated, ['allowed_classes' => false]);
        }
        return $cached;
    }

    /**
     * A day is generous purely as a safety net (in case a pattern file is
     * somehow touched without its mtime changing) - the mtime-based key is
     * what actually keeps this fresh in the normal case.
     *
     * Stored compressed: all patterns' markup together is well over a
     * megabyte, and MySQL's default max_allowed_packet (1 MB on many hosts
     * and on XAMPP) rejects a single option that large - the cache then
     * silently never saved. Compressed it is ~10x smaller.
     */
    private static function write_patterns_cache($cache_key, array $patterns_data) {
        $to_store = function_exists('gzcompress')
            ? base64_encode(gzcompress(serialize($patterns_data), 6))
            : $patterns_data;
        set_transient($cache_key, $to_store, DAY_IN_SECONDS);
    }

    /**
     * register_block_pattern() properties from a pattern file's headers and
     * rendered markup.
     */
    private static function pattern_properties(array $headers, $content) {
        $properties = [
            'title'   => $headers['title'],
            'content' => $content,
        ];

        if (!empty($headers['description'])) {
            $properties['description'] = $headers['description'];
        }

        foreach (['categories', 'keywords', 'blockTypes'] as $list_header) {
            if (!empty($headers[$list_header])) {
                $properties[$list_header] = array_map('trim', explode(',', $headers[$list_header]));
            }
        }

        if (!empty($headers['viewportWidth'])) {
            $properties['viewportWidth'] = (int) $headers['viewportWidth'];
        }

        if ('' !== $headers['inserter']) {
            $properties['inserter'] = in_array(strtolower($headers['inserter']), ['yes', 'true', '1'], true);
        }

        return $properties;
    }
}

patterns::get_instance();
