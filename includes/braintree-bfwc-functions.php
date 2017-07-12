<?php

/**
 * Functions specific to Braintree Subscription functionality when WC subscriptions is not active.
 */

function bfwcs_billing_interval_string( $interval = null )
{
	$intervals = bfwcs_billing_intervals();
	$string = array ();
	foreach ( $intervals as $index => $text ) {
		$remainder = $index % 10;
		if ( ! in_array( $index, array (
				11, 
				12, 
				13 
		) ) ) {
			switch( $remainder ) {
				case 1 :
					$suffix = __( 'st', 'braintree-payments' );
					break;
				case 2 :
					$suffix = __( 'nd', 'braintree-payments' );
					break;
				case 3 :
					$suffix = __( 'rd', 'braintree-payments' );
					break;
				default :
					$suffix = __( 'th', 'braintree-payments' );
					break;
			}
		} else {
			$suffix = __( 'th', 'braintree-payments' );
		}
		$string [ $index ] = sprintf( $intervals [ $index ], $index, $suffix );
	}
	return apply_filters( 'bfwcs_billing_interval_string', $interval ? $string [ $interval ] : $string, $interval );
}

function bfwcs_billing_intervals()
{
	$count = 24;
	$intervals = array ();
	for ( $i = 1; $i <= $count; $i ++) {
		$intervals [ $i ] = _n( 'every month', 'every %s%s month', $i, 'braintree-payments' );
	}
	// Allow plugins to add more intervals if necessary.
	return apply_filters( 'braintree_subscription_internvals', $intervals );
}

function bfwcs_get_price_string( $total_string, $interval, $period )
{
	$string = __( '%s every %s', 'braintree-payments' );
	$price_string = sprintf( $string, $total_string, $interval > 1 ? sprintf( '%s %s', $interval, bfwc_billing_periods_string( 'plural', $period ) ) : bfwc_billing_periods_string( 'singular', $period ) );
	return apply_filters( 'bfwcs_get_price_string', $price_string, $total_string, $period, $interval );
}

function bfwc_subscription_length_string()
{
	$lengths = array ();
	
	$lengths [ 0 ] = __( 'never expires', 'braintree-payments' );
	foreach ( range( 1, 48 ) as $i ) {
		$lengths [ $i ] = sprintf( _n( '%s month', '%s months', $i, 'braintree-payments' ), $i );
	}
	return apply_filters( 'braintree_subscription_lengths', $lengths );
}

/**
 * Return an array of options that can be used to represent formatted billing periods.
 */
function bfwc_billing_periods_string( $type = 'singular', $period = null )
{
	$i = $type === 'plural' ? 2 : 1;
	
	$periods = array (
			'day' => _n( 'day', 'days', $i, 'braintree-payments' ), 
			'month' => _n( 'month', 'months', $i, 'braintree-payments' ) 
	);
	
	return apply_filters( 'bfwc_billing_periods_string', $period == null ? $periods : $periods [ $period ], $type, $period );
}

/**
 * Given an order object or order_id, return true if there are Braintree subscriptions associated with the order.
 *
 * @param
 *        	mixed int|WC_Order $order
 */
function bfwcs_order_contains_subscription( $order )
{
	return count( bfwcs_get_subscriptions_for_order( $order ) ) > 0;
}

/**
 *
 * @param
 *        	mixed int|WC_Order $order
 * @return Braintree_Gateway_WC_Subscription[]
 */
function bfwcs_get_subscriptions_for_order( $order )
{
	if ( is_object( $order ) && is_a( $order, 'Braintree_Gateway_WC_Subscription' ) ) {
		return ( array ) $order;
	}
	if ( is_object( $order ) ) {
		$order_id = bwc_get_order_property( 'id', $order );
	} else {
		$order_id = $order;
	}
	
	$args = array (
			'post_type' => 'bfwc_subscription', 
			'posts_per_page' => - 1, 
			'post_parent' => $order_id, 
			'post_status' => 'any' 
	);
	
	$posts = get_posts( $args );
	if ( ! $posts ) {
		return array ();
	}
	$subscriptions = array ();
	
	foreach ( $posts as $post ) {
		$subscriptions [] = bfwcs_get_subscription( $post->ID );
	}
	return apply_filters( 'bfwcs_get_subscriptions_for_order', $subscriptions, $order );
}

/**
 * Return a subscription object from the given id.
 *
 * @param int $id        	
 * @return Braintree_Gateway_WC_Subscription
 */
function bfwcs_get_subscription( $id )
{
	$subscription = WC()->order_factory->get_order( $id );
	return apply_filters( 'bfwcs_get_subscription', $subscription );
}

/**
 * Return true if the product is a Braintree Subscription.
 *
 * @param
 *        	mixed int|WC_Product $product
 */
function bfwcs_product_is_subscription( $product )
{
	// ensure the product is an object.
	if ( ! is_object( $product ) ) {
		$product = wc_get_product( $product );
	}
	return $product->is_type( array (
			'braintree-subscription', 
			'braintree-variable-subscription', 
			'braintree-subscription-variation' 
	) );
}

