<?php

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Class DLM_WC_Integration
 * This class integrates Download Monitor with WooCommerce.
 *
 * @since 1.0.0
 */
class DLM_WC_Integration {

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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Hook in and add the download monitor field to link the download.
	 * Works with all WooCommerce product types.
	 * Make the field a Select2 to enhance the user experience to search downloads.
	 *
	 * @since 1.0.0
	 */
	public function add_download_monitor_field() {
		$downloads = $this->get_download_monitor_options();
		?>
		<div class="options-group dlm-woocommerce-locked-downloads">
			<p class="form-field">
				<label for="<?php
				echo esc_attr( DLM_WC_Constants::META_WC_PROD_KEY ); ?>">Downloads</label>
				<select class='wc-enhanced-select'
				        name='<?php
				        echo esc_attr( DLM_WC_Constants::META_WC_PROD_KEY ); ?>[]' multiple='multiple'>
					<?php
					// Cycle through each download and output the option.
					foreach ( $downloads as $id => $title ) {
						$selected = in_array( (string) $id, (array) get_post_meta( get_the_ID(), DLM_WC_Constants::META_WC_PROD_KEY, true ), true );
						echo '<option value="' . esc_attr( $id ) . '" ' . ( $selected ? ' selected="selected" ' : '' ) . '>' . esc_html( $title ) . '</option>';
					}
					?>
				</select>
			</p>
		</div>
		<?php
	}

	/**
	 * Save the download monitor field and update the associated meta with the correct ID.
	 *
	 * @param  int  $post_id  The ID of the product being saved.
	 *
	 * @since 1.0.0
	 */
	public function save_download_monitor_field( $post_id ) {
		// The retrieved data should be an array.
		$download_monitor_ids = ! empty( $_POST[ DLM_WC_Constants::META_WC_PROD_KEY ] ) ? array_map( 'absint', $_POST[ DLM_WC_Constants::META_WC_PROD_KEY ] ) : array();

		if ( ! empty( $download_monitor_ids ) ) {
			update_post_meta( $post_id, DLM_WC_Constants::META_WC_PROD_KEY, $download_monitor_ids );
			// Lock each download to the product.
			foreach ( $download_monitor_ids as $id ) {
				$currently_locked = get_post_meta( absint( $id ), DLM_WC_Constants::META_WC_LOCKED_KEY, true );
				if ( ! empty( $currently_locked ) && is_array( $currently_locked ) ) {
					if ( ! in_array( $post_id, $currently_locked, true ) ) {
						$currently_locked[] = $post_id;
					}
					update_post_meta( absint( $id ), DLM_WC_Constants::META_WC_LOCKED_KEY, $currently_locked );
				} else {
					update_post_meta( absint( $id ), DLM_WC_Constants::META_WC_LOCKED_KEY, array( $post_id ) );
				}
			}
		}
	}

	/**
	 * Hook in and add the download monitor tab
	 *
	 * @param  array  $items  The existing tabs.
	 *
	 * @since 1.0.0
	 */
	public function add_download_monitor_tab( $items ) {
		$items['download_monitor'] = 'Download Monitor';

		return $items;
	}

	/**
	 * Add the download monitor endpoint to the "My account" area.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function add_download_monitor_endpoint() {
		add_rewrite_endpoint( 'download_monitor', EP_ROOT | EP_PAGES );
	}

	/**
	 * Show the download monitor content in the "My account" area.
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

		// Fetch all orders for the current user.
		$orders       = wc_get_orders( array( 'customer' => $user_id ) );
		$download_ids = array();

		foreach ( $orders as $order ) {
			// Check if the order is completed.
			if ( 'completed' !== $order->get_status() ) {
				continue;
			}

			$items = $order->get_items();
			foreach ( $items as $item ) {
				$product_id = $item->get_product_id();
				$downloads  = get_post_meta( $product_id, DLM_WC_Constants::META_WC_PROD_KEY, true );
				if ( ! empty( $downloads ) ) {
					foreach ( $downloads as $download ) {
						if ( ! in_array( $download, $download_ids, true ) ) {
							$download_ids[] = $download;
						}
					}
				}
			}
		}

		echo '<h2>' . esc_html__( 'Download Monitor Downloads', 'download-monitor-woocommerce-integration' ) . '</h2>';

		if ( empty( $download_ids ) ) {
			echo esc_html__( 'No downloads available.', 'download-monitor-woocommerce-integration' );

			return;
		}

		echo '<ul>';
		foreach ( $download_ids as $download_id ) {
			$download = download_monitor()->service( 'download_repository' )->retrieve_single( $download_id );
			if ( $download ) {
				echo '<li><a href="' . esc_url( $download->get_the_download_link() ) . '">' . esc_html( $download->get_title() ) . '</a></li>';
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
		$downloads = download_monitor()->service( 'download_repository' )->retrieve();

		$options = array();
		foreach ( $downloads as $download ) {
			$options[ $download->get_id() ] = $download->get_title();
		}

		return $options;
	}

	/**
	 * Add required scripts and styles
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function enqueue_admin_scripts() {
		// Enqueue scripts only in WooCommerce Product edit/new screen.
		if ( ! function_exists( 'get_current_screen' ) || ! get_current_screen() || 'product' !== get_current_screen()->id ) {
			return;
		}
		wp_enqueue_script( 'dlm-wc-integration', DLM_WC_URL . '/assets/js/admin.js', array( 'select2' ), DLM_WC_VERSION, true );
		wp_enqueue_style( 'dlm-wc-integration', DLM_WC_URL . '/assets/css/admin.css', array(), DLM_WC_VERSION );
	}
}
