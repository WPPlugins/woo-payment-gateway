<?php

/**
 * 
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *
 */
class Braintree_Gateway_Recurring_Donation extends Braintree_Gateway_Donation
{

	public function __construct( $post )
	{
		parent::__construct( $post );
	}

	public static function init()
	{
		add_action( 'bfwc_bt_rc_donation_cancelled', __CLASS__ . '::recurring_donation_cancelled' );
	}

	public function add_refund( $refund )
	{
		$refunds = $this->get_refunds();
		$refunds[] = $refund;
		update_post_meta( $this->id, 'refunds', $refunds );
	}

	/**
	 *
	 * @param Braintree_Gateway_Recurring_Donation $donation 
	 */
	public static function recurring_donation_cancelled( $donation )
	{
		try {
			$result = Braintree_Subscription::cancel( $donation->id );
			
			if ( $result->success ) {
				$donation->add_note( __( 'Recurring donation has been cancelled.', 'braintree-payments' ) );
			} else {
				$donation->add_note( sprintf( __( 'Error cancelling recurring donation. Reason: %s', 'braintree-payments' ), $result->message ) );
			}
		} catch ( \Braintree\Exception $e ) {
			$donation->add_note( sprintf( __( 'Error cancelling recurring donation. Exception: %s', 'braintree-payments' ), get_class( $e ) ) );
		}
	}
}
Braintree_Gateway_Recurring_Donation::init();