/**
 * Return the string for the subscription price.
 *
 * @param WC_Cart $cart        	
 * @return <div> <strong>$20 every month for 14 months</strong>
 *         </div>
 */
function bfwcs_cart_subtotal_string( $cart )
{
	$interval = $cart->subscription_period_interval;
	$period = $cart->subscription_period;
	$text = sprintf( '<span class="subscription-Details">%s</span>', bfwcs_frontend_interval_string( $interval, $period ) );
	if ( $length = $cart->subscription_length ) {
		$text .= bfwcs_get_length_string( $length, $period );
	}
	return apply_filters( 'bfwcs_cart_subtotal_string', sprintf( '%s %s', $cart->get_cart_subtotal(), $text ), $cart );
}

/**
 * Returns the interval string for the given interval and period.
 *
 * @param int $interval        	
 * @param string $period        	
 * @return string every 3rd month for 12 months. / month for 9 months.
 */
function bfwcs_get_interval_string( $interval, $period )
{
	return apply_filters( 'bfwcs_get_interval_string', sprintf( __( '%s', 'braintree-payments' ), $interval > 1 ? bfwcs_billing_interval_string( $interval ) : sprintf( __( '/ %s', 'braintree-payments' ), bfwcs_get_period_string( $period ) ) ), $interval, $period );
}

function bfwcs_frontend_interval_string( $interval, $period )
{
	return sprintf( __( 'every %s', 'braintree-payments' ), $interval > 1 ? sprintf( '%s %s', $interval, bfwc_billing_periods_string( 'plural', $period ) ) : bfwc_billing_periods_string( 'singular', $period ) );
}

/**
 *
 * @param string $period        	
 * @return string the text representation of the period provided.
 */
function bfwcs_get_period_string( $period = 'month' )
{
	$periods = array (
			'day' => __( 'day', 'braintree-payments' ), 
			'month' => __( 'month', 'braintree-payments' ) 
	);
	return $periods [ $period ];
}

/**
 *
 * @param int $length        	
 * @param string $period
 *        	day, month, etc
 * @return string
 */
function bfwcs_get_length_string( $length, $period )
{
	$type = $length > 1 ? 'plural' : 'singular';
	return apply_filters( 'bfwcs_get_length_string', $length > 0 ? sprintf( __( ' for %s %s', 'braintree-payments' ), $length, bfwc_billing_periods_string( $type, $period ) ) : '', $length, $period );
}

/**
 *
 * @param WC_Product $product        	
 * @param string $price        	
 * @param int $quantity
 *        	quantity of the products that should be included in the product price.
 */
function bfwcs_get_product_price_html( $product, $price = '', $quantity = 1 )
{
	$length = $product->get_subscription_length();
	$interval = $product->get_subscription_period_interval();
	$period = $product->get_subscription_period();
	$tax_enabled = wc_tax_enabled();
	$show_tax = get_option( 'woocommerce_tax_display_shop' ) === 'incl';
	
	if ( empty( $price ) ) {
		
		if ( $product->is_on_sale() ) {
			$price = $product->get_display_price( $product->get_sale_price() );
		} else {
			if ( bwc_is_wc_3_0_0_or_more() ) {
				$price = $tax_enabled && $show_tax ? wc_get_price_including_tax( $product ) : wc_get_price_excluding_tax( $product );
			} else {
				$price = $tax_enabled && $show_tax ? $product->get_price_including_tax() : $product->get_price_excluding_tax();
			}
		}
	
	}
	$price = $price * $quantity;
	
	$text = '';
	
	$text = sprintf( __( 'every %s', 'braintree-payments' ), $interval > 1 ? sprintf( '%s %s', $interval, bfwc_billing_periods_string( 'plural', $period ) ) : bfwc_billing_periods_string( 'singular', $period ) );
	
	// $20 / month for 24 months.
	if ( $length > 0 ) {
		$text .= bfwcs_get_length_string( $length, $period );
	}
	
	// has trial period, add text.
	if ( $trial_length = $product->subscription_trial_length ) {
		$text .= sprintf( __( ' with a %s-%s free trial', 'braintree-payments' ), $trial_length, $product->subscription_trial_period );
	}
	
	if ( $signup_fee = $product->subscription_sign_up_fee ) {
		$text .= sprintf( __( ' and a %s sign up fee', 'braintree-payments' ), wc_price( $signup_fee * $quantity ) );
	}
	
	$price = wc_price( $price ) . '<span class="subscription-Details"> ' . $text . '</span>';
	return apply_filters( 'bfwcs_get_product_price_html', $price, $product );
}

/**
 * Create the subscription within the Wordpress database.
 *
 * @param unknown $args        	
 */
