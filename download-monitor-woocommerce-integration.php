<?php
/*
	Plugin Name: Download Monitor - WooCommerce integration
	Plugin URI: https://www.download-monitor.com/extensions/dlm-simple-wordpress-membership-integration/
	Description: Download Monitor & WooCommerce integration extension allows you to limit downloads by bought products categories & subscription.
	Version: 1.0.0
	Author: WPChill
	Author URI: https://wpchill.com
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

/**
 * Plugin init.
 *
 * @return void
 */
function _dlm_aam_woocommerce_integration() {

	// Define.
	define( 'DLM_AAM_WC_FILE', __FILE__ );
	define( 'DLM_AAM_WC_PATH', plugin_dir_path( __FILE__ ) );
	define( 'DLM_AAM_WC_URL', plugin_dir_url( __FILE__ ) );

	// include files.
	require_once DLM_AAM_WC_PATH . 'classes/class-dlm-aam-woocommerce.php';

	// Instantiate main plugin object.
	DLM_AMM_WOOCOMMERCE::get_instance();

	if ( class_exists( 'WC_Subscriptions' ) ) {
		require_once DLM_AAM_WC_PATH . 'classes/class-dlm-aam-wc-subscriptions.php';
		// Instantiate subscriptions plugin object.
		DLM_AMM_WC_Subscriptions::get_instance();
	}
	require_once DLM_AAM_WC_PATH . 'classes/class-dlm-wc-integration.php';
	require_once DLM_AAM_WC_PATH . 'classes/class-dlm-wc-constants.php';
	require_once DLM_AAM_WC_PATH . 'classes/class-dlm-wc-access.php';

	$dlm_wc_integration = DLM_WC_Integration::get_instance();
	DLM_WC_Access::get_instance();
}

// init extension.
add_action( 'plugins_loaded', '_dlm_aam_woocommerce_integration', 120 );
