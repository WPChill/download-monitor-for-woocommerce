<?php
class DLM_AMM_WC_Subscriptions {
	
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

        add_filter( 'dlm_aam_group', array( $this, 'add_groups' ), 15, 1 );
        add_filter( 'dlm_aam_group_value_wc_subscriptions', array( $this, 'wc_subscriptions_group_value' ), 15 );
        add_filter( 'dlm_aam_restriction', array( $this, 'restrictions' ), 15, 1 );
        add_filter( 'dlm_aam_rest_variables', array( $this, 'rest_variables' ), 15, 1 );
        add_filter( 'dlm_aam_rule_wc_subscriptions_applies', array( $this, 'wc_subscriptions_rule' ), 15, 2 );
        add_filter( 'dlm_aam_meets_restriction', array( $this, 'wc_subscriptions_restrictions' ), 15, 2 );
		
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return object The DLM_AMM_WC_Subscriptions object.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof DLM_AMM_WC_Subscriptions ) ) {
			self::$instance = new DLM_AMM_WC_Subscriptions();
		}

		return self::$instance;

	}

	/**
	 * Add Woo Subscriptions to the list of rules
	 *
	 * @param [type] $groups
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function add_groups( $groups ) {
	
		$groups[] = array(
			'key'        => 'wc_subscriptions',
			'name'       => esc_html__( 'WooCommerce Subscriptions', 'dlm-woocommerce-integration' ),
			'conditions' => array(
				'includes' => array(
					'restriction' => array( 'null', 'amount', 'global_amount', 'daily_amount', 'monthly_amount', 'daily_global_amount', 'monthly_global_amount', 'date', 'wc_subscription_length' ),
				),
			),
			'field_type' => 'select',
		);

		return $groups;
	}

	/**
	 * Add Woo Subscriptions groups to group values
	 *
	 * @param object $return
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function wc_subscriptions_group_value( $return ) {

		// Woo Subscriptions groups.
		$groups[] = array(
			'key'  => 'null',
			'name' => esc_html__( 'None', 'dlm-woocommerce-integration' ),
		);

        // Get all Subscription products
        $subscription_products = wc_get_products( array(
            'type'  => array('variable-subscription', 'subscription'),
            'limit'     => -1,
            'orderby' => 'date',
            'order'     => 'DESC'
        ) );

        if ( empty( $subscription_products ) ){
            return '-';
        }

        foreach( $subscription_products as $product ) {

            $groups[] = array(
                'key'  => $product->get_id(),
                'name' => $product->get_name(),
            );

        }

		return wp_send_json( $groups );
	}

	/**
	 * Add Woo Subscriptions to restrictions
	 *
	 * @param array $restrictions
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function restrictions( $restrictions ) {
		$restrictions[] = array(
			'key'  => 'wc_subscription_length',
			'name' => esc_html__( 'Subscription Length', 'dlm-advanced-access-manager' ),
			'type' => esc_html( 'input' ),
			'conditions' => array(
				'includes' => array(
					'group' => array(  'null', 'role', 'user', 'ip' )
				)
			),
		);
		foreach ( $restrictions as $key => $restriction ) {
			if ( isset( $restriction['conditions']['includes']['group'] ) ) {
				$restrictions[ $key ]['conditions']['includes']['group'][] = 'wc_subscriptions';
			}
		}

		 return $restrictions;
	}

	/**
	 * Add Woo Subscriptions to rest variables
	 *
	 * @param [type] $rest_variables
	 * @return array
	 *
	 * @since 1.0.0
	 */
	public function rest_variables( $rest_variables ) {

        $vars['str_wc_subscriptions_'] = esc_html__( 'WooCommerce Subscriptions', 'dlm-woocommerce-integration' );

		// Woo Subscriptions groups.
		$groups = array();

        // Get all Subscription products
        $subscription_products = wc_get_products( array(
            'type'  => array('variable-subscription', 'subscription'),
            'limit'     => -1,
            'orderby' => 'date',
            'order'     => 'DESC'
        ) );

        if ( empty( $subscription_products ) ){
            return $rest_variables;
        }

        foreach( $subscription_products as $product ) {

            $groups[] = array(
                'key'  => $product->get_id(),
                'name' => $product->get_name(),
            );

        }

		$rest_variables['wc_subscriptions_groups'] = json_encode( $groups );

		return $rest_variables;
	}

	/**
	 * Checks if the rule applies for the customer.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function wc_subscriptions_rule( $applies, $rule ) {
		
		$current_user = wp_get_current_user();
		if ( ( $current_user instanceof WP_User ) && 0 != $current_user->ID ) {
			if ( wcs_user_has_subscription( $current_user->ID, (int)$rule->get_group_value(), 'active' ) ) {
				
				$applies = true;
			}
		}

		return $applies;
	}

	/**
	 * Counts the number of downloads in the subscription period.
	 *
	 * @return absint
	 *
	 * @since 1.0.0
	 */
	public function wc_subscriptions_amount_downloaded( $rule ){
		global $wpdb;

		$exclude_query = "SELECT `post_id` FROM {$wpdb->postmeta} WHERE `meta_key`='_except_from_restriction' AND `meta_value`=1";
		$exclude_ids = $wpdb->get_col( $exclude_query );

		if( empty( $exclude_ids ) ){
			$exclude_ids[] = 0;
		}

		$exclude_ids_in = '(' . implode( ',', $exclude_ids ) . ')';

		$current_user = wp_get_current_user();
        $subscriptions = wcs_get_users_subscriptions( $current_user->ID );

        $start_date = $exp_date = false;
        foreach( $subscriptions as $sub ){

            // look for the subscription we found in wc_subscriptions_rule(); 
            if( $sub->has_product( absint( $rule->get_group_value() ) ) ){
                $start_date = $sub->get_date('last_order_date_paid');
                $exp_date = $sub->get_date('next_payment');
                continue;
            }
        }

        $amount_downloaded = absint( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM {$wpdb->download_log} WHERE `user_id` = %s AND `download_status` IN ( 'completed', 'redirected' ) AND `download_date` >= '%s' AND `download_date` <= '%s' AND download_id NOT IN {$exclude_ids_in}", $current_user->ID, $start_date, $exp_date ) ) );

		return $amount_downloaded;
	}

	/**
	 * Checks if the user exceeded the number of dwonloads limit. 
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public function wc_subscriptions_restrictions( $meets_restrictions, $rule){

		if( 'wc_subscription_length' == $rule->get_restriction() ){

			// get amount of times this IP address downloaded file
			$amount_downloaded = $this->wc_subscriptions_amount_downloaded( $rule );

			// check if times download is equal to or smaller than allowed amount
			if ( $amount_downloaded >= absint( $rule->get_restriction_value() ) ) {
				// nope
				$meets_restrictions = false;
			}
		}
		return $meets_restrictions;
	}

}