function bfwcs_create_subscription( $args )
{
	$post_args = array (
			'post_type' => 'bfwc_subscription', 
			'post_author' => 0, 
			'post_status' => 'wc-pending', 
			'post_parent' => absint( $args [ 'order_id' ] ) 
	);
	
	$post_id = wp_insert_post( $post_args );
	
	if ( is_wp_error( $post_id ) ) {
		return new WP_Error( 'subscription-error', __( 'There was an error creating the subscription.', 'braintree-payments' ) );
	}
	
	update_post_meta( $post_id, '_customer_user', $args [ 'customer_user' ] );
	update_post_meta( $post_id, '_start_date', $args [ 'start_date' ] );
	update_post_meta( $post_id, '_next_payment_date', $args [ 'next_payment_date' ] );
	update_post_meta( $post_id, '_trial_end_date', $args [ 'trial_end_date' ] );
	update_post_meta( $post_id, '_end_date', $args [ 'end_date' ] );
	update_post_meta( $post_id, '_subscription_trial_length', $args [ 'subscription_trial_length' ] );
	update_post_meta( $post_id, '_subscription_trial_period', $args [ 'subscription_trial_period' ] );
	update_post_meta( $post_id, '_first_payment_date', $args [ 'first_payment_date' ] );
	update_post_meta( $post_id, '_braintree_plan', $args [ 'braintree_plan' ] );
	update_post_meta( $post_id, '_merchant_account_id', $args [ 'merchant_account_id' ] );
	update_post_meta( $post_id, '_subscription_period', $args [ 'subscription_period' ] );
	update_post_meta( $post_id, '_subscription_period_interval', $args [ 'subscription_period_interval' ] );
	update_post_meta( $post_id, '_subscription_time_zone', $args [ 'subscription_time_zone' ] );
	update_post_meta( $post_id, '_subscription_length', $args [ 'subscription_length' ] );
	update_post_meta( $post_id, '_order_currency', $args [ 'order_currency' ] );
	
	return WC()->order_factory->get_order( $post_id );
}

function bfwcs_create_renewal_order( $subscription )
{
	if ( ! is_object( $subscription ) ) {
		$subscription = bfwcs_get_subscription( $subscription );
	}
	
	$order = bfwcs_create_order_from_subscription( $subscription );
	
	if ( ! $order ) {
		return new WP_Error();
	}
	
	return $order;
}

/**
 *
 * @param
 *        	mixed int|Braintree_Gateway_WC_Subscription $subscription
 */
function bfwcs_create_order_from_subscription( $subscription )
{
	global $wpdb;
	
	$wpdb->query( 'START TRANSACTION' );
	
	try {
		if ( ! is_object( $subscription ) ) {
			$subscription = bfwcs_get_subscription( $subscription );
		}
		
		$items = $subscription->get_items( array (
				'line_item', 
				'tax', 
				'fee', 
				'shipping' 
		) );
		
		$renewal_order = wc_create_order( array (
				'customer_id' => $subscription->get_user_id() 
		) );
		
		// add all of the items from the subscription to the new order.
		foreach ( $items as $item_id => $item ) {
			
			$recurring_item_id = wc_add_order_item( $renewal_order->id, array (
					'order_item_name' => $item [ 'name' ], 
					'order_item_type' => $item [ 'type' ] 
			) );
			
			foreach ( $item [ 'item_meta' ] as $meta_key => $meta_values ) {
				foreach ( $meta_values as $meta_value ) {
					wc_add_order_item_meta( $recurring_item_id, $meta_key, maybe_unserialize( $meta_value ) );
				}
			}
		
		}
		
		// copy all the metadata from the subscription to the order.
		bfwcs_copy_order_meta( $subscription, $renewal_order );
		
		update_post_meta( bwc_get_order_property( 'id', $renewal_order ), '_renewal_order', true );
		update_post_meta( bwc_get_order_property( 'id', $renewal_order ), '_subscription_id', bwc_get_order_property( 'id', $subscription ) );
		
		$wpdb->query( 'COMMIT' );
		
		return $renewal_order;
	
	} catch( Exception $e ) {
		$wpdb->query( 'ROLLBACK' );
		return false;
	}

}

/**
 * Copy meta data from one order to another.
 *
 * @param WC_Order $from        	
 * @param WC_Order $to        	
 */
