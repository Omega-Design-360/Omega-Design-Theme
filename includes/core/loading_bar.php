<?php
/**
 * Site-wide page-load indicator - three small pulsing dots centered on the
 * screen while a page finishes loading, so a page that's slow (a heavy hero
 * image, a third-party script, a slow connection) gives the visitor
 * something moving to look at instead of a static screen with no feedback.
 *
 * Deliberately just a visual indicator, not a content gate: the page
 * renders normally underneath it the whole time (nothing is hidden or
 * blocked waiting for "ready"), so this can't turn a slow resource into a
 * blank page, and doesn't affect how fast real content is visible to a
 * visitor or a search engine.
 *
 * The markup/CSS/starter script are all inlined (wp_head/wp_body_open)
 * rather than enqueued as separate files - the whole point is that it's
 * visible from the very first paint, before any external stylesheet has
 * even started downloading.
 *
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class loading_bar {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_head', [$this, 'output_styles'], 1);
        add_action('wp_body_open', [$this, 'output_markup'], 1);
    }

    public function init() {}

    private function should_output() {
        if (is_admin() || is_customize_preview()) {
            return false;
        }
        return apply_filters('omega_design_show_loading_bar', true);
    }

    public function output_styles() {
        if (!$this->should_output()) {
            return;
        }
        ?>
        <style id="omega-loading-bar-style">
            #omega-loading-bar {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                display: flex;
                align-items: center;
                gap: 10px;
                z-index: 999999;
                transition: opacity 0.35s ease;
                pointer-events: none;
            }
            #omega-loading-bar span {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: var(--wp--preset--color--primary, #1fbb00);
                animation: omega-loading-dot-pulse 1s ease-in-out infinite;
            }
            #omega-loading-bar span:nth-child(2) { animation-delay: 0.15s; }
            #omega-loading-bar span:nth-child(3) { animation-delay: 0.3s; }
            @keyframes omega-loading-dot-pulse {
                0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
                40% { transform: scale(1); opacity: 1; }
            }
            @media (prefers-reduced-motion: reduce) {
                #omega-loading-bar span {
                    animation: none;
                    opacity: 0.8;
                }
            }
        </style>
        <?php
    }

    public function output_markup() {
        if (!$this->should_output()) {
            return;
        }
        ?>
        <div id="omega-loading-bar"><span></span><span></span><span></span></div>
        <script>
        (function () {
            var bar = document.getElementById('omega-loading-bar');
            if (!bar) { return; }

            /*
             * A page that finishes loading almost instantly (the common case
             * on localhost, or any fast connection) would otherwise show
             * this for only a handful of milliseconds - technically present,
             * but too brief to actually register as "a loading indicator"
             * to a real person watching the screen. Holding it for at least
             * this long regardless of how fast the page actually loads is
             * what makes it reliably visible rather than a rare flash.
             */
            var MIN_VISIBLE_MS = 500;
            var shownAt = Date.now();

            function finish() {
                var wait = Math.max(0, MIN_VISIBLE_MS - (Date.now() - shownAt));
                window.setTimeout(function () {
                    bar.style.opacity = '0';
                    window.setTimeout(function () {
                        if (bar.parentNode) { bar.parentNode.removeChild(bar); }
                    }, 400);
                }, wait);
            }

            if (document.readyState === 'complete') {
                finish();
            } else {
                window.addEventListener('load', finish);
            }
        })();
        </script>
        <?php
    }
}
