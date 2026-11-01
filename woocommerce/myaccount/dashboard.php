<?php
/**
 * My Account Dashboard
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$account_id       = get_current_user_id();
$account_name     = isset( $current_user->display_name ) ? $current_user->display_name : '';
$account_orders   = wc_get_orders(
	array(
		'customer_id' => $account_id,
		'limit'       => 5,
		'orderby'     => 'date',
		'order'       => 'DESC',
	)
);
$account_order_count = function_exists( 'wc_get_customer_order_count' ) ? wc_get_customer_order_count( $account_id ) : count( $account_orders );
$account_spent       = function_exists( 'wc_get_customer_total_spent' ) ? wc_get_customer_total_spent( $account_id ) : 0;
$account_downloads   = function_exists( 'wc_get_customer_available_downloads' ) ? wc_get_customer_available_downloads( $account_id ) : array();
?>

<section class="omega-account-welcome" aria-labelledby="omega-account-welcome-title">
	<div class="omega-account-welcome__copy">
		<p class="omega-account-eyebrow"><?php esc_html_e( 'Your account', 'omega-design' ); ?></p>
		<h2 id="omega-account-welcome-title">
			<?php
			printf(
				/* translators: %s: customer display name. */
				esc_html__( 'Welcome back, %s', 'omega-design' ),
				esc_html( $account_name )
			);
			?>
		</h2>
		<p><?php esc_html_e( 'Your orders, details, and account preferences—all together in one place.', 'omega-design' ); ?></p>
	</div>
	<div class="omega-account-welcome__profile">
		<?php echo get_avatar( $account_id, 58, '', esc_attr( $account_name ), array( 'class' => 'omega-account-avatar' ) ); ?>
	</div>
</section>

<section class="omega-account-stats" aria-label="<?php esc_attr_e( 'Account summary', 'omega-design' ); ?>">
	<a class="omega-account-stat" href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>">
		<span class="omega-account-stat__icon" aria-hidden="true">▤</span>
		<span class="omega-account-stat__content"><strong><?php echo esc_html( number_format_i18n( $account_order_count ) ); ?></strong><span><?php esc_html_e( 'Total orders', 'omega-design' ); ?></span></span>
	</a>
	<div class="omega-account-stat">
		<span class="omega-account-stat__icon omega-account-stat__icon--accent" aria-hidden="true">◈</span>
		<span class="omega-account-stat__content"><strong><?php echo wp_kses_post( wc_price( $account_spent ) ); ?></strong><span><?php esc_html_e( 'Total spent', 'omega-design' ); ?></span></span>
	</div>
	<a class="omega-account-stat" href="<?php echo esc_url( wc_get_endpoint_url( 'downloads' ) ); ?>">
		<span class="omega-account-stat__icon omega-account-stat__icon--soft" aria-hidden="true">↓</span>
		<span class="omega-account-stat__content"><strong><?php echo esc_html( number_format_i18n( count( $account_downloads ) ) ); ?></strong><span><?php esc_html_e( 'Available downloads', 'omega-design' ); ?></span></span>
	</a>
</section>

<div class="omega-account-dashboard-grid">
	<section class="omega-account-panel omega-account-recent-orders" aria-labelledby="omega-account-orders-title">
		<header class="omega-account-panel__heading">
			<div><p class="omega-account-eyebrow"><?php esc_html_e( 'Your activity', 'omega-design' ); ?></p><h3 id="omega-account-orders-title"><?php esc_html_e( 'Recent orders', 'omega-design' ); ?></h3></div>
			<a href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( 'All orders', 'omega-design' ); ?> <span aria-hidden="true">→</span></a>
		</header>
		<?php if ( $account_orders ) : ?>
			<div class="omega-account-order-list">
				<?php foreach ( $account_orders as $account_order ) : ?>
					<div class="omega-account-order">
						<div class="omega-account-order__number"><a href="<?php echo esc_url( $account_order->get_view_order_url() ); ?>"><?php echo esc_html( '#' . $account_order->get_order_number() ); ?></a><span><?php echo esc_html( wc_format_datetime( $account_order->get_date_created() ) ); ?></span></div>
						<span class="omega-account-order__status omega-account-order__status--<?php echo esc_attr( $account_order->get_status() ); ?>"><?php echo esc_html( wc_get_order_status_name( $account_order->get_status() ) ); ?></span>
						<strong class="omega-account-order__total"><?php echo wp_kses_post( $account_order->get_formatted_order_total() ); ?></strong>
						<a class="omega-account-order__action" href="<?php echo esc_url( $account_order->get_view_order_url() ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'View order %s', 'omega-design' ), $account_order->get_order_number() ) ); ?>">→</a>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="omega-account-empty"><p><?php esc_html_e( 'You haven’t placed an order yet.', 'omega-design' ); ?></p><a class="wp-element-button" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Explore the shop', 'omega-design' ); ?> <span aria-hidden="true">→</span></a></div>
		<?php endif; ?>
	</section>

</div>

<?php
/** My Account dashboard extension hook. */
do_action( 'woocommerce_account_dashboard' );
/** @deprecated 2.6.0 */
do_action( 'woocommerce_before_my_account' );
do_action( 'woocommerce_after_my_account' );