function bfwcs_copy_order_meta( $from, $to )
{
	global $wpdb;
	$query = $wpdb->prepare( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d 
			AND meta_key NOT LIKE '%%_date' AND meta_key NOT LIKE '_subscription%%' AND meta_key NOT IN ('_created_in_braintree', '_transaction_id', '_order_key')", $from->id );
	$results = $wpdb->get_results( $query );
	
	foreach ( $results as $result ) {
		update_post_meta( $to->id, $result->meta_key, maybe_unserialize( $result->meta_value ) );
	}
}

/**
 * Return true if products that are the same can be combined into one subscription.
 *
 * @return boolean
 */
function bfwcs_can_combine_subscriptions()
{
	return bt_manager()->is_active( 'braintree_subscription_combine_same_products' );
}

/**
 * Determine if the WC_Cart contains a Braintree subscription product.
 *
 * @return boolean
 */
function bfwcs_cart_contains_subscriptions()
{
	if ( ! empty( WC()->cart->cart_contents ) ) {
		foreach ( WC()->cart->get_cart() as $key => $item ) {
			$product = $item [ 'data' ];
			if ( bfwcs_product_is_subscription( $product ) ) {
				return true;
			}
		}
	}
	return false;
}

/**
 *
 * @param WC_Product $product        	
 * @return mixed string|WP_Error
 */
function bfwcs_get_plan_from_product( $product )
{
	if ( ! is_object( $product ) ) {
		$product = wc_get_product( $product );
	}
	$key = sprintf( 'braintree_%s_plans', bt_manager()->get_environment() );
	$plans = $product->{$key};
	$currency = get_woocommerce_currency();
	return isset( $plans [ $currency ] ) ? $plans [ $currency ] : '';
}

/**
 * Calculate the start date for the Braintree subscription.
 * The start date is defined as the date
 * in which the subscription is active. If there is a trial period then the subscription can start
 * immediately. The gateway timezone is used to calculate the datetime object but the returned date is in UTC.
 *
 * @return DateTime $date DateTime object representing the start date in UTC
 */
function bfwcs_calculate_start_date()
{
	$date_in_timezone = new DateTime( null, new DateTimeZone( bfwc_get_gateway_timezone() ) );
	$date_in_utc = clone $date_in_timezone;
	$date_in_utc->setTimezone( new DateTimeZone( 'UTC' ) );
	
	return $date_in_utc;
}

/**
 * Calculate the first payment date of the subscription.
 * The date is returned in UTC.
 *
 * @param WC_Product $product        	
 * @return DateTime
 */
function bfwcs_calculate_first_payment_date( $trial_period = 'day', $trial_length = null )
{
	$date_in_tz = new DateTime( null, new DateTimeZone( bfwc_get_gateway_timezone() ) );
	// has trial period so add time.
	if ( $trial_length ) {
		switch( $trial_period ) {
			case 'day' :
				$date_in_tz->add( new DateInterval( 'P' . $trial_length . 'D' ) );
				break;
			case 'month' :
				$date_in_tz->add( new DateInterval( 'P' . $trial_length . 'M' ) );
				break;
		}
	}
	$date_in_utc = $date_in_tz->setTimezone( new DateTimeZone( 'UTC' ) );
	return $date_in_utc;
}

/**
 *
 * @param number $subscription_length        	
 * @param string $subscription_period        	
 * @param DateTime $first_payment        	
 * @return DateTime
 */
function bfwcs_calculate_end_date( $subscription_length = 0, $subscription_period = 'month', $trial_period = 'day', $trial_length = 0, $timezone = 'UTC' )
{
	if ( ! $subscription_length ) {
		return 0;
	}
	$end_date = clone bfwcs_calculate_first_payment_date( $trial_period, $trial_length );
	
	/**
	 * set the end date in the provided timezone.
	 * This is to prevent calculation errors like 3/01/2017 UTC which
	 * would set the num of days to 31. If timezone is America/Chicago, then the month is actuall Feb.
	 */
	$end_date->setTimeZone( new DateTimeZone( $timezone ) );
	$day_of_month = $end_date->format( 'j' );
	$days_in_first_month = $end_date->format( 't' );
	
	// set date to first day of month
	$end_date->setDate( $end_date->format( 'Y' ), $end_date->format( 'm' ), 1 );
	
	switch( $subscription_period ) {
		case 'month' :
			$end_date->add( new DateInterval( 'P' . $subscription_length . 'M' ) );
			break;
	}
	
	if ( $trial_length ) {
		if ( $day_of_month > $end_date->format( 't' ) ) {
			$end_date->setDate( $end_date->format( 'Y' ), $end_date->format( 'm' ), $end_date->format( 't' ) );
		} else {
			$end_date->setDate( $end_date->format( 'Y' ), $end_date->format( 'm' ), $day_of_month );
		}
	} else {
		/**
		 * subscription started on last day of month or start day is greater than number of days in end month,
		 * so make sure end date is on last day of month
		 */
		if ( ( $day_of_month === $days_in_first_month && $day_of_month > 28 ) || $day_of_month > $end_date->format( 't' ) ) {
			$end_date->setDate( $end_date->format( 'Y' ), $end_date->format( 'm' ), $end_date->format( 't' ) );
		} else {
			$end_date->setDate( $end_date->format( 'Y' ), $end_date->format( 'm' ), $day_of_month );
		}
	}
	// set timezone back to UTC after calculations are performed.
	$end_date->setTimeZone( new DateTimeZone( 'UTC' ) );
	return $end_date;
}

/**
 * Generate the html for the cart shipping displayed on the cart page.
 *
 * @param WC_Cart $cart        	
 */
function bfwcs_cart_shipping_total( $cart )
{
	$text = $cart->get_cart_shipping_total() . ' ' . bfwcs_frontend_interval_string( $cart->subscription_period_interval, $cart->subscription_period );
	return sprintf( '%s %s', $text, bfwcs_get_length_string( $cart->subscription_length, $cart->subscription_period ) );
}

/**
 * Return html for the recurring total portion displayed on the cart page.
 *
 * @param WC_Cart $cart        	
 */
function bfwcs_cart_recurring_total_html( $cart )
{
	$total = $cart->get_total();
	$recurring_total = sprintf( '%s %s', $total, bfwcs_frontend_interval_string( $cart->subscription_period_interval, $cart->subscription_period ) );
	if ( $length = $cart->subscription_length ) {
		$recurring_total = sprintf( '%s %s', $recurring_total, bfwcs_get_length_string( $cart->subscription_length, $cart->subscription_period ) );
	}
	return $recurring_total;
}

/**
 * Return html for the tax total portion displayed on the cart page.
 *
 * @param
 *        	$tax
 * @param WC_Cart $cart        	
 */
function bfwcs_cart_tax_total_html( $tax, $cart )
{
	$recurring_total = sprintf( '%s %s', $tax->formatted_amount, bfwcs_frontend_interval_string( $cart->subscription_period_interval, $cart->subscription_period ) );
	if ( $cart->subscription_length ) {
		$recurring_total = sprintf( '%s %s', $recurring_total, bfwcs_get_length_string( $cart->subscription_length, $cart->subscription_period ) );
	}
	return $recurring_total;
}

/**
 * Return a formatted date.
 * The timezone given is the timezone in which the formatted string will be converted to. All given times
 * should be in UTC to ensure proper conversion.
 *
 * @param
 *        	mixed DateTime|string $date
 * @param string $timezone        	
 * @param string $format        	
 */
function bfwcs_cart_formatted_date( $date, $timezone = 'UTC', $format = 'F j, Y' )
{
	$date = $date instanceof DateTime ? $date : new DateTime( $date, new DateTimeZone( 'UTC' ) );
	$date->setTimezone( new DateTimeZone( $timezone ) );
	return $date->format( $format );
}

/**
 * Return an array of subscription statuses.
 *
 * @return mixed
 */
function bfwc_get_subscription_statuses()
{
	global $bfwc_subscription_statuses;
	return $bfwc_subscription_statuses;
}

function bfwc_register_subscription_status( $status, $values )
{
	global $bfwc_subscription_statuses;
	$bfwc_subscription_statuses [ $status ] = $values [ 'label' ];
	register_post_status( $status, $values );
}

/**
 * Return the nice name for the status provided.
 * If the status is invalid, then the status provided will
 * be returned.
 *
 * @param unknown $status        	
 */
function bfwc_get_subscription_status_name( $status )
{
	$status = strpos( $status, 'wc-' ) === false ? 'wc-' . $status : $status;
	$statuses = wp_parse_args( wc_get_order_statuses(), bfwc_get_subscription_statuses() );
	return isset( $statuses [ $status ] ) ? $statuses [ $status ] : $status;
}

/**
 *
 * @param WC_Order $order        	
 * @param Braintree_Gateway_WC_Subscription $subscription        	
 * @param string $address_type        	
 */
function bfwc_copy_address_from_order( $order, $subscription, $address_type = 'all' )
{
	$address_types = $address_type === 'all' ? array (
			'billing', 
			'shipping' 
	) : array (
			$address_type 
	);
	
	$address_fields = array (
			'first_name', 
			'last_name', 
			'company', 
			'address_1', 
			'address_2', 
			'city', 
			'state', 
			'postcode', 
			'country', 
			'email', 
			'phone' 
	);
	
	foreach ( $address_types as $type ) {
		
		foreach ( $address_fields as $field_key ) {
			$field_var = sprintf( '%s_%s', $type, $field_key );
			$address [ $field_key ] = bwc_get_order_property( $field_var, $order );
		}
		
		$subscription->set_address( $address, $type );
	}
	
	return $subscription;
}

/**
 *
 * @param WC_Product $product        	
 */
function bfwc_get_product_descriptors( $product )
{
	if ( ! is_object( $product ) ) {
		$product = wc_get_product( $product );
	}
	if ( ! $descriptors = $product->subscription_descriptors ) {
		$descriptors = array ();
	}
	return $descriptors;
}

/**
 * Return the configured gateway timezone.
 *
 * @return string
 */
function bfwc_get_gateway_timezone()
{
	return bt_manager()->get_option( 'subscription_gateway_timezone' );
}

function bfwcs_get_subscription_statuses()
{
	return apply_filters( 'bfwcs_subscription_statuses', array (
			'wc-active' => array (
					'label' => __( 'Active', 'braintree-payments' ), 
					'public' => true, 
					'exclude_from_search' => false, 
					'show_in_admin_all_list' => true, 
					'show_in_admin_status_list' => true, 
					'label_count' => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'braintree-payments' ) 
			), 
			'wc-expired' => array (
					'label' => __( 'Expired', 'braintree-payments' ), 
					'public' => true, 
					'exclude_from_search' => false, 
					'show_in_admin_all_list' => true, 
					'show_in_admin_status_list' => true, 
					'label_count' => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'braintree-payments' ) 
			), 
			'wc-past-due' => array (
					'label' => __( 'Past Due', 'braintree-payments' ), 
					'public' => true, 
					'exclude_from_search' => false, 
					'show_in_admin_all_list' => true, 
					'show_in_admin_status_list' => true, 
					'label_count' => _n_noop( 'Past Due <span class="count">(%s)</span>', 'Past Due <span class="count">(%s)</span>', 'braintree-payments' ) 
			) 
	) );
}

