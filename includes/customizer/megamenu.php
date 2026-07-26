<?php
/**
 * Mega Menu – registers the template-part area, enqueues assets,
 * injects trigger classes into navigation-link blocks, and ensures
 * a dedicated "Mega Menu" wp_navigation post exists.
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class megamenu {

    private static $instance = null;

    /**
     * Navigation-link labels / URL slugs that open a mega menu panel.
     * key = lowercase label or URL slug, value = panel CSS class.
     */
    private static $trigger_map = [
        'shop'      => 'omega-panel--shop',
        'resources' => 'omega-panel--resources',
        'blog'      => 'omega-panel--resources',
    ];

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter( 'default_wp_template_part_areas', [ $this, 'register_megamenu_area' ] );
        add_action( 'wp_enqueue_scripts',   [ $this, 'enqueue_assets' ] );
        add_action( 'enqueue_block_assets', [ $this, 'enqueue_assets' ] );
        add_filter( 'render_block',         [ $this, 'inject_trigger_class' ], 10, 2 );
        add_action( 'init',                 [ $this, 'ensure_megamenu_navigation' ] );
        add_filter( 'pre_render_block',     [ $this, 'inject_megamenu_nav_ref' ], 10, 2 );

        // Clean up old deduplication flag.
        add_action( 'init', static function () {
            delete_option( 'omega_nav_deduped_v1' );
        } );
    }

    public function init() {}

    /* ── Template-part area ─────────────────────────────────────── */

    public function register_megamenu_area( $areas ) {
        foreach ( $areas as $area ) {
            if ( $area['area'] === 'megamenu' ) {
                return $areas;
            }
        }
        $areas[] = [
            'area'        => 'megamenu',
            'area_tag'    => 'div',
            'label'       => __( 'Mega Menu', 'omega-design' ),
            'description' => __( 'Mega menu panels displayed inside the site header.', 'omega-design' ),
            'icon'        => 'menu',
        ];
        return $areas;
    }

    /* ── Assets ─────────────────────────────────────────────────── */

    public function enqueue_assets() {
        $ver = defined( 'OMEGA_DESIGN_VERSION' ) ? OMEGA_DESIGN_VERSION : null;

        $css_path = get_template_directory() . '/assets/css/megamenu.css';
        if ( file_exists( $css_path ) ) {
            wp_enqueue_style(
                'omega-design-megamenu',
                get_template_directory_uri() . '/assets/css/megamenu.css',
                [],
                $ver ?? filemtime( $css_path )
            );
        }

        if ( ! is_admin() ) {
            $js_path = get_template_directory() . '/assets/js/megamenu.js';
            if ( file_exists( $js_path ) ) {
                wp_enqueue_script(
                    'omega-design-megamenu',
                    get_template_directory_uri() . '/assets/js/megamenu.js',
                    [],
                    $ver ?? filemtime( $js_path ),
                    true
                );
            }
        }
    }

    /* ── Inject mega-menu trigger class into nav-link blocks ─────── */

    public function inject_trigger_class( $block_content, $block ) {
        if ( 'core/navigation-link' !== ( $block['blockName'] ?? '' ) ) {
            return $block_content;
        }

        $label    = strtolower( trim( $block['attrs']['label'] ?? '' ) );
        $url_slug = strtolower( trim( basename( rtrim( $block['attrs']['url'] ?? '', '/' ) ) ) );

        $panel_class = self::$trigger_map[ $label ]
                    ?? self::$trigger_map[ $url_slug ]
                    ?? null;

        if ( ! $panel_class ) {
            return $block_content;
        }

        return preg_replace(
            '/class="(wp-block-navigation-item\b[^"]*)"/',
            'class="$1 omega-megamenu-trigger ' . esc_attr( $panel_class ) . '"',
            $block_content,
            1
        );
    }

    /* ── Ensure a "Mega Menu" navigation post exists ─────────────── */

    public function ensure_megamenu_navigation() {
        $nav_id = (int) get_option( 'omega_megamenu_nav_id' );

        if ( $nav_id && 'publish' === get_post_status( $nav_id ) ) {
            return;
        }

        $new_id = wp_insert_post( [
            'post_type'    => 'wp_navigation',
            'post_title'   => 'Mega Menu',
            'post_status'  => 'publish',
            'post_content' => $this->build_initial_nav_content(),
        ] );

        if ( ! is_wp_error( $new_id ) ) {
            update_option( 'omega_megamenu_nav_id', $new_id );
        }
    }

    private function build_initial_nav_content() {
        $links = [];

        if ( function_exists( 'wc_get_page_id' ) ) {
            $woo = [
                'Shop'       => wc_get_page_id( 'shop' ),
                'Cart'       => wc_get_page_id( 'cart' ),
                'Checkout'   => wc_get_page_id( 'checkout' ),
                'My Account' => wc_get_page_id( 'myaccount' ),
            ];
            foreach ( $woo as $label => $page_id ) {
                if ( $page_id && $page_id > 0 ) {
                    $links[] = $this->nav_link_block( $label, $page_id );
                }
            }
        }

        if ( empty( $links ) ) {
            $pages = get_pages( [ 'number' => 6 ] );
            foreach ( $pages as $page ) {
                $links[] = $this->nav_link_block( $page->post_title, $page->ID );
            }
        }

        return implode( "\n", $links );
    }

    private function nav_link_block( $label, $page_id ) {
        return sprintf(
            '<!-- wp:navigation-link {"label":"%s","type":"page","id":%d,"url":"%s","kind":"post-type"} /-->',
            esc_attr( $label ),
            (int) $page_id,
            esc_url( get_permalink( $page_id ) )
        );
    }

    /* ── Inject saved nav ref into the mega menu navigation block ── */

    public function inject_megamenu_nav_ref( $pre_render, $parsed_block ) {
        if ( 'core/navigation' !== ( $parsed_block['blockName'] ?? '' ) ) {
            return $pre_render;
        }

        $classes = $parsed_block['attrs']['className'] ?? '';
        if ( strpos( $classes, 'omega-megamenu-navigation' ) === false ) {
            return $pre_render;
        }

        $nav_id = (int) get_option( 'omega_megamenu_nav_id' );
        if ( ! $nav_id ) {
            return $pre_render;
        }

        $parsed_block['attrs']['ref'] = $nav_id;

        // render_block() re-fires 'pre_render_block' internally; since the className
        // that triggers this filter is still present on $parsed_block, calling
        // render_block() here without unhooking first causes infinite recursion.
        remove_filter( 'pre_render_block', [ $this, 'inject_megamenu_nav_ref' ], 10 );
        $rendered = render_block( $parsed_block );
        add_filter( 'pre_render_block', [ $this, 'inject_megamenu_nav_ref' ], 10, 2 );

        return $rendered;
    }
}

megamenu::get_instance();
