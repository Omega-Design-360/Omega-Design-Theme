<?php
/**
 * Site-wide page-load progress bar - a slim, animated bar at the very top
 * of every front-end page (the same idea as YouTube/GitHub's own), so a
 * page that's slow to finish loading (a heavy hero image, a third-party
 * script, a slow connection) gives the visitor something moving to look at
 * instead of a static screen with no feedback at all.
 *
 * Deliberately just a visual indicator, not a content gate: the page
 * renders normally underneath it the whole time (nothing is hidden or
 * blocked waiting for "ready"), so this can't turn a slow resource into a
 * blank page, and doesn't affect how fast real content is visible to a
 * visitor or a search engine.
 *
 * The bar's own CSS and starter script are both inlined (wp_head/
 * wp_body_open) rather than enqueued as separate files - the whole point
 * is that it's visible from the very first paint, before any external
 * stylesheet has even started downloading.
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
                top: 0;
                left: 0;
                width: 0%;
                height: 3px;
                background: var(--wp--preset--color--primary, #1fbb00);
                z-index: 999999;
                transition: width 0.25s ease, opacity 0.3s ease 0.1s;
                pointer-events: none;
            }
            @media (prefers-reduced-motion: reduce) {
                #omega-loading-bar {
                    transition: opacity 0.3s ease;
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
        <div id="omega-loading-bar"></div>
        <script>
        (function () {
            var bar = document.getElementById('omega-loading-bar');
            if (!bar) { return; }

            var progress = 0;
            var eased = window.setInterval(function () {
                // Eases toward 90% and stalls there - it only ever reaches
                // 100% once the page has actually finished loading (below),
                // so it never lies about being done early on a slow page.
                progress += (90 - progress) * 0.1;
                bar.style.width = progress + '%';
            }, 200);

            function finish() {
                window.clearInterval(eased);
                bar.style.width = '100%';
                window.setTimeout(function () {
                    bar.style.opacity = '0';
                    window.setTimeout(function () {
                        if (bar.parentNode) { bar.parentNode.removeChild(bar); }
                    }, 400);
                }, 200);
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
