<?php
/**
 * Global / per-page / per-template color scheme.
 *
 * Every page, post and template can choose which palette it uses, from the
 * "Color Scheme" panel in the editor sidebar (assets/js/color-scheme-panel.js):
 *
 * - Default: what applies today - a landing page's content uses its own
 *   built-in scheme (assets/css/landing-{slug}.css), everything else uses
 *   the site's global palette (Styles > Colors).
 * - Global: the site's global palette everywhere, including inside a
 *   landing page's content (its built-in scheme is switched off).
 * - Custom: this page's own palette, applied to the WHOLE page - header,
 *   content and footer. Colors left empty keep their Default.
 *
 * A page/post's own setting (post meta) wins over its template's setting
 * (one site option keyed by template slug), which wins over Default.
 *
 * Output: body classes (omega-page-scheme-global/-custom, plus
 * omega-scheme-active, which the landing stylesheets use to swap their
 * fixed decorative colors for palette-derived ones) and, for Custom, one
 * inline <style> overriding the preset color variables on <body> and on
 * any landing page wrapper inside it.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class color_scheme {

    private static $instance = null;

    const META_KEY   = '_omega_color_scheme';
    const OPTION_KEY = 'omega_template_color_schemes';

    /** Palette slugs a scheme can override (same list as color-scheme-panel.js). */
    const SLUGS = [
        'primary', 'secondary', 'accent', 'heading', 'body-text', 'background', 'page-background',
        'surface', 'border', 'button-text', 'button-background', 'success', 'warning', 'danger',
    ];

    /** Resolved scheme for the current request (false = not resolved yet). */
    private $resolved = false;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'register_storage']);
        add_filter('body_class', [$this, 'add_body_classes']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_css'], 20);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_panel']);
        add_action('update_option_' . self::OPTION_KEY, [$this, 'flush_page_cache']);
        add_action('add_option_' . self::OPTION_KEY, [$this, 'flush_page_cache']);
    }

    public function init() {}

    private function scheme_schema() {
        return [
            'type'       => 'object',
            'properties' => [
                'mode'   => ['type' => 'string', 'enum' => ['', 'global', 'custom']],
                'colors' => [
                    'type'                 => 'object',
                    'additionalProperties' => ['type' => 'string'],
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    public function register_storage() {
        register_post_meta('', self::META_KEY, [
            'single'            => true,
            'type'              => 'object',
            'default'           => [],
            'show_in_rest'      => ['schema' => $this->scheme_schema()],
            'sanitize_callback' => [$this, 'sanitize_scheme'],
            'auth_callback'     => function ($allowed, $meta_key, $post_id) {
                return current_user_can('edit_post', $post_id);
            },
        ]);

        register_setting('omega_design', self::OPTION_KEY, [
            'type'              => 'object',
            'default'           => [],
            'show_in_rest'      => [
                'schema' => [
                    'type'                 => 'object',
                    'additionalProperties' => $this->scheme_schema(),
                ],
            ],
            'sanitize_callback' => [$this, 'sanitize_template_schemes'],
        ]);
    }

    /** Keeps only a known mode and palette colors whose values are plain CSS colors. */
    public function sanitize_scheme($scheme) {
        if (!is_array($scheme)) {
            return [];
        }

        $mode = in_array($scheme['mode'] ?? '', ['global', 'custom'], true) ? $scheme['mode'] : '';
        $out  = $mode ? ['mode' => $mode] : [];

        if (!empty($scheme['colors']) && is_array($scheme['colors'])) {
            $colors = [];
            foreach (self::SLUGS as $slug) {
                $color = $this->sanitize_color($scheme['colors'][$slug] ?? '');
                if ($color !== '') {
                    $colors[$slug] = $color;
                }
            }
            if ($colors) {
                $out['colors'] = $colors;
            }
        }

        return $out;
    }

    public function sanitize_template_schemes($schemes) {
        if (!is_array($schemes)) {
            return [];
        }

        $out = [];
        foreach ($schemes as $slug => $scheme) {
            $slug   = sanitize_key($slug);
            $scheme = $this->sanitize_scheme($scheme);
            if ($slug !== '' && !empty($scheme['mode'])) {
                $out[$slug] = $scheme;
            }
        }
        return $out;
    }

    private function sanitize_color($value) {
        $value = trim((string) $value);
        return preg_match('/^(#[0-9a-fA-F]{3,8}|rgba?\([\d\s.,%]+\)|hsla?\([\d\s.,%deg]+\))$/', $value) ? $value : '';
    }

    /** Slug of the block template rendering this request, e.g. "page" or "single". */
    private function current_template_slug() {
        global $_wp_current_template_id;
        if (empty($_wp_current_template_id) || false === strpos($_wp_current_template_id, '//')) {
            return '';
        }
        return sanitize_key(substr($_wp_current_template_id, strpos($_wp_current_template_id, '//') + 2));
    }

    /** The scheme in effect for this request: page/post setting, else template setting, else none. */
    public function get_active_scheme() {
        if (false !== $this->resolved) {
            return $this->resolved;
        }

        $scheme = [];

        if (is_singular()) {
            $scheme = $this->sanitize_scheme(get_post_meta(get_queried_object_id(), self::META_KEY, true));
        }

        if (empty($scheme['mode'])) {
            $templates = get_option(self::OPTION_KEY, []);
            $slug      = $this->current_template_slug();
            if ($slug && is_array($templates) && !empty($templates[$slug])) {
                $scheme = $this->sanitize_scheme($templates[$slug]);
            }
        }

        $this->resolved = empty($scheme['mode']) ? [] : $scheme;
        return $this->resolved;
    }

    public function add_body_classes($classes) {
        $scheme = $this->get_active_scheme();
        if (!empty($scheme['mode'])) {
            $classes[] = 'omega-page-scheme-' . $scheme['mode'];
            $classes[] = 'omega-scheme-active';
        }
        return $classes;
    }

    /**
     * Custom colors, written for <body> (header, content, footer) and for
     * any landing page wrapper inside it (which otherwise re-declares its
     * own built-in scheme). !important so they win over both that and the
     * dark-mode remap in color-mode.css.
     */
    public static function build_custom_css($colors, $body_selector = 'body.omega-page-scheme-custom') {
        $declarations = '';
        foreach (self::SLUGS as $slug) {
            if (!empty($colors[$slug])) {
                $declarations .= '--wp--preset--color--' . $slug . ':' . $colors[$slug] . ' !important;';
            }
        }

        if ($declarations === '') {
            return '';
        }

        return $body_selector . ',' . $body_selector . ' .omega-landing{' . $declarations . '}';
    }

    public function enqueue_frontend_css() {
        $scheme = $this->get_active_scheme();
        if (($scheme['mode'] ?? '') !== 'custom' || empty($scheme['colors'])) {
            return;
        }

        $css = self::build_custom_css($scheme['colors']);
        if ($css !== '') {
            wp_add_inline_style('omega-design-style', $css);
        }
    }

    /** Sidebar panel - post editor and Site Editor only. */
    public function enqueue_editor_panel() {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || !in_array($screen->base, ['post', 'site-editor'], true)) {
            return;
        }

        $path = OMEGA_DESIGN_ASSETS . '/js/color-scheme-panel.js';
        if (!file_exists($path)) {
            return;
        }

        wp_enqueue_script(
            'omega-design-color-scheme-panel',
            OMEGA_DESIGN_JS_URI . '/color-scheme-panel.js',
            ['wp-plugins', 'wp-editor', 'wp-data', 'wp-core-data', 'wp-components', 'wp-element', 'wp-i18n'],
            filemtime($path),
            true
        );

        wp_localize_script('omega-design-color-scheme-panel', 'omegaColorScheme', [
            'metaKey'   => self::META_KEY,
            'optionKey' => self::OPTION_KEY,
            'slugs'     => self::SLUGS,
            'canManage' => current_user_can('manage_options'),
        ]);
    }

    public function flush_page_cache() {
        if (class_exists(__NAMESPACE__ . '\\page_cache')) {
            page_cache::get_instance()->flush();
        }
    }
}
