<?php
/**
 * Site-wide "Scroll Animation" block control. Adds an Advanced-panel
 * setting (Fade Up / Fade Down / Fade In / Slide Left / Slide Right / Zoom
 * In, with duration/delay) to every block via assets/js/scroll-animations-
 * editor.js, and plays it on the front end via assets/js/scroll-
 * animations.js (IntersectionObserver, so nothing plays until the element
 * actually scrolls into view).
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class scroll_animations {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor']);
        add_filter('render_block', [$this, 'inject_dynamic_block_attrs'], 20, 2);
        add_filter('register_block_type_args', [$this, 'add_animation_attributes'], 10, 2);
    }

    /** Must match EXCLUDED_BLOCKS in assets/js/scroll-animations-editor.js. */
    const EXCLUDED_BLOCKS = ['omega-design/tabs-item', 'core/template-part', 'core/navigation'];

    /**
     * Server half of scroll-animations-editor.js's attribute filter, the same
     * way blocks.php registers omegaResponsive. The editor adds these three
     * attributes to every block; without them in the server-side schema too,
     * any dynamic block the editor previews through the block-renderer REST
     * route (Tag Cloud, Archives, Calendar, RSS...) was rejected with
     * "omegaAnimation is not a valid property" and showed
     * "Error: [object Object]" instead of its preview.
     */
    public function add_animation_attributes($args, $block_type) {
        if (in_array($block_type, self::EXCLUDED_BLOCKS, true)) {
            return $args;
        }

        $args['attributes'] = array_merge($args['attributes'] ?? [], [
            'omegaAnimation'         => ['type' => 'string', 'default' => ''],
            'omegaAnimationDuration' => ['type' => 'number', 'default' => 600],
            'omegaAnimationDelay'    => ['type' => 'number', 'default' => 0],
        ]);

        return $args;
    }

    public function init() {}

    public function enqueue_frontend() {
        $css_path = OMEGA_DESIGN_ASSETS . '/css/scroll-animations.css';
        if (file_exists($css_path)) {
            wp_enqueue_style('omega-design-scroll-animations', OMEGA_DESIGN_CSS_URI . '/scroll-animations.css', [], filemtime($css_path));
        }

        $js_path = OMEGA_DESIGN_ASSETS . '/js/scroll-animations.js';
        if (file_exists($js_path)) {
            wp_enqueue_script('omega-design-scroll-animations', OMEGA_DESIGN_JS_URI . '/scroll-animations.js', [], filemtime($js_path), true);
        }
    }

    public function enqueue_editor() {
        $js_path = OMEGA_DESIGN_ASSETS . '/js/scroll-animations-editor.js';
        if (!file_exists($js_path)) {
            return;
        }

        wp_enqueue_script(
            'omega-design-scroll-animations-editor',
            OMEGA_DESIGN_JS_URI . '/scroll-animations-editor.js',
            ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-hooks', 'wp-i18n'],
            filemtime($js_path),
            true
        );
    }

    /**
     * blocks.getSaveContent.extraProps (the JS filter that stamps the
     * data-omega-animate-* attributes on) only ever runs for static blocks,
     * whose saved HTML comes straight from that JS save() output. Dynamic
     * blocks (render_callback-based - core/query, WooCommerce's blocks,
     * this theme's own omega-design/tabs) render fresh markup in PHP on
     * every request instead, from $block['attrs'] - so without this, giving
     * one of those blocks a scroll animation in the inspector would save
     * correctly but silently never actually animate on the front end.
     */
    public function inject_dynamic_block_attrs($block_content, $block) {
        $attrs = $block['attrs'] ?? [];
        $animation = $attrs['omegaAnimation'] ?? '';

        if ($animation === '' || trim((string) $block_content) === '') {
            return $block_content;
        }

        // Static blocks already carry this from their own save() output -
        // skip so it's never stamped onto the same wrapper twice.
        if (strpos($block_content, 'data-omega-animate=') !== false) {
            return $block_content;
        }

        $duration = isset($attrs['omegaAnimationDuration']) ? (int) $attrs['omegaAnimationDuration'] : 600;
        $delay    = isset($attrs['omegaAnimationDelay']) ? (int) $attrs['omegaAnimationDelay'] : 0;

        $extra = sprintf(
            ' class="omega-animate" data-omega-animate="%s" data-omega-animate-duration="%d" data-omega-animate-delay="%d"',
            esc_attr($animation),
            $duration,
            $delay
        );

        $replaced = preg_replace('/^(\s*<[a-z0-9]+)([^>]*)(>)/i', '$1$2' . $extra . '$3', $block_content, 1);

        return null === $replaced ? $block_content : $replaced;
    }
}
