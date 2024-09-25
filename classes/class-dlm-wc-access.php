<?php
/**
 * DLMWC_Access class file.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Class DLMWC_Access
 * This class handles the access to downloads.
 *
 * @since 1.0.0
 */
class DLMWC_Access {

	/**
	 * The singleton instance of the class.
	 *
	 * @var object
	 * @since 1.0.0
	 */
	public static $instance;

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return object The DLMWC_Access object.
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof DLMWC_Access ) ) {
			self::$instance = new DLMWC_Access();
		}

		return self::$instance;
	}

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		// Filter the download to redirect regular download buttons of mail locked downloads.
		add_filter( 'dlm_can_download', array( $this, 'check_access' ), 30, 2 );
		// Add shortcode form to the no access page.
		add_action( 'dlm_no_access_after_message', array( $this, 'add_products_on_modal' ), 15, 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Check access to download.
	 *
	 * @param  bool   $has_access      Whether the user has access to the download.
	 * @param  object $download        The download object.
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function check_access( $has_access, $download ) {
		// let's check if the download is locked.
		if ( ! get_post_meta( $download->get_id(), DLMWC_Constants::META_WC_LOCKED_KEY, true ) ) {
			return $has_access;
		}

		// let's check if the user is logged in.
		if ( ! is_user_logged_in() ) {
			// If product is locked by Woocommerce and user not logged in automatically deny access
			// and list the products that are locking the download.
			$this->set_headers( $download );

			return false;
		}

		// let's check if the user has a completed order with the download.
		$has_order     = false;
		$user_id       = get_current_user_id();
		$orders        = array();
		$subscriptions = array();

		if ( function_exists( 'wc_get_orders' ) ) {
			// Get all completed orders for the user.
			$orders = wc_get_orders(
				array(
					'customer' => $user_id,
					'status'   => array( 'completed' ),
				)
			);
		}

		if ( function_exists( 'wcs_get_users_subscriptions' ) ) {
			// Get current user subscriptions.
			$subscriptions = wcs_get_users_subscriptions( $user_id );
		}

		// If there are no orders or subscriptions, deny access.
		if ( empty( $orders ) && empty( $subscriptions ) ) {
			return false;
		}

		// Cycle through the orders and check if the download is in the order.
		foreach ( $orders as $order ) {
			$order_items = $order->get_items();
			foreach ( $order_items as $order_item ) {
				// Check if the download is locked by the product.
				if ( in_array( $download->get_id(), get_post_meta( absint( $order_item['product_id'] ), DLMWC_Constants::META_WC_PROD_KEY, true ) ) ) {
					$has_order = true;
					break;
				}
			}
		}

		// Cycle through the subscriptions and check if the download is in the subscription.
		foreach ( $subscriptions as $sub ) {
			$sub_items = $sub->get_items();
			// look for items in the subscriptions that match the download.
			foreach ( $sub_items as $sub_item ) {
				if ( in_array( $download->get_id(), get_post_meta( absint( $sub_item->get_product_id() ), DLMWC_Constants::META_WC_PROD_KEY, true ) ) ) {
					$has_order = true;
					break;
				}
			}
		}

		// If the user has a completed order with the download, let's allow access.
		if ( ! $has_order ) {
			$this->set_headers( $download );

			return false;
		}

		// If the user doesn't have a completed order with the download, return given access.
		return $has_access;
	}

	/**
	 * Set headers in case No Access Modal is enabled.
	 *
	 * @param DLM_Download $download The download object.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function set_headers( $download ) {
		if ( get_option( 'dlm_no_access_modal', false ) && apply_filters( 'do_dlm_xhr_access_modal', true, $download ) && defined( 'DLM_DOING_XHR' ) && DLM_DOING_XHR ) {
			header_remove( 'X-dlm-no-waypoints' );

			$restriction_type = 'dlm-woocommerce-modal';

			header( 'X-DLM-Woo-redirect: true' );
			header( 'X-DLM-No-Access: true' );
			header( 'X-DLM-No-Access-Modal: true' );
			header( 'X-DLM-No-Access-Restriction: ' . $restriction_type );
			header( 'X-DLM-Nonce: ' . wp_create_nonce( 'dlm_ajax_nonce' ) );
			header( 'X-DLM-Woo-Locked: true' );
			header( 'X-DLM-Download-ID: ' . absint( $download->get_id() ) );
			exit;
		}
	}

	/**
	 * Add products on modal.
	 *
	 * @param DLM_Download $download The download object.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function add_products_on_modal( $download ) {
		$products = get_post_meta( $download->get_id(), DLMWC_Constants::META_WC_LOCKED_KEY, true );

		if ( ! empty( $products ) ) {
			$template_handler = new DLM_Template_Handler();
			ob_start();
			$template_handler->get_template_part(
				'no-access-modal-products',
				'',
				DLMWC_PATH . 'templates/',
				array(
					'products' => $products,
				)
			);
			echo wp_kses_post( ob_get_clean() );
		}
	}

	/**
	 * Enqueue needed scripts and styles
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		wp_register_style( 'dlm-wci-frontend', DLMWC_URL . 'assets/css/front/frontend.css', array(), DLMWC_VERSION );
	}
}
