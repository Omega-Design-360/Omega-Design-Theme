<?php
/**
 * Classic Header - WooCommerce cart + account icons.
 *
 * Automatic, not a setting: shows only when WooCommerce is actually
 * active on the site (OMEGA_DESIGN_WOOCOMMERCE_ACTIVE, functions.php),
 * nothing to configure either way. Renders directly into
 * classic_header.php's own markup (see its $wc_icons_html), right after
 * the nav on desktop and right before the mobile toggle button, so it
 * only ever needs to exist where a Classic style is already active.
 *
 * @package OmegaDesign\customizer
 */

namespace OmegaDesign\customizer;

defined('ABSPATH') || exit;

class woocommerce_header {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    public function init() {}

    public static function is_active() {
        return defined('OMEGA_DESIGN_WOOCOMMERCE_ACTIVE')
            ? OMEGA_DESIGN_WOOCOMMERCE_ACTIVE
            : class_exists('WooCommerce');
    }

    /**
     * Empty string whenever WooCommerce isn't active - classic_header.php
     * treats that exactly like "no icons", same as it already does for an
     * unset nav menu, so nothing extra renders and no empty wrapper is
     * left behind in the markup.
     */
    public function icons_html() {
        if (!self::is_active()) {
            return '';
        }

        $account_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : '';
        $mini_cart   = $this->mini_cart_html();

        if ('' === $account_url && '' === $mini_cart) {
            return '';
        }

        ob_start();
        ?>
        <div class="omega-classic-header__wc-icons">
            <?php if ('' !== $account_url) : ?>
                <a class="omega-classic-header__wc-icon" href="<?php echo esc_url($account_url); ?>" aria-label="<?php esc_attr_e('My Account', 'omega-design'); ?>">
                    <?php echo $this->account_icon_svg(); ?>
                </a>
            <?php endif; ?>
            <?php echo $mini_cart; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * The real WooCommerce Mini Cart block - the exact same slide-out
     * drawer (empty-cart state, item list, "Start shopping"/checkout,
     * live item count badge) the Site Editor's own block inserter offers,
     * rather than a hand-rolled cart link. Rendered the same way any
     * theme embeds a block outside post content - do_blocks() runs it
     * through WordPress's normal block-rendering pipeline (parse + each
     * block's own render_callback), which is what lets the block's own
     * assets (wc-blocks-*, its React-driven drawer) register and hydrate
     * correctly; a bare render_block() call skips parts of that pipeline
     * and was observed to return nothing.
     */
    private function mini_cart_html() {
        if (!function_exists('do_blocks') || !\WP_Block_Type_Registry::get_instance()->is_registered('woocommerce/mini-cart')) {
            return $this->fallback_cart_link_html();
        }

        $html = do_blocks('<!-- wp:woocommerce/mini-cart /-->');
        return '' !== trim((string) $html) ? $html : $this->fallback_cart_link_html();
    }

    /**
     * Only reached if the Mini Cart block somehow isn't available (an old
     * WooCommerce version without WooCommerce Blocks, or the block was
     * unregistered) - a site with WooCommerce active should never be left
     * with no cart link at all in the header.
     */
    private function fallback_cart_link_html() {
        $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : '';
        if ('' === $cart_url) {
            return '';
        }

        $count = (function_exists('WC') && WC()->cart) ? WC()->cart->get_cart_contents_count() : 0;

        return '<a class="omega-classic-header__wc-icon omega-classic-header__wc-icon--cart" href="' . esc_url($cart_url) . '" aria-label="' . esc_attr__('Cart', 'omega-design') . '">'
            . $this->cart_icon_svg()
            . '<span class="omega-cart-count' . (0 === $count ? ' is-hidden' : '') . '">' . esc_html((string) $count) . '</span>'
            . '</a>';
    }

    /** Plain inline SVG - no icon font/image dependency, matching the rest of the Classic Header. */
    private function account_icon_svg() {
        return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="4"></circle><path d="M4 20c0-4 3.5-7 8-7s8 3 8 7"></path></svg>';
    }

    private function cart_icon_svg() {
        return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="9" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.5 3h2l2.4 12.2a2 2 0 0 0 2 1.8h8.2a2 2 0 0 0 2-1.6L21 8H6"></path></svg>';
    }
}

woocommerce_header::get_instance();
