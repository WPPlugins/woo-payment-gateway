<?php
/**
 * 
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *
 *@property string $braintree_plan Braintree plan Id.
 *@property string $merchant_account_id merchant account Id to be used on the subscription.
 */
class Braintree_Gateway_WC_Subscription extends WC_Order
{
	
	/**
	 *
	 * @var WC_Order
	 */
	public $order;

	public function __construct( $order )
	{
		parent::__construct( $order );
		
		$this->order = $this->bfwc_get_post()->post_parent ? wc_get_order( $this->bfwc_get_post()->post_parent ) : null;
		
		$this->post_status = get_post_status( $this->id );
	}

	public function get_order( $id = 0 )
	{
		return $this->order;
	}

	/**
	 *
	 * @since 2.6.2a
	 * {@inheritDoc}
	 *
	 * @see WC_Abstract_Order::__get()
	 */
	public function __get( $key )
	{
		if ( bwc_is_wc_3_0_0_or_more() ) {
			return bwc_get_order_property( $key, $this );
		} else {
			return parent::__get( $key );
		}
	}

	public function bfwc_get_post()
	{
		return get_post( bwc_get_order_property( 'id', $this ) );
	}

	/**
	 * Update the Subscription status.
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Abstract_Order::update_status()
	 */
	public function update_status( $new_status, $note = '', $manual = false )
	{
		global $wpdb;
		
		$new_status = ( strpos( $new_status, 'wc-' ) !== false ) ? substr( $new_status, 3 ) : $new_status;
		$old_status = $this->get_status();
		$new_status_key = 'wc-' . $new_status;
		$old_status_key = 'wc-' . $old_status;
		
		try {
			
			// only update if the status is changed
			if ( $old_status !== $new_status ) {
				switch( $new_status ) {
					case 'active' :
						if ( $this->has_status( array (
								'cancelled', 
								'expired' 
						) ) ) {
							throw new Exception( sprintf( __( 'Subscriptions with status %s cannot be changed to Active', 'braintree-payments' ), bfwc_get_subscription_status_name( $this->post_status ) ) );
						}
						break;
					case 'cancelled' :
						if ( $this->has_status( array (
								'expired' 
						) ) ) {
							throw new Exception( sprintf( __( 'Subscriptions that are expired cannot be changed to %s.', 'braintree-payments' ), bfwc_get_subscription_status_name( $this->post_status ) ) );
						}
						break;
				}
				
				// update the subscription status.
				$wpdb->update( $wpdb->posts, array (
						'post_status' => $new_status_key 
				), array (
						'ID' => $this->id 
				) );
				
				// allow functionality to hook into the status change trigger.
				do_action( 'bfwc_subscription_status_' . $new_status, $old_status_key, $this );
				
				// made it this far so status was updated.
				$this->add_order_note( sprintf( __( 'Subscription status changed from %1$s to %2$s.', 'braintree-payments' ), bfwc_get_subscription_status_name( $old_status ), bfwc_get_subscription_status_name( $new_status ) ) );
			
			}
		} catch( Exception $e ) {
			// update the post status to what it was before since there was an exception thrown.
			$wpdb->update( $wpdb->posts, array (
					'post_status' => $old_status_key 
			), array (
					'ID' => $this->id 
			) );
			
			$this->add_order_note( sprintf( __( 'Unable to change subscription status from %1$s to %2$s. Message: %3$s', 'braintree-payments' ), bfwc_get_subscription_status_name( $old_status ), bfwc_get_subscription_status_name( $new_status ), $e->getMessage() ) );
		
		}
	}

	public function payment_complete( $transaction_id = '' )
	{
		if ( ! empty( $transaction_id ) ) {
			update_post_meta( $this->id, '_transaction_id', $transaction_id );
		}
		
		$this->update_status( 'active' );
	}

	/**
	 * Does the subscription have a length or does it never expire.
	 *
	 * @return bool
	 */
	public function never_expires()
	{
		return 0 === ( int ) $this->get_length();
	}

	public function get_length()
	{
		return $this->subscription_length;
	}

	public function get_billing_interval()
	{
		return $this->subscription_period_interval;
	}

	public function get_period()
	{
		return 'month';
	}

	/**
	 * Return true if the subscription has a trial period.
	 *
	 * @return bool
	 */
	public function has_trial()
	{
		return 0 !== ( int ) $this->get_trial_length();
	}

	public function get_trial_period()
	{
		return $this->subscription_trial_period;
	}

	public function get_trial_length()
	{
		return $this->subscription_trial_length;
	}

	public function get_timezone()
	{
		return $this->subscription_time_zone;
	}

	public function get_date_key( $type )
	{
		return strpos( '_date', $type ) !== false ? $type : "{$type}_date";
	}

