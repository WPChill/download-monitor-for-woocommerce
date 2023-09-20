<?php

class DLM_AMM_WOOCOMMERCE {

	const VERSION = '1.0.0';

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Load plugin text domain
		load_plugin_textdomain( 'download-monitor-woocommerce-integration', false, dirname( plugin_basename( DLM_AAM_WC_FILE ) ) . '/languages/' );

		if ( 'ok' !== $this->core_exists() && $this->is_dlm_admin_page() ) {

			add_action( 'admin_notices', array( $this, 'display_notice_core_missing' ), 8 );

		} else {
			add_filter( 'dlm_aam_group', array( $this, 'add_groups' ), 15, 1 );
			add_filter( 'dlm_aam_group_value_wc_product_cat', array(
				$this,
				'wc_products_categories_group_value'
			),          15 );
			add_filter( 'dlm_aam_group_value_wc_product', array(
				$this,
				'wc_products_group_value'
			),          15 );
			add_filter( 'dlm_aam_rest_variables', array( $this, 'rest_variables' ), 25, 1 );
			add_filter( 'dlm_aam_rule_wc_product_cat_applies', array( $this, 'wc_product_cat_rule' ), 15, 2 );
			add_filter( 'dlm_aam_rule_wc_product_applies', array( $this, 'wc_product_rule' ), 15, 2 );
		}
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return object The DLM_AMM_WOOCOMMERCE object.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof DLM_AMM_WOOCOMMERCE ) ) {
			self::$instance = new DLM_AMM_WOOCOMMERCE();
		}

		return self::$instance;

	}

	/**
	 * Add WooCommerce caegories to the list of rules
	 *
	 * @param [type] $groups
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function add_groups( $groups ) {

		// Add WooCommerce categories to the list of rules.
		$groups[] = array(
			'key'        => 'wc_product_cat',
			'name'       => esc_html__( 'WooCommerce Category', 'download-monitor-woocommerce-integration' ),
			'conditions' => array(
				'includes' => array(
					'restriction' => array(
						'null',
						'amount',
						'global_amount',
						'daily_amount',
						'monthly_amount',
						'daily_global_amount',
						'monthly_global_amount',
						'date'
					),
				),
			),
			'field_type' => 'select',
		);

		// Add WooCommerce Products to the list of rules.
		$groups[] = array(
			'key'        => 'wc_product',
			'name'       => esc_html__( 'WooCommerce Prduct', 'download-monitor-woocommerce-integration' ),
			'conditions' => array(
				'includes' => array(
					'restriction' => array(
						'null',
						'amount',
						'global_amount',
						'daily_amount',
						'monthly_amount',
						'daily_global_amount',
						'monthly_global_amount',
						'date'
					),
				),
			),
			'field_type' => 'select',
		);

		return $groups;
	}

	/**
	 * Returns all product categories.
	 *
	 * @return OBJECT
	 *
	 * @since 1.0.0
	 */
	private function wc_get_product_categories() {
		$args = array(
			'taxonomy'     => 'product_cat',
			'hierarchical' => 1,
			'hide_empty'   => 0
		);

		return get_categories( $args );
	}

	/**
	 * Checks if user bought product from category.
	 *
	 * @param object $customer
	 *
	 * @param absint $cat_id
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	private function wc_customer_bought_product_from_cat( $customer, $cat_id ) {

		if ( ! is_user_logged_in() ) {

			return false;
		}

		$args = array(
			'posts_per_page' => - 1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_cat',
					'terms'    => $cat_id,
					'operator' => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
				),
			),
			'return'         => 'ids',
		);

		$query        = new WC_Product_Query( $args );
		$products_ids = $query->get_products();

		if ( empty( $products_ids ) ) {

			return false;
		}

		foreach ( $products_ids as $id ) {
			//var_dump(  wc_customer_bought_product( '', $customer_id , $id ));

			if ( wc_customer_bought_product( $customer->user_email, $customer->ID, $id ) ) {
				return true;
			}
		}

		//wp_die();

		return false;

	}

	/**
	 * Add WooCommerce categories to group values
	 *
	 * @param object $return
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function wc_products_categories_group_value( $return ) {

		// WooCommerce groups.
		$groups[] = array(
			'key'  => 'null',
			'name' => esc_html__( 'None', 'download-monitor-woocommerce-integration' ),
		);

		global $wpdb;
		$wc_product_cats = $this->wc_get_product_categories();

		// check, loop & add to $roles.
		if ( ! empty( $wc_product_cats ) ) {
			foreach ( $wc_product_cats as $cat ) {
				$groups[] = array(
					'key'  => $cat->term_id,
					'name' => ( 0 == $cat->parent ) ? $cat->name : '— ' . $cat->name, // category or sub-category
				);
			}
		}

		return wp_send_json( $groups );
	}

	/**
	 * Add WooCommerce Products to group values
	 *
	 * @param object $return
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function wc_products_group_value( $return ) {

		// WooCommerce groups.
		$groups[] = array(
			'key'  => 'null',
			'name' => esc_html__( 'None', 'download-monitor-woocommerce-integration' ),
		);

		global $wpdb;
		$dlmwci      = DLM_WC_Integration::get_instance();
		$wc_products = $dlmwci->get_download_monitor_options();

		if ( ! empty( $wc_products ) ) {
			foreach ( $wc_products as $id => $title ) {
				$groups[] = array(
					'key'  => $id,
					'name' => $title
				);
			}
		}

		return wp_send_json( $groups );
	}

	/**
	 * Add WooCommerce categories to rest variables
	 *
	 * @param [type] $rest_variables
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function rest_variables( $rest_variables ) {

		// WooCommerce groups.
		$cat_groups     = array();
		$product_groups = array();

		// Get WooCommerce groups.
		$wc_product_cats = $this->wc_get_product_categories();
		// check, loop & add to $roles.
		if ( ! empty( $wc_product_cats ) ) {
			foreach ( $wc_product_cats as $cat ) {
				$cat_groups[] = array(
					'key'    => $cat->term_id,
					'name'   => $cat->name,
					'parent' => $cat->parent,
				);
			}
		}

		// Add WooCommerce categories to rest variables.
		$rest_variables['wc_product_cat_groups'] = json_encode( $cat_groups );

		$dlmwci      = DLM_WC_Integration::get_instance();
		$wc_products = $dlmwci->get_download_monitor_options();

		if ( ! empty( $wc_products ) ) {
			foreach ( $wc_products as $id => $title ) {
				$product_groups[] = array(
					'key'  => $id,
					'name' => $title
				);
			}
		}

		// Add WooCommerce Products to rest variables.
		$rest_variables['wc_product_groups'] = json_encode( $product_groups );

		return $rest_variables;
	}

	/**
	 * Check if rule applies to customer.
	 *
	 * @param bool   $applies
	 *
	 * @param object $rule
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function wc_product_cat_rule( $applies, $rule ) {

		$current_user = wp_get_current_user();

		if ( ( $current_user instanceof WP_User ) && 0 != $current_user->ID ) {
			if ( $this->wc_customer_bought_product_from_cat( $current_user, absint( $rule->get_group_value() ) ) ) {

				$applies = true;
			}
		}

		return $applies;
	}


	/**
	 * Check if rule applies to customer.
	 *
	 * @param bool   $applies
	 *
	 * @param object $rule
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function wc_product_rule( $applies, $rule ) {

		$current_user = wp_get_current_user();

		if ( ( $current_user instanceof WP_User ) && 0 != $current_user->ID ) {
			// Get all completed orders for the user
			$orders = wc_get_orders(
				array(
					'customer' => $current_user->ID,
					'status'   => array( 'completed' ),
				)
			);
			// If there are no orders or subscriptions, deny access
			if ( ! empty( $orders ) ) {
				$product_id = absint( $rule->get_group_value() );
				// Cycle through the orders and check if the download is in the order
				foreach ( $orders as $order ) {
					$order_items = $order->get_items();
					foreach ( $order_items as $order_item ) {
						// Check if the order item is a product and if the product is the download
						if ( absint( get_post_meta( absint( $order_item['product_id'] ), '_download_monitor_id', true ) ) === absint( get_post_meta( absint( $product_id ), '_download_monitor_id', true ) ) ) {
							$applies = true;
							break;
						}
					}
				}
			}
		}

		return $applies;
	}

	/**
	 * Check if Download Monitor & Download Monitor Advanced Access Manager & WooCommerce are installed and active.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function core_exists() {

		$missing = array();

		// check for Download Monitor
		if ( ! defined( 'DLM_VERSION' ) ) {
			$missing[] = 'missing_dlm';
		}

		// check for DLM Advanced Access Manager
		if ( ! class_exists( 'DLM_Advanced_Access_Manager' ) ) {
			$missing[] = 'missing_aam';
		}

		// check for WooCommerce class
		if ( ! class_exists( 'WooCommerce', false ) ) {
			$missing[] = 'missing_wc';
		}

		if ( 3 == count( $missing ) ) {
			return 'missing_all';
		}

		if ( 2 == count( $missing ) ) {
			if ( ! array_diff( array( 'missing_dlm', 'missing_aam' ), $missing ) ) {
				return 'missing_dlm_amm';
			}
			if ( ! array_diff( array( 'missing_dlm', 'missing_wc' ), $missing ) ) {
				return 'missing_dlm_wc';
			}
			if ( ! array_diff( array( 'missing_aam', 'missing_wc' ), $missing ) ) {
				return 'missing_amm_wc';
			}
		}

		if ( 1 == count( $missing ) ) {
			return $missing[0];

		}

		return 'ok';
	}

	/**
	 * Core notice
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function display_notice_core_missing() {

		$dlm_link = '<a href="https://wordpress.org/plugins/download-monitor/" target="_blank"><strong>' . __( 'Download Monitor', 'download-monitor-woocommerce-integration' ) . '</strong></a>';
		$wc_link  = '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank"><strong>' . __( 'WooCommerce', 'download-monitor-woocommerce-integration' ) . '</strong></a>';
		$aam_link = '<a href="https://www.download-monitor.com/extensions/advanced-access-manager/?utm_source=download-monitor&utm_medium=rcp-integration&utm_campaign=upsell" target="_blank"><strong>' . __( 'Download Monitor - Advanced Access Manager', 'download-monitor-woocommerce-integration' ) . '</strong></a>';

		$core_exists     = $this->core_exists();
		$notice_messages = array(
			'missing_dlm'     => sprintf( __( 'Download Monitor & WooCommerce integration requires %s in order to work.', 'download-monitor-woocommerce-integration' ), $dlm_link ),
			'missing_aam'     => sprintf( __( 'Download Monitor & WooCommerce integration requires %s addon in order to work.', 'download-monitor-woocommerce-integration' ), $aam_link ),
			'missing_wc'      => sprintf( __( 'Download Monitor & WooCommerce integration requires %s in order to work.', 'download-monitor-woocommerce-integration' ), $wc_link ),
			'missing_dlm_amm' => sprintf( __( 'Download Monitor & WooCommerce integration requires %s & %s addon in order to work.', 'download-monitor-woocommerce-integration' ), $dlm_link, $aam_link ),
			'missing_dlm_wc'  => sprintf( __( 'Download Monitor & WooCommerce integration requires %s & %s plugin in order to work.', 'download-monitor-woocommerce-integration' ), $dlm_link, $wc_link ),
			'missing_amm_wc'  => sprintf( __( 'Download Monitor & WooCommerce integration requires %s addon & %s plugin in order to work.', 'download-monitor-woocommerce-integration' ), $aam_link, $wc_link ),
			'missing_all'     => sprintf( __( 'Download Monitor & WooCommerce integration requires %s, %s addon & %s plugin in order to work.', 'download-monitor-woocommerce-integration' ), $dlm_link, $aam_link, $wc_link ),
		);

		$class = 'notice notice-error';
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $notice_messages[ $core_exists ] ) );

	}

	/**
	 * Check if we are on a dlm page
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function is_dlm_admin_page() {
		global $pagenow;

		if ( 'plugins.php' === $pagenow || ( isset( $_GET['post_type'] ) && 'dlm_download' === $_GET['post_type'] ) ) {
			return true;
		}

		return false;
	}

}