<?php
/**
 * @since 2.6.7
 * @author Payment Plugins
 *
 */
class Braintree_Gateway_WC_Cart_Calculations
{
	
	public static $gateway_fee_calculation = false;

	public static function init()
	{
		add_action( 'woocommerce_cart_calculate_fees', __CLASS__ . '::calculate_fees' );
		
		add_action( 'woocommerce_after_calculate_totals', __CLASS__ . '::after_calculate_totals' );
	}

	/**
	 *
	 * @since 2.6.7
	 * @param WC_Cart $cart        	
	 */
	public static function after_calculate_totals( $cart )
	{
		if ( bwc_fees_enabled() && ! self::$gateway_fee_calculation ) {
			if ( $gateway = WC()->session->get( 'chosen_payment_method', null ) ) {
				if ( bwc_fee_enabled_for_gateway( $gateway ) ) {
					self::$gateway_fee_calculation = true;
					// save current cart total since calculate_totals() resets the value.
					WC()->session->set( 'bfwc_cart_total', $cart->total );
					$cart->calculate_totals();
					self::$gateway_fee_calculation = false;
				}
			}
		}
	}

	/**
	 *
	 * @param WC_Cart $cart        	
	 */
	public static function calculate_fees( $cart )
	{
		if ( self::$gateway_fee_calculation ) {
			$fees = bwc_get_fees_for_gateway( WC()->session->get( 'chosen_payment_method' ) );
			foreach ( $fees as $fee ) {
				$fee_amount = bwc_calculate_fee( $fee, array (
						'cost' => WC()->session->get( 'bfwc_cart_total', 0 ), 
						'qty' => $cart->get_cart_contents_count() 
				) );
				if ( $fee_amount ) {
					$taxable = isset( $fee [ 'tax_status' ] ) ? ( $fee [ 'tax_status' ] === 'taxable' ? true : false ) : false;
					$cart->add_fee( $fee [ 'name' ], $fee_amount, $taxable );
				}
			}
		}
	}
}
Braintree_Gateway_WC_Cart_Calculations::init();