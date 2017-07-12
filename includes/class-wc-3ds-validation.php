<?php
/**
 * @since 2.6.7
 * @author Payment Plugins
 *
 */
class WC_Braintree_Gateway_3DS_Validation
{
	/**
	 *
	 * @var \Braintree\PaymentMethodNonce
	 */
	private $nonce = null;
	
	/**
	 *
	 * @var \Braintree\ThreeDSecureInfo
	 */
	private $threeds_info = '';

	/**
	 *
	 * @param \Braintree\PaymentMethodNonce $payment_method_nonce        	
	 */
	public function __construct( $payment_method_nonce )
	{
		$this->set_nonce( $payment_method_nonce );
		$this->threeds_info = $payment_method_nonce->threeDSecureInfo;
	}

	public function init()
	{
		$actions = apply_filters( 'bfwc_add_3ds_filters', array (
				'authorize_only' => array (
						'braintree_woocommerce_' . WC_Braintree_Payment_Gateway::ID . '_order_attributes' => 'WC_Braintree_Payment_Gateway::threeds_authorize' 
				), 
				'reject' => array (
						'woocommerce_after_checkout_validation' => 'WC_Braintree_Payment_Gateway::threeds_reject' 
				), 
				'accept' => array (
						'braintree_woocommerce_' . WC_Braintree_Payment_Gateway::ID . '_order_attributes' => 'WC_Braintree_Payment_Gateway::threeds_accept' 
				) 
		) );
		$action = '';
		if ( $this->is_card_ineligible() ) {
			$action = bt_manager()->get_option( '3ds_card_ineligible' );
		} elseif ( ! $this->is_liability_shifted() ) {
			$action = bt_manager()->get_option( '3ds_liability_not_shifted' );
		}
		if ( isset( $actions [ $action ] ) ) {
			foreach ( $actions [ $action ] as $filter => $function ) {
				if ( is_string( $function ) && function_exists( $function ) ) {
					$reflection_function = new ReflectionFunction( $function );
					$args_num = $reflection_function->getNumberOfParameters();
				} elseif ( is_string( $function ) && strpos( $function, '::' ) !== false ) {
					$method = substr( $function, strpos( $function, '::' ) + 2 );
					$class = substr( $function, 0, strpos( $function, '::' ) );
					$reflection_method = new ReflectionMethod( $class, $method );
					$args_num = $reflection_method->getNumberOfParameters();
				} elseif ( is_array( $function ) ) {
					$object = key( $function );
					$method = current( $function );
					$class_name = is_object( $object ) ? get_class( $object ) : $object;
					$reflection_method = new ReflectionMethod( $class_name, $method );
					$args_num = $reflection_method->getNumberOfParameters();
				}
				add_action( $filter, $function, 10, $args_num );
			}
		}
	}

	/**
	 */
	public function get_nonce()
	{
		return $this->nonce;
	}

	/**
	 *
	 * @param \Braintree\PaymentMethodNonce $nonce        	
	 */
	public function set_nonce( $nonce )
	{
		$this->nonce = $nonce;
	}

	public function is_liability_shifted()
	{
		return $this->threeds_info->liabilityShifted;
	}

	public function is_liability_shift_possible()
	{
		return $this->threeds_info->liabilityShiftPossible;
	}

	public function is_card_ineligible()
	{
		return ! $this->is_liability_shifted() && ! $this->is_liability_shift_possible();
	}
}