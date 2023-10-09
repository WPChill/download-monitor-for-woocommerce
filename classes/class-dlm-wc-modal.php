<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Class DLM_WC_Modal
 * used to handle the modal functionality for the Email Lock extension.
 *
 * @since 1.0.0
 */
class DLM_WC_Modal {

	/**
	 * The singleton instance of the class.
	 *
	 * @var DLM_WC_Modal
	 * @since 1.0.0
	 */
	public static $instance;

	private function __construct() {

		add_action( 'wp_footer', array( $this, 'add_footer_scripts' ) );
		add_action( 'wp_ajax_nopriv_dlm_woo_lock_modal', array( $this, 'xhr_no_access_modal' ), 15 );
		add_action( 'wp_ajax_dlm_woo_lock_modal', array( $this, 'xhr_no_access_modal' ), 15 );

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return DLM_WC_Modal
	 * @since 1.0.0
	 */
	public static function get_instance(): DLM_WC_Modal {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof DLM_WC_Modal ) ) {
			self::$instance = new DLM_WC_Modal();
		}

		return self::$instance;
	}

	/**
	 * Add required scripts to footer.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function add_footer_scripts() {
		// Only add the script if the modal template exists.
		// Failsafe, in case the Modal template is non-existent, for example prior to DLM 4.9.0
		if ( ! class_exists( 'DLM_Constants' ) || ! defined( 'DLM_Constants::DLM_MODAL_TEMPLATE' ) ) {
			return;
		}
		?>
		<script>
			jQuery(document).on('dlm-xhr-modal-data', function (e, data, headers) {
				if ('undefined' !== typeof headers['x-dlm-woo-locked']) {
					data['action']             = 'dlm_woo_lock_modal';
					data['dlm_modal_response'] = 'true';
				}
			});
		</script>
		<?php
	}


	/**
	 * Renders the modal contents.
	 *
	 * @return void
	 * @since 4.3.13
	 */
	public function xhr_no_access_modal() {

		if ( isset( $_POST['download_id'] ) ) {
			wp_enqueue_style( 'dlm-wci-frontend', DLM_WC_URL . 'assets/css/front/frontend.css', array(), DLM_WC_VERSION );
			// Scripts and styles already enqueued in the shortcode action.
			$title   = __( 'Buy one of the following to get access to the desired file.', 'download-monitor-woocommerce-integration' );
			$content = $this->modal_content( absint( $_POST['download_id'] ) );

			if ( ! $content ) {
				$content = __( 'No products found.', 'download-monitor-woocommerce-integration' );
			}
			DLM_Modal::display_modal_template(
				array(
					'title'   => $title,
					'content' => '<div id="dlm_woo_lock_form">' . $content . '</div>'
				)
			);
		}

		wp_die();
	}

	/**
	 * The modal content for the Woocommerce Integration extension.
	 *
	 * @param int $download_id The download ID.
	 *
	 * @return false|string
	 * @since 1.0.0
	 */
	private function modal_content( $download_id ) {

		$products = get_post_meta( $download_id, DLM_WC_Constants::META_WC_LOCKED_KEY, true );

		if ( ! empty( $products ) ) {
			$template_handler = new DLM_Template_Handler();
			ob_start();
			$template_handler->get_template_part(
				'no-access-modal-products',
				'',
				DLM_WC_PATH . 'templates/',
				array(
					'products' => $products
				)
			);

			return ob_get_clean();
		}

		return false;
	}
}

