<?php
/**
 * Named "pick a look" block style variations (Button: Ghost/Soft/Pill/3D;
 * Group: Card/Bordered/Shadow; Image: Rounded/Circle/Framed) - the same
 * kind of one-click component-style swatches Squarespace/Webflow offer,
 * built on WordPress's own register_block_style() so they show up right in
 * each block's native Styles tab, no separate UI to build or maintain.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

use OmegaDesign\traits\assets;
use OmegaDesign\traits\singleton;

defined('ABSPATH') || exit;

class block_style_variations {

    use singleton;
    use assets;

    private function __construct() {
        add_action('init', [$this, 'register_styles']);
        add_action('enqueue_block_assets', [$this, 'enqueue_assets']);
    }

    public function register_styles() {
        $styles = [
            'core/button' => [
                ['name' => 'omega-ghost', 'label' => __('Ghost', 'omega-design')],
                ['name' => 'omega-soft', 'label' => __('Soft', 'omega-design')],
                ['name' => 'omega-pill', 'label' => __('Pill', 'omega-design')],
                ['name' => 'omega-3d', 'label' => __('3D', 'omega-design')],
            ],
            'core/group' => [
                ['name' => 'omega-card', 'label' => __('Card', 'omega-design')],
                ['name' => 'omega-bordered', 'label' => __('Bordered', 'omega-design')],
                ['name' => 'omega-shadow', 'label' => __('Shadow', 'omega-design')],
            ],
            'core/image' => [
                ['name' => 'omega-rounded', 'label' => __('Rounded', 'omega-design')],
                ['name' => 'omega-circle', 'label' => __('Circle', 'omega-design')],
                ['name' => 'omega-framed', 'label' => __('Framed', 'omega-design')],
            ],
        ];

        foreach ($styles as $block_name => $block_styles) {
            foreach ($block_styles as $style) {
                register_block_style($block_name, $style);
            }
        }
    }

    /**
     * enqueue_block_assets (not wp_enqueue_scripts) so the same rules load
     * both on the front end and inside the block editor canvas - a style
     * variation should look the same the moment it's picked, not only
     * after publishing.
     */
    public function enqueue_assets() {
        self::enqueue_style('omega-design-block-style-variations', 'css/block-style-variations.css');
    }
}
