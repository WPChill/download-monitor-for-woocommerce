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
function _dlm_woocommerce_integration() {

	// Define.
	define( 'DLM_WC_FILE', __FILE__ );
	define( 'DLM_WC_PATH', plugin_dir_path( __FILE__ ) );
	define( 'DLM_WC_URL', plugin_dir_url( __FILE__ ) );
	define( 'DLM_WC_VERSION', '1.0.0' );

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
add_action( 'plugins_loaded', '_dlm_woocommerce_integration', 120 );