function bfwcs_get_related_orders( $subscription )
{
	global $wpdb;
	if ( ! is_object( $subscription ) ) {
		$subscription = bfwcs_get_subscription( $subscription );
	}
	$results = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts AS posts LEFT JOIN $wpdb->postmeta AS postmeta 
			ON postmeta.post_id = posts.ID WHERE posts.post_type = 'shop_order' AND postmeta.meta_key = '_subscription_id' 
			AND postmeta.meta_value = %s", bwc_get_order_property( 'id', $subscription ) ) );
	$orders = array ();
	foreach ( $results as $result ) {
		$orders [] = wc_get_order( $result->ID );
	}
	return $orders;

}

/**
 * Return true if the order is actually a subscription.
 *
 * @param
 *        	mixed WC_Order|int $order
 */
function bfwcs_order_is_subscription( $order )
{
	if ( ! is_object( $order ) ) {
		$order = wc_get_order( $order );
	}
	return bwc_get_order_property( 'post', $order )->post_type === 'bfwc_subscription';
}

/**
 * Retrieve subscriptions for the given user.
 *
 * @param number $user_id        	
 * @return Braintree_Gateway_WC_Subscription[]
 */
function bfwcs_get_subscriptions_for_user( $user_id = 0 )
{
	$posts = get_posts( array (
			'post_type' => 'bfwc_subscription', 
			'post_status' => array_merge( array_keys( bfwcs_get_subscription_statuses() ), array_keys( wc_get_order_statuses() ) ), 
			'posts_per_page' => - 1, 
			'meta_query' => array (
					array (
							'key' => '_customer_user', 
							'value' => $user_id 
					) 
			) 
	) );
	$subscriptions = array ();
	foreach ( $posts as $post ) {
		$subscriptions [] = bfwcs_get_subscription( $post->ID );
	}
	return $subscriptions;

}