	/**
	 * Return the datetime object for the specified date.
	 * All dates are returned in UTC.
	 *
	 * @return DateTime
	 */
	public function get_date( $type )
	{
		$type = $this->get_date_key( $type );
		
		// all dates are stored as UTC in the database.
		$date = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->$type, new DateTimeZone( 'UTC' ) );
		return $date;
	}

	/**
	 *
	 * @param string $type        	
	 * @param
	 *        	mixed string|DateTime $date
	 */
	public function update_date( $type, $date )
	{
		if ( $date instanceof DateTime ) {
			$date->setTimezone( new DateTimeZone( 'UTC' ) );
			$date_string = $date->format( 'Y-m-d H:i:s' );
		} else {
			$date_string = $date;
		}
		$type = '_' . $this->get_date_key( $type );
		update_post_meta( $this->id, $type, $date_string );
	}

	public function calculate_date( $type )
	{
		switch( $type ) {
			case 'next_payment' :
				$last_payment = $this->get_last_pay;
				
				break;
		}
	}

	/**
	 * Calculate the next payment date given a date.
	 *
	 * @param DateTime $date        	
	 */
	protected function get_next_payment_date( $date )
	{
		$next_payment_date = clone $date;
		
		switch( $this->subscription_period ) {
			case 'day' :
				$date->add( new DateInterval( 'P' . $this->subscription_period_interval . 'D' ) );
				break;
			case 'month' :
				$period = 'M';
				// calculate the date.
				$periods_to_add = $this->subscription_period_interval;
				
				// set the day of month to first day that way adding months, wont affect february.
				$next_payment_date->setDate( $next_payment_date->format( 'Y' ), $next_payment_date->format( 'm' ), 1 );
				$next_payment_date->add( new DateInterval( "P{$periods_to_add}{$period}" ) );
				$current_day = $date->format( 'j' ); // day of month, no leading zeros
				$days_in_date = $date->format( 't' );
				$days_in_current_month = $next_payment_date->format( 't' ); // days in month
				
				/**
				 * current date's billing day exceeds the nex billing dates days in month, or this is the last day.
				 */
				if ( $current_day > $days_in_current_month || $days_in_date === $current_day ) {
					// days of previous month were greater so add as last day.
					$next_payment_date->setDate( $next_payment_date->format( 'Y' ), $next_payment_date->format( 'm' ), $days_in_current_month );
				} else {
					$next_payment_date->setDate( $next_payment_date->format( 'Y' ), $next_payment_date->format( 'm' ), $current_day );
				}
				break;
			default :
				$period = 'M';
		}
		return apply_filters( 'get_next_payment_date', $next_payment_date, $this );
	}

	/**
	 * Return the descriptor for the type provided.
	 * Valid types are <strong>name</strong>, <strong>phone</strong>, <strong>url</strong>
	 *
	 * @param unknown $type        	
	 */
	public function get_descriptor( $type )
	{
		$descriptors = $this->descriptor;
		if ( $descriptors ) {
			return isset( $descriptors [ $type ] ) ? $descriptors [ $type ] : '';
		}
		return '';
	}

	/**
	 * Return true if descriptors have been configured for the subscription.
	 */
	public function has_descriptors()
	{
		return ( bool ) $this->descriptors;
	}

	public function update_payment_method_title( $title )
	{
		update_post_meta( $this->id, '_payment_method_title', $title );
	}

	public function update_payment_method_token( $token = '' )
	{
		update_post_meta( $this->id, '_payment_method_token', $token );
	}

	/**
	 *
	 * @param bool $bool        	
	 */
	public function set_created( $bool )
	{
		update_post_meta( $this->id, '_created_in_braintree', $bool );
	}

	/**
	 * Return true if the subscription has been created within Braintree.
	 */
	public function is_created()
	{
		return $this->created_in_braintree;
	}

	public function get_formatted_total()
	{
		$total = $this->get_total();
		extract( array (
				'decimal_separator' => wc_get_price_decimal_separator(), 
				'thousand_separator' => wc_get_price_thousand_separator(), 
				'decimals' => wc_get_price_decimals(), 
				'price_format' => get_woocommerce_price_format() 
		) );
		$total = number_format( $total, $decimals, $decimal_separator, $thousand_separator );
		$total_string = sprintf( '%s%s', bfwcs_get_currency_symbol( $this->get_currency() ), $total );
		$total_string = bfwcs_get_price_string( $total_string, $this->subscription_period_interval, $this->subscription_period );
		return apply_filters( 'bfwc_subscription_formatted_order_total', $total_string, $this );
	}

	public function get_currency( $context = 'view' )
	{
		if ( bwc_is_wc_3_0_0_or_more() ) {
			return parent::get_currency( $context );
		} else {
			return parent::get_order_currency();
		}
	}

	/**
	 * Return a formatted date string using the timezone that the subscription was created in.
	 *
	 * @param string $type        	
	 * @param string $format        	
	 */
	public function get_formatted_date( $type, $format = null )
	{
		$format = $format ? $format : get_option( 'date_format' );
		$date = $this->get_date( $type );
		
		switch( $type ) {
			case 'next_payment' :
				if ( ! $this->has_child_orders() && ! $this->has_trial() && $date == $this->get_date( 'start' ) ) {
					// calculate the next payment date since first payment hasn't occured and there is no trial period.
					$date = $this->get_next_payment_date( $date );
				}
				break;
		}
		if ( $date ) {
			$date->setTimezone( new DateTimeZone( $this->subscription_time_zone ? $this->subscription_time_zone : 'UTC' ) );
			return $date->format( $format );
		}
		switch( $type ) {
			case 'end' :
				return __( 'Never Expires', 'braintree-payments' );
		}
	}

	/**
	 * return true if child orders have been processed for the subscription.
	 */
	public function has_child_orders()
	{
		global $wpdb;
		
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_subscription_id' AND meta_value = %s", $this->id ) );
		return ! empty( $result );
	}

	public function get_payment_method_to_display()
	{
		return apply_filters( 'bfwc_payment_method_to_display', $this->payment_method_title, $this );
	}

	public function get_view_subscription_url()
	{
		$url = wc_get_endpoint_url( 'view-subscription', $this->id, wc_get_page_permalink( 'myaccount' ) );
		return apply_filters( 'bfwc_subscription_view_url', $url, $this );
	}

	public function update_meta( $key, $value )
	{
		if ( strpos( $key, '_' ) !== 0 ) {
			$key = '_' . $key;
		}
		update_post_meta( $this->id, $key, $value );
	}

	/**
	 * Return true if the subscription starts on the last day of the month.
	 *
	 * @return bool
	 */
	public function last_day_of_month()
	{
		$date = $this->get_date( 'start' );
		$date->setTimezone( new DateTimeZone( $this->subscription_time_zone ) );
		if ( $date->format( 'j' ) === $date->format( 't' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Sync the subscription's dates before creating in Braintree.
	 * This will allow for accurate date calculations.
	 */
	public function sync_dates()
	{
		if ( ! $this->is_created() ) {
			
			// update all of the dates.
			$this->update_date( 'start', bfwcs_calculate_start_date() );
			$this->update_date( 'first_payment', bfwcs_calculate_first_payment_date( $this->get_trial_period(), $this->get_trial_length() ) );
			$this->update_date( 'end', bfwcs_calculate_end_date( $this->get_length(), $this->get_period(), $this->get_trial_period(), $this->get_trial_length(), $this->get_timezone() ) );
			$this->update_date( 'next_payment', $this->get_date( 'first_payment' ) );
			if ( $this->has_trial() ) {
				$this->update_date( 'trial_end', $this->get_date( 'first_payment' ) );
			}
		}
	}

	/**
	 * Return the number of billing cycles a subsciption has.
	 */
	public function get_num_of_billing_cycles()
	{
		return ! $this->never_expires() ? floor( $this->get_length() / $this->get_billing_interval() ) : 0;
	}

	public function get_checkout_payment_url( $on_checkout = false )
	{
		$url = wc_get_page_permalink( 'myaccount' );
		
		if ( 'yes' == get_option( 'woocommerce_force_ssl_checkout' ) || is_ssl() ) {
			$url = str_replace( 'http:', 'https:', $url );
		}
		return wc_get_endpoint_url( 'pay-subscription', $this->id, $url );
	}

	/**
	 * Return if the subscription's status can be update to the provided status.
	 *
	 * @param string $status        	
	 */
	public function can_be_updated_to( $status )
	{
		$current_status = $this->get_status();
		$result = true;
		$status = 'wc-' === substr( $status, 0, 3 ) ? substr( $status, 3 ) : $status;
		switch( $status ) {
			case 'active' :
				if ( $this->has_status( array (
						'expired', 
						'cancelled' 
				) ) ) {
					$result = false;
				}
				break;
			case 'cancelled' :
				if ( ! $this->has_status( array (
						'active', 
						'on-hold', 
						'past-due' 
				) ) ) {
					$result = false;
				}
				break;
			case 'on-hold' :
				if ( ! $this->has_status( array (
						'active', 
						'processing', 
						'pending' 
				) ) ) {
					$result = false;
				}
				break;
			case 'expired' :
				if ( ! $this->has_status( array (
						'active', 
						'on-hold' 
				) ) ) {
					$result = false;
				}
				break;
			case 'past-due' :
				if ( ! $this->has_status( array (
						'active', 
						'on-hold', 
						'processing', 
						'pending' 
				) ) ) {
					$result = false;
				}
				break;
		}
		return apply_filters( 'bfwc_subscription_can_be_updated_to', $result, $this );
	}
}