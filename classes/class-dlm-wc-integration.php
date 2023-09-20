<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class DLM_WC_Integration
 * This class integrates Download Monitor with WooCommerce.
 *
 * @since 1.0.0
 */
class DLM_WC_Integration {

	public static $instance;

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return object The DLM_WC_Integration object.
	 * @since 1.0.0
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof DLM_WC_Integration ) ) {
			self::$instance = new DLM_WC_Integration();
		}

		return self::$instance;

	}

	/**
	 * Hook into the required WooCommerce areas.
	 * woocommerce_product_options_general integrates the link download.
	 * woocommerce_process_product_meta saves the download meta field.
	 * woocommece_account_downloads_endpoint hooks into the existing download tab to output our content.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_download_monitor_field' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_download_monitor_field' ) );
		add_action( 'woocommerce_account_downloads_endpoint', array( $this, 'show_download_monitor_content' ) );
	}

	/**
	 * Hook in and add the download monitor field to link the download.
	 * Works with all WooCommerce product types.
	 * Make the field a Select2 to enhance the user experience to search downloads.
	 *
	 * @since 1.0.0
	 */
	public function add_download_monitor_field() {
		global $post;
		echo '<div class="options_group">';
		woocommerce_wp_select(
			array(
				'id'      => '_download_monitor_id',
				'label'   => __( 'Link Download', 'download-monitor-woocommerce-integration' ),
				'options' => array( '' => __( 'Select Download', 'download-monitor-woocommerce-integration' ) ) + $this->get_download_monitor_options(),
				'class'   => 'wc-enhanced-select',
				'style'   => 'width: 400px;'
			)
		);
		echo '</div>';
		// Inline JavaScript to initialize Select2
		echo '<script type="text/javascript">
            jQuery(document).ready(function($) {
                $(".wc-enhanced-select").select2();
            });
        </script>';
	}


	/**
	 * Save the download monitor field and update the associated meta with the correct ID.
	 *
	 * @since 1.0.0
	 */
	public function save_download_monitor_field( $post_id ) {
		$download_monitor_id = $_POST['_download_monitor_id'];
		if ( ! empty( $download_monitor_id ) ) {
			update_post_meta( $post_id, '_download_monitor_id', absint( $download_monitor_id ) );
			update_post_meta( absint( $download_monitor_id ), '_dlm_wc_locked', $post_id );
		}
	}

	/**
	 * Hook in and add the download monitor tab
	 */
	public function add_download_monitor_tab( $items ) {
		$items['download_monitor'] = 'Download Monitor';

		return $items;
	}

	/**
	 * Add the download monitor endpoint to the my account area.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function add_download_monitor_endpoint() {
		add_rewrite_endpoint( 'download_monitor', EP_ROOT | EP_PAGES );
	}

	/**
	 * Show the download monitor content in the my account area.
	 * Make sure they have purchased the download.
	 * Block the download access if they haven't purchased the content.
	 *
	 * @since 1.0.0
	 */
	public function show_download_monitor_content() {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			echo esc_html__( 'You must be logged in to view downloads.', 'download-monitor-woocommerce-integration' );

			return;
		}

		// Fetch all orders for the current user
		$orders       = wc_get_orders( array( 'customer' => $user_id ) );
		$download_ids = array();

		foreach ( $orders as $order ) {
			// Check if the order is completed
			if ( 'completed' !== $order->get_status() ) {
				continue;
			}

			$items = $order->get_items();
			foreach ( $items as $item ) {
				$product_id  = $item->get_product_id();
				$download_id = get_post_meta( $product_id, '_download_monitor_id', true );
				if ( $download_id ) {
					$download_ids[] = $download_id;
				}
			}
		}

		if ( empty( $download_ids ) ) {
			echo '<h2>' . esc_html__( 'Download Monitor Downloads', 'download-monitor-woocommerce-integration' ) . '</h2>';
			echo esc_html__( 'No downloads available.', 'download-monitor-woocommerce-integration' );

			return;
		}

		echo '<h2>' . esc_html__( 'Download Monitor Downloads', 'download-monitor-woocommerce-integration' ) . '</h2>';
		echo '<ul>';
		foreach ( $download_ids as $download_id ) {
			$download = get_post( $download_id );
			if ( $download ) {
				echo '<li><a href="' . esc_url( get_permalink( $download_id ) ) . '">' . esc_html( $download->post_title ) . '</a></li>';
			}
		}
		echo '</ul>';
	}

	/**
	 * Fetch all the download monitor downloads and return them as an array.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_download_monitor_options() {
		// Fetch Download Monitor downloads.
		$downloads = get_posts(
			array(
				'post_type'   => 'dlm_download',
				'numberposts' => - 1
			)
		);

		$options = array();
		foreach ( $downloads as $download ) {
			$options[ $download->ID ] = $download->post_title;
		}

		return $options;
	}
}