/**
 *
 * @param Braintree_Gateway_WC_Subscription $subscription        	
 */
function bfwcs_get_subscription_actions( $subscription )
{
	$actions = array (
			'view' => array (
					'url' => wc_get_endpoint_url( 'view-subscription', $subscription->id, wc_get_page_permalink( 'myaccount' ) ), 
					'label' => __( 'View', 'braintree-payments' ) 
			) 
	);
	
	if ( ! $subscription->has_status( 'cancelled' ) ) {
	
	}
	return apply_filters( 'bfwc_get_subscription_actions', $actions );
}

/**
 * return an array of user actions that pertain to the subscription.
 *
 * @param Braintree_Gateway_WC_Subscription $subscription        	
 */
function bfwc_subscription_user_actions( $subscription )
{
	$actions = array ();
	if ( $subscription->has_status( 'active' ) || $subscription->has_status( 'past-due' ) ) {
		$actions [ 'cancel' ] = array (
				'label' => __( 'Cancel', 'braintree-payments' ), 
				'url' => add_query_arg( 'cancel-subscription', $subscription->id, wp_nonce_url( wc_get_endpoint_url( 'view-subscription', $subscription->id, wc_get_page_permalink( 'myaccount' ) ), 'cancel-subscription' ) ) 
		);
		$actions [ 'change_payment_method' ] = array (
				'label' => __( 'Change Payment Method', 'braintree-payments' ), 
				'url' => wc_get_endpoint_url( 'change-payment-method', $subscription->id, wc_get_page_permalink( 'myaccount' ) ) 
		);
	} elseif ( $subscription->has_status( 'pending' ) ) {
		$actions [ 'pay_for_subscription' ] = array (
				'label' => __( 'Pay', 'braintree-payments' ), 
				'url' => $subscription->get_checkout_payment_url() 
		);
	}
	return apply_filters( 'bfwc_subscription_user_actions', $actions, $subscription );
}

/**
 * Return true if the current request is for the subscription change payment method.
 */
function bfwcs_is_change_payment_method()
{
	global $wp;
	return isset( $wp->query_vars [ 'change-payment-method' ] );
}

/**
 * Check to see if a payment method can be deleted.
 *
 * @param WP_Error $error        	
 * @param string $token        	
 */
