<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * My Account navigation.
 *
 * @since 2.6.0
 */
?>
<div class="omega-account-shell">
	<header class="omega-account-heading">
		<div>
			<p class="omega-account-eyebrow"><?php esc_html_e( 'Your space', 'omega-design' ); ?></p>
			<h1><?php esc_html_e( 'My account', 'woocommerce' ); ?></h1>
			<p><?php esc_html_e( 'Manage your orders, addresses, and account details.', 'omega-design' ); ?></p>
		</div>
	</header>
	<div class="omega-account-layout">
		<?php do_action( 'woocommerce_account_navigation' ); ?>

		<div class="woocommerce-MyAccount-content">
	<?php
		/**
		 * My Account content.
		 *
		 * @since 2.6.0
		 */
		do_action( 'woocommerce_account_content' );
	?>
		</div>
	</div>
</div>
