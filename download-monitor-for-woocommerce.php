<?php
/**
	Plugin Name: Download Monitor integration for WooCommerce
	Plugin URI: https://download-monitor.com/kb/woocommerce-and-download-monitor-integration/
	Description: With the help of this extension you will be able to restrict the downloads from your website behind a WooCommerce purchase.
	Version: 1.0.0
	Author: WPChill
	Author URI: https://wpchill.com
	Requires PHP: 7.3
	Text Domain: download-monitor-for-woocommerce
	Domain Path: /languages
	Requires Plugins: woocommerce, download-monitor
	License: GPL v3
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Plugin init.
 *
 * @return void
 */
function dlm_woocommerce_integration() {
	// Define.
	define( 'DLM_WC_FILE', __FILE__ );
	define( 'DLM_WC_PATH', plugin_dir_path( __FILE__ ) );
	define( 'DLM_WC_URL', plugin_dir_url( __FILE__ ) );
	define( 'DLM_WC_VERSION', '1.0.0' );

	if ( ! class_exists( 'WP_DLM' ) ) { // Check if DLM is active.
		add_action( 'admin_notices', 'dlm_woocommerce_dlm_needs', 15 );

		return;
	}

	// Check if WooCommerce is active.
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'dlm_woocommerce_woocommerce_needs', 15 );

		return;
	}
	// include files.
	require_once DLM_WC_PATH . 'classes/class-dlm-wc-integration.php';
	require_once DLM_WC_PATH . 'classes/class-dlm-wc-constants.php';
	require_once DLM_WC_PATH . 'classes/class-dlm-wc-access.php';
	require_once DLM_WC_PATH . 'classes/class-dlm-wc-modal.php';
	// Initiate classes.
	DLM_WC_Integration::get_instance();
	DLM_WC_Access::get_instance();
	DLM_WC_Modal::get_instance();
}

// init extension.
add_action( 'plugins_loaded', 'dlm_woocommerce_integration', 120 );

/**
 * Download Monitor needed notice.
 *
 * @return void
 * @since 1.0.0
 */
function dlm_woocommerce_dlm_needs() {
	?>
	<div class="notice notice-error is-dismissible">
		<p>
		<?php
			esc_html_e( 'Download Monitor - WooCommerce integration requires Download Monitor plugin to be installed and activated.', 'download-monitor-for-woocommerce' );
		?>
		</p>
	</div>
	<?php
}

/**
 * WooCommerce needed notice.
 *
 * @return void
 * @since 1.0.0
 */
function dlm_woocommerce_woocommerce_needs() {
	?>
	<div class="notice notice-error is-dismissible">
		<p>
		<?php
			esc_html_e( 'Download Monitor - WooCommerce integration requires WooCommerce plugin to be installed and activated.', 'download-monitor-for-woocommerce' );
		?>
		</p>
	</div>
	<?php
}