function bfwc_can_delete_payment_method( $error, $token )
{
	if ( ! bt_manager()->is_woocommerce_subscriptions_active() ) {
		global $wpdb;
		
		$statuses = bfwcs_get_subscription_statuses();
		unset( $statuses [ 'wc-cancelled' ] );
		$statuses = array_keys( $statuses );
		
		$in_array = vsprintf( implode( ',', array_fill( 0, count( $statuses ), "'%s'" ) ), $statuses );
		
		// query that finds subscriptions that use the provided payment method token.
		$query = $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts AS posts INNER JOIN $wpdb->postmeta AS postmeta 
				ON posts.ID = postmeta.post_id WHERE posts.post_type = 'bfwc_subscription' AND posts.post_status IN ($in_array) 
				AND postmeta.meta_key = '_payment_method_token' AND postmeta.meta_value = %s", $token );
		$count = $wpdb->get_var( $query );
		
		if ( $count ) {
			$message = sprintf( _n( 'There is a subscription associated with this payment method.', 'There are %s subscriptions associated with this payment method.', $count, 'braintree-payments' ), $count );
			$error->add( 'method-delete', $message );
		}
	}
	return $error;
}

/**
 * Return true if the current request is for the pay subscription page.
 *
 * @return boolean
 */
function bfwcs_is_pay_for_subscription_request()
{
	global $wp;
	return ! empty( $wp->query_vars [ 'pay-subscription' ] );
}

function bfwc_get_template( $template, $args = array() )
{
	extract( $args );
	
	$located = bwc_locate_template( $template );
	
	if ( ! file_exists( $located ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( 'File %s does not exist.', $template ), bt_manager()->version );
		return;
	}
	
	// Allow other plugins to replace the file if desired.
	$located = apply_filters( "braintree_woocommerce_template_{$template}", $located, $args );
	
	include $located;
}

function bfwc_add_body_class( $classes )
{
	$classes [] = 'bfwc-body';
	return $classes;
}
add_filter( 'body_class', 'bfwc_add_body_class' );

/**
 *
 * @param WC_Order $order        	
 * @param Braintree_Gateway_WC_Subscription $subscription        	
 */
function bfwcs_calculate_order_total( $order, $subscription )
{
	if ( ! $subscription->has_trial() ) {
		$order->set_total( $order->get_total() - $subscription->get_total() );
		if ( $order->get_cart_tax() ) {
			update_post_meta( $order->id, '_order_tax', $order->get_cart_tax() - $subscription->get_cart_tax() );
		}
		if ( $order->get_shipping_tax() ) {
			update_post_meta( $order->id, '_order_shipping_tax', $order->get_shipping_tax() - $subscription->get_shipping_tax() );
		}
	}
}

/**
 *
 * @param string $currency        	
 * @return mixed
 */
