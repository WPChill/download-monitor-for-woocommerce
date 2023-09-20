<?php

/**
 * Class DLM_WC_Access
 * This class handles the access to downloads.
 *
 * @since 1.0.0
 */
class DLM_WC_Access {

	public static $instance;

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return object The DLM_WC_Integration object.
	 * @since 1.0.0
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof DLM_WC_Access ) ) {
			self::$instance = new DLM_WC_Access();
		}

		return self::$instance;

	}

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		// Filter the download to redirect regular download buttons of mail locked downloads
		add_filter( 'dlm_can_download', array( $this, 'check_access' ), 30, 5 );
	}

	/**
	 * Check access to download.
	 *
	 * @param bool   $has_access     Whether the user has access to the download.
	 * @param object $download       The download object.
	 * @param object $version        The version object.
	 * @param array  $post_data      The post data.
	 * @param bool   $XMLHttpRequest Whether the request is an XMLHttpRequest.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function check_access( $has_access, $download, $version, $post_data = null, $XMLHttpRequest = false ) {

		// let's check if the download is locked
		if ( ! get_post_meta( $download->get_id(), DLM_WC_Constants::META_WC_LOCKED_KEY, true ) ) {
			return $has_access;
		}

		// let's check if the user is logged in
		if ( ! is_user_logged_in() ) {
			return $has_access;
		}

		// let's check if the user has a completed order with the download
		$has_order = false;
		$user_id   = get_current_user_id();

		// Get all completed orders for the user
		$orders = wc_get_orders(
			array(
				'customer' => $user_id,
				'status'   => array( 'completed' ),
			)
		);

		// Get current user subscriptions
		$subscriptions = wcs_get_users_subscriptions( $user_id );

		// If there are no orders or subscriptions, deny access
		if ( empty( $orders ) && empty( $subscriptions ) ) {
			return false;
		}

		// Cycle through the orders and check if the download is in the order
		foreach ( $orders as $order ) {
			$order_items = $order->get_items();
			foreach ( $order_items as $order_item ) {
				// Check if the order item is a product and if the product is the download
				if ( absint( get_post_meta( absint( $order_item['product_id'] ), '_download_monitor_id', true ) ) === absint( $download->get_id() ) ) {
					$has_order = true;
					break;
				}
			}
		}

		// Cycle through the subscriptions and check if the download is in the subscription
		foreach ( $subscriptions as $sub ) {
			$sub_items = $sub->get_items();
			// look for items in the subscriptions that match the download
			foreach ( $sub_items as $sub_item ) {
				if ( absint( get_post_meta( absint( $sub_item->get_product_id() ), '_download_monitor_id', true ) ) === absint( $download->get_id() ) ) {
					$has_order = true;
					break;
				}
			}
		}

		// If the user has a completed order with the download, let's allow access
		if ( ! $has_order ) {
			return false;
		}

		// If the user doesn't have a completed order with the download, return given access
		return $has_access;
	}
}
