<?php
/**
 * Singleton Trait
 *
 * One shared instance per using class - PHP gives every class that uses a
 * trait its own copy of the trait's static properties, so each module still
 * keeps its own separate instance.
 *
 * @package OmegaDesign\traits
 */

namespace OmegaDesign\traits;

defined('ABSPATH') || exit;

trait singleton {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Called by the loader after construction. Modules that wire their
     * hooks in the constructor simply inherit this no-op.
     */
    public function init() {}
}
