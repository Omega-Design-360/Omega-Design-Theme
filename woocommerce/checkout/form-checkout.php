<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	$omega_myaccount_url  = wc_get_page_permalink( 'myaccount' );
	$omega_button_class   = wc_wp_theme_get_element_class_name( 'button' );
	$omega_show_register  = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
	$omega_notice_message = apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( "You'll need to log in to complete your order. Sign in if you already have an account, or create a new one — it only takes a minute.", 'omega-design' ) );
	?>
	<div class="omega-checkout-auth-notice">
		<div class="omega-checkout-auth-notice__icon" aria-hidden="true">
			<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="10" rx="2"></rect><path d="M8 10V7a4 4 0 0 1 8 0v3"></path></svg>
		</div>
		<p class="omega-checkout-auth-notice__title"><?php esc_html_e( 'Please log in to continue', 'omega-design' ); ?></p>
		<p class="omega-checkout-auth-notice__message"><?php echo esc_html( $omega_notice_message ); ?></p>
		<div class="omega-checkout-auth-notice__actions">
			<a href="<?php echo esc_url( $omega_myaccount_url ); ?>" class="omega-checkout-auth-notice__button omega-checkout-auth-notice__button--primary <?php echo esc_attr( $omega_button_class ); ?>">
				<?php esc_html_e( 'Log in now', 'omega-design' ); ?>
			</a>
			<?php if ( $omega_show_register ) : ?>
				<a href="<?php echo esc_url( $omega_myaccount_url ); ?>" class="omega-checkout-auth-notice__button omega-checkout-auth-notice__button--secondary">
					<?php esc_html_e( 'Create an account', 'omega-design' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="col2-set" id="customer_details">
			<div class="col-1">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
			</div>

			<div class="col-2">
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			</div>
		</div>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	<?php endif; ?>
	
	<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
	
	<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>
	
	<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

	<div id="order_review" class="woocommerce-checkout-review-order">
		<?php do_action( 'woocommerce_checkout_order_review' ); ?>
	</div>

	<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