function bfwcs_get_currency_symbol( $currency )
{
	$symbols = apply_filters( 'woocommerce_currency_symbols', array (
			'AED' => '&#x62f;.&#x625;', 
			'AFN' => '&#x60b;', 
			'ALL' => 'L', 
			'AMD' => 'AMD', 
			'ANG' => '&fnof;', 
			'AOA' => 'Kz', 
			'ARS' => '&#36;', 
			'AUD' => '&#36;', 
			'AWG' => '&fnof;', 
			'AZN' => 'AZN', 
			'BAM' => 'KM', 
			'BBD' => '&#36;', 
			'BDT' => '&#2547;&nbsp;', 
			'BGN' => '&#1083;&#1074;.', 
			'BHD' => '.&#x62f;.&#x628;', 
			'BIF' => 'Fr', 
			'BMD' => '&#36;', 
			'BND' => '&#36;', 
			'BOB' => 'Bs.', 
			'BRL' => '&#82;&#36;', 
			'BSD' => '&#36;', 
			'BTC' => '&#3647;', 
			'BTN' => 'Nu.', 
			'BWP' => 'P', 
			'BYR' => 'Br', 
			'BZD' => '&#36;', 
			'CAD' => '&#36;', 
			'CDF' => 'Fr', 
			'CHF' => '&#67;&#72;&#70;', 
			'CLP' => '&#36;', 
			'CNY' => '&yen;', 
			'COP' => '&#36;', 
			'CRC' => '&#x20a1;', 
			'CUC' => '&#36;', 
			'CUP' => '&#36;', 
			'CVE' => '&#36;', 
			'CZK' => '&#75;&#269;', 
			'DJF' => 'Fr', 
			'DKK' => 'DKK', 
			'DOP' => 'RD&#36;', 
			'DZD' => '&#x62f;.&#x62c;', 
			'EGP' => 'EGP', 
			'ERN' => 'Nfk', 
			'ETB' => 'Br', 
			'EUR' => '&euro;', 
			'FJD' => '&#36;', 
			'FKP' => '&pound;', 
			'GBP' => '&pound;', 
			'GEL' => '&#x10da;', 
			'GGP' => '&pound;', 
			'GHS' => '&#x20b5;', 
			'GIP' => '&pound;', 
			'GMD' => 'D', 
			'GNF' => 'Fr', 
			'GTQ' => 'Q', 
			'GYD' => '&#36;', 
			'HKD' => '&#36;', 
			'HNL' => 'L', 
			'HRK' => 'Kn', 
			'HTG' => 'G', 
			'HUF' => '&#70;&#116;', 
			'IDR' => 'Rp', 
			'ILS' => '&#8362;', 
			'IMP' => '&pound;', 
			'INR' => '&#8377;', 
			'IQD' => '&#x639;.&#x62f;', 
			'IRR' => '&#xfdfc;', 
			'ISK' => 'kr.', 
			'JEP' => '&pound;', 
			'JMD' => '&#36;', 
			'JOD' => '&#x62f;.&#x627;', 
			'JPY' => '&yen;', 
			'KES' => 'KSh', 
			'KGS' => '&#x441;&#x43e;&#x43c;', 
			'KHR' => '&#x17db;', 
			'KMF' => 'Fr', 
			'KPW' => '&#x20a9;', 
			'KRW' => '&#8361;', 
			'KWD' => '&#x62f;.&#x643;', 
			'KYD' => '&#36;', 
			'KZT' => 'KZT', 
			'LAK' => '&#8365;', 
			'LBP' => '&#x644;.&#x644;', 
			'LKR' => '&#xdbb;&#xdd4;', 
			'LRD' => '&#36;', 
			'LSL' => 'L', 
			'LYD' => '&#x644;.&#x62f;', 
			'MAD' => '&#x62f;. &#x645;.', 
			'MAD' => '&#x62f;.&#x645;.', 
			'MDL' => 'L', 
			'MGA' => 'Ar', 
			'MKD' => '&#x434;&#x435;&#x43d;', 
			'MMK' => 'Ks', 
			'MNT' => '&#x20ae;', 
			'MOP' => 'P', 
			'MRO' => 'UM', 
			'MUR' => '&#x20a8;', 
			'MVR' => '.&#x783;', 
			'MWK' => 'MK', 
			'MXN' => '&#36;', 
			'MYR' => '&#82;&#77;', 
			'MZN' => 'MT', 
			'NAD' => '&#36;', 
			'NGN' => '&#8358;', 
			'NIO' => 'C&#36;', 
			'NOK' => '&#107;&#114;', 
			'NPR' => '&#8360;', 
			'NZD' => '&#36;', 
			'OMR' => '&#x631;.&#x639;.', 
			'PAB' => 'B/.', 
			'PEN' => 'S/.', 
			'PGK' => 'K', 
			'PHP' => '&#8369;', 
			'PKR' => '&#8360;', 
			'PLN' => '&#122;&#322;', 
			'PRB' => '&#x440;.', 
			'PYG' => '&#8370;', 
			'QAR' => '&#x631;.&#x642;', 
			'RMB' => '&yen;', 
			'RON' => 'lei', 
			'RSD' => '&#x434;&#x438;&#x43d;.', 
			'RUB' => '&#8381;', 
			'RWF' => 'Fr', 
			'SAR' => '&#x631;.&#x633;', 
			'SBD' => '&#36;', 
			'SCR' => '&#x20a8;', 
			'SDG' => '&#x62c;.&#x633;.', 
			'SEK' => '&#107;&#114;', 
			'SGD' => '&#36;', 
			'SHP' => '&pound;', 
			'SLL' => 'Le', 
			'SOS' => 'Sh', 
			'SRD' => '&#36;', 
			'SSP' => '&pound;', 
			'STD' => 'Db', 
			'SYP' => '&#x644;.&#x633;', 
			'SZL' => 'L', 
			'THB' => '&#3647;', 
			'TJS' => '&#x405;&#x41c;', 
			'TMT' => 'm', 
			'TND' => '&#x62f;.&#x62a;', 
			'TOP' => 'T&#36;', 
			'TRY' => '&#8378;', 
			'TTD' => '&#36;', 
			'TWD' => '&#78;&#84;&#36;', 
			'TZS' => 'Sh', 
			'UAH' => '&#8372;', 
			'UGX' => 'UGX', 
			'USD' => '&#36;', 
			'UYU' => '&#36;', 
			'UZS' => 'UZS', 
			'VEF' => 'Bs F', 
			'VND' => '&#8363;', 
			'VUV' => 'Vt', 
			'WST' => 'T', 
			'XAF' => 'Fr', 
			'XCD' => '&#36;', 
			'XOF' => 'Fr', 
			'XPF' => 'Fr', 
			'YER' => '&#xfdfc;', 
			'ZAR' => '&#82;', 
			'ZMW' => 'ZK' 
	) );
	
	return apply_filters( 'bfwcs_get_currency_symbol', isset( $symbols [ $currency ] ) ? $symbols [ $currency ] : '', $symbols, $currency );
}

/**
 * Return an array of UTC timezones
 */
function bfwc_get_timezones()
{
	$timezones = array ();
	$timezone_list = timezone_identifiers_list();
	foreach ( $timezone_list as $zone ) {
		try {
			$date_time_zone = new DateTimeZone( $zone );
			$timezones [ $zone ] = sprintf( '%s - UTC Offset: %s hrs', $zone, $date_time_zone->getOffset( new DateTime() ) / 3600 );
		} catch( Exception $e ) {
		
		}
	}
	return $timezones;
}

function bfwcs_subscription_link_active()
{
	return bt_manager()->is_active( 'my_account_subscriptions' );
}