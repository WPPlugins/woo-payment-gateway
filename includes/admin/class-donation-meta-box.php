<?php
use Braintree\Exception\NotFound;

class Braintree_Gateway_Donation_Meta_Box
{
	
	private $save_called = false;
	
	public static $post_types = array (
			'braintree_donation', 
			'bt_rc_donation' 
	);

	public function __construct()
	{
		add_action( 'add_meta_boxes', array (
				$this, 
				'add_meta_box' 
		) );
		add_action( 'add_meta_boxes', array (
				$this, 
				'remove_meta_boxes' 
		) );
		add_filter( 'braintree_donation_actions', array (
				$this, 
				'donation_actions' 
		) );
		add_filter( 'braintree_recurring_donation_actions', array (
				$this, 
				'recurring_donation_actions' 
		) );
		add_action( 'save_post', array (
				$this, 
				'save_meta' 
		), 10, 2 );
		add_action( 'braintree_donation_action_refund_donation', array (
				$this, 
				'refund_donation' 
		), 10, 2 );
		add_action( 'braintree_donation_action_capture_donation', array (
				$this, 
				'capture_donation' 
		), 10, 2 );
		add_action( 'braintree_donation_action_void_donation', array (
				$this, 
				'void_donation' 
		), 10, 2 );
		add_action( 'braintree_recurring_donation_action_refund_donation', array (
				$this, 
				'refund_recurring_donation' 
		), 10, 2 );
		add_action( 'braintree_recurring_donation_action_void_donation', array (
				$this, 
				'void_recurring_donation' 
		), 10, 2 );
	}

	public function add_meta_box()
	{
		add_meta_box( 'braintree-donation-box', __( 'Donation', 'braintree-payments' ), array (
				$this, 
				'output_donation' 
		), 'braintree_donation', 'normal', 'high' );
		add_meta_box( 'donation-actions-box', __( 'Donation Actions' ), array (
				$this, 
				'output_actions_box' 
		), 'braintree_donation', 'side', 'high' );
		
		add_meta_box( 'braintree-recurring-donation-box', __( 'Recurring Donation', 'braintree-payments' ), array (
				$this, 
				'output_recurring_donation' 
		), 'bt_rc_donation', 'normal', 'high' );
		add_meta_box( 'donation-actions-box', __( 'Donation Actions' ), array (
				$this, 
				'output_recurring_actions_box' 
		), 'bt_rc_donation', 'side', 'high' );
	}

	public function output_donation( $post )
	{
		wp_nonce_field( 'braintree-donation', '_bfwc_donation_nonce' );
		$donation = bfwcd_get_donation( $post );
		include bt_manager()->plugin_admin_path() . 'meta-box-html/edit-donation.php';
	}

	public function output_recurring_donation( $post )
	{
		global $braintree_subscription;
		if ( ! $braintree_subscription ) {
			try {
				$this->get_braintree_subscription( $post );
			} catch( Exception $e ) {
				return; // return if the subscription could not be found.
			}
		}
		wp_nonce_field( 'braintree-donation', '_bfwc_donation_nonce' );
		$donation = bfwcd_get_recurring_donation( $post );
		include bt_manager()->plugin_admin_path() . 'meta-box-html/edit-recurring-donation.php';
	}

	public function output_actions_box( $post )
	{
		$donation = bfwcd_get_donation( $post );
		include bt_manager()->plugin_admin_path() . 'meta-box-html/donation-actions.php';
	}

	public function output_recurring_actions_box( $post )
	{
		global $braintree_subscription;
		if ( ! $braintree_subscription ) {
			try {
				$this->get_braintree_subscription( $post );
			} catch( Exception $e ) {
				return; // return if the subscription could not be found.
			}
		}
		$donation = bfwcd_get_recurring_donation( $post );
		include bt_manager()->plugin_admin_path() . 'meta-box-html/recurring-donation-actions.php';
	}

	private function get_braintree_subscription( $post )
	{
		global $braintree_subscription;
		$donation = bfwcd_get_recurring_donation( $post );
		try {
			$braintree_subscription = Braintree_Subscription::find( $donation->id );
		} catch( NotFound $e ) {
			bt_manager()->add_admin_notice( 'error', sprintf( __( 'Subscription %s could no be found in your %s Braintree environemnt.' ), $donation->id, bt_manager()->get_environment() ) );
			throw new Exception();
		} catch( Braintree\Exception $e ) {
			bt_manager()->add_admin_notice( 'error', sprintf( __( 'An exception was thrown while retrieving subscription %s from your %s Braintree environment.' ), $donation->id, bt_manager()->get_environment() ) );
			throw new Exception();
			;
		}
	}

	/**
	 * Populate the donations action array with actions that can be performed on
	 * the donation.
	 *
	 * @param array $array        	
	 * @return array
	 */
	public function donation_actions( $array )
	{
		global $post;
		$donation = bfwcd_get_donation( $post );
		
		try {
			$transaction = Braintree_Transaction::find( $donation->get_transaction_id() );
			switch( $transaction->status ) {
				case Braintree_Transaction::SETTLED :
				case Braintree_Transaction::SETTLING :
					$array [ 'refund_donation' ] = __( 'Refund Donation', 'braintree-payments' );
					break;
				case Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT :
					$array [ 'void_donation' ] = __( 'Void Donation', 'braintree-payments' );
					break;
				case Braintree_Transaction::AUTHORIZED :
					$array [ 'capture_donation' ] = __( 'Capture Donation', 'braintree-payments' );
					$array [ 'void_donation' ] = __( 'Void Donation', 'braintree-payments' );
					break;
				default :
					break;
			}
		} catch( Braintree\Exception $e ) {
			bt_manager()->add_admin_notice( 'error', sprintf( __( 'Transcation %s could not be found in your %s Braintree environment.', 'braintree-payments' ), $donation->get_transaction_id(), bt_manager()->get_environment() ) );
		}
		
		return $array;
	}

	public function recurring_donation_actions( $actions )
	{
		global $braintree_subscription, $post;
		$actions [ 'refund_donation' ] = __( 'Refund Transaction', 'braintree-payments' );
		$actions [ 'void_donation' ] = __( 'Void Transaction', 'braintree-payments' );
		
		return $actions;
	}

	public function remove_meta_boxes()
	{
		foreach ( self::$post_types as $type ) {
			remove_meta_box( 'commentsdiv', $type, 'normal' );
			remove_meta_box( 'woothemes-settings', $type, 'normal' );
			remove_meta_box( 'commentstatusdiv', $type, 'normal' );
			remove_meta_box( 'slugdiv', $type, 'normal' );
			remove_meta_box( 'submitdiv', $type, 'side' );
		}
	}

	public function save_meta( $post_id, $post )
	{
		if ( empty( $post_id ) || $this->save_called || ! isset( $_POST [ '_bfwc_donation_nonce' ] ) || ! wp_verify_nonce( $_POST [ '_bfwc_donation_nonce' ], 'braintree-donation' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( ! in_array( $post->post_type, self::$post_types ) ) {
			return;
		}
		$this->save_called = true;
		
		global $braintree_donation;
		switch( $post->post_type ) {
			case 'braintree_donation' :
				$braintree_donation = bfwcd_get_donation( $post );
				break;
			case 'bt_rc_donation' :
				$braintree_donation = bfwcd_get_recurring_donation;
				break;
		}
		
		$address_fields = $braintree_donation->get_billing_address_fields();
		
		foreach ( $address_fields as $key => $field ) {
			if ( isset( $_POST [ $key ] ) ) {
				update_post_meta( $post_id, $key, $_POST [ $key ] );
			}
		}
		
		if ( $_POST [ 'donation_status' ] !== $post->post_status ) {
			$post_status = $_POST [ 'donation_status' ];
			$donation_status = strpos( $post_status, 'btd-' ) !== false ? substr( $post_status, 4 ) : $post_status;
			wp_update_post( array (
					'ID' => $post->ID, 
					'post_status' => $post_status 
			) );
			do_action( "bfwc_{$post->post_type}_$donation_status", $braintree_donation );
		}
		
		if ( ! empty( $_POST [ 'braintree_donation_actions' ] ) ) {
			do_action( 'braintree_donation_action_' . $_POST [ 'braintree_donation_actions' ], $post_id, $post );
		}
		if ( ! empty( $_POST [ 'braintree_recurring_donation_actions' ] ) ) {
			do_action( 'braintree_recurring_donation_action_' . $_POST [ 'braintree_recurring_donation_actions' ], $post_id, $post );
		}
	}

	public function refund_donation( $post_id, $post )
	{
		global $braintree_donation;
		$amount = $_POST [ 'donation_refund_amount' ];
		if ( ! $braintree_donation->get_transaction_id() ) {
			bt_manager()->add_admin_notice( 'error', sprintf( __( 'Donation %s cannot be refunded. There is no transaction ID associated with the donation', 'braintree-payments' ), $post_id ) );
		} else {
			try {
				$result = Braintree_Transaction::refund( $braintree_donation->get_transaction_id(), $amount );
				if ( $result->success ) {
					
					$braintree_donation->add_refund( array (
							'time' => $result->transaction->createdAt->format( 'Y-m-d H:i:s' ), 
							'amount' => $amount, 
							'transaction' => $result->transaction->id 
					) );
					if ( $braintree_donation->amount == 0 ) {
						$braintree_donation->update_status( 'cancelled' );
					}
					bt_manager()->add_admin_notice( 'success', sprintf( __( 'Refund for donation was processed.', 'braintree-payments' ), $result->message ) );
				} else {
					bt_manager()->add_admin_notice( 'error', sprintf( __( 'Refund for donation was not processed. Reason: %s', 'braintree-payments' ), $result->message ) );
				}
			} catch( NotFound $e ) {
				bt_manager()->add_admin_notice( 'error', sprintf( __( 'Transaction %s could not be found in your %s Braintree environment.', 'braintree-payments' ), $donation->get_transaction_id(), bt_manager()->get_environment() ) );
			} catch( Braintree\Exception $e ) {
				bt_manager()->add_admin_notice( 'error', sprintf( __( 'There was an error processing the refund.', 'braintree-payments' ) ) );
			}
		}
	}

	/**
	 * Void the transaction for the given donation.
	 *
	 * @param int $post_id        	
	 * @param WP_Post $post        	
	 */
	public function void_donation( $post_id, $post )
	{
		global $braintree_donation;
		try {
			$result = Braintree_Transaction::void( $braintree_donation->get_transaction_id() );
			if ( $result->success ) {
				
				$braintree_donation->update_status( 'cancelled' );
				
				bt_manager()->add_admin_notice( 'success', sprintf( __( 'Transaction %s has been voided.', 'braintree-payments' ), $braintree_donation->get_transaction_id() ) );
			} else {
				$message = sprintf( __( 'Transaction %s could not be voided. Reason: %s', 'braintree-payments' ), $braintree_donation->get_transaction_id(), $result->message, true );
				bt_manager()->add_admin_notice( 'error', $message );
				bt_manager()->error( $message );
			}
		} catch( NotFound $e ) {
			$message = sprintf( __( 'Transaction %s was not found in your %s Braintree environment.', 'braintree-payments' ), $braintree_donation->get_transaction_id(), bt_manager()->get_environment() );
			bt_manager()->add_admin_notice( 'error', $message );
			bt_manager()->error( $message );
		} catch( Braintree\Exception $e ) {
			bt_manager()->add_admin_notice( 'error', sprintf( __( 'There was an Exception thrown while voiding transaction %s.', 'braintree-payments' ), $braintree_donation->get_transaction_id() ) );
		}
	}

	/**
	 * Capture the donation amount.
	 * This submits the donation for settlement.
	 *
	 * @param int $post_id        	
	 * @param WP_POST $post        	
	 */
	public function capture_donation( $post_id, $post )
	{
		global $braintree_donation;
		$amount = $_POST [ 'donation_capture_amount' ];
		$transaction_id = $braintree_donation->get_transaction_id();
		try {
			$result = Braintree_Transaction::submitForSettlement( $transaction_id, $amount );
			if ( $result->success ) {
				
				$braintree_donation->update_amount( $amount ); // Update the new
				                                               // amount.
				$braintree_donation->update_status( 'complete' );
				
				bt_manager()->add_admin_notice( 'success', sprintf( __( 'Transaction %s was submitted for settlement.', 'braintree-payments' ), $transaction_id ) );
			} else {
				bt_manager()->add_admin_notice( 'error', sprintf( __( 'There was an error submitting transaction %s for settlement. Reason: %s', 'braintree-payments' ), $transaction_id, print_r( $result->errors, true ) ) );
			}
		} catch( Braintree\Exception $e ) {
			bt_manager()->add_admin_notice( 'error', sprintf( __( 'Transaction %s could not be captured.', 'braintree-payments' ), $transaction_id ) );
		}
	}

	public function refund_recurring_donation( $post_id, $post )
	{
		global $braintree_donation;
		$transaction_id = $_POST [ 'recurring_donation_transaction' ];
		$amount = $_POST [ 'donation_refund_amount' ];
		try {
			$result = Braintree_Transaction::refund( $transaction_id, $amount );
			if ( $result->success ) {
				$braintree_donation->add_refund( array (
						'amount' => $amount, 
						'time' => $result->transaction->createdAt->format( 'Y-m-d H:i:s' ), 
						'transaction' => $result->transaction->id 
				) );
				$braintree_donation->add_note( sprintf( __( 'Transaction %s was refunded successfully', 'braintree-payments' ), $transaction_id ) );
				bt_manager()->add_admin_notice( 'success', sprintf( __( 'Transaction %s was refunded successfully.', 'braintree-payments' ), $transaction_id ) );
			} else {
				$braintree_donation->add_note( sprintf( __( 'Error refunding transaction %s. Reason: %s', 'braintree-payments' ), $transaction_id, $result->message ) );
			}
		} catch( NotFound $e ) {
			bt_manager()->add_admin_notice( 'error', sprintf( __( 'Transaction %s could not be found in your %s Braintree environment.', 'braintree-payments' ), $donation->get_transaction_id(), bt_manager()->get_environment() ) );
		} catch( Braintree\Exception $e ) {
			bt_manager()->add_admin_notice( 'error', sprintf( __( 'There was an error processing the refund.', 'braintree-payments' ) ) );
		}
	}

	public function void_recurring_donation( $post_id, $post )
	{
		global $braintree_donation;
		$transaction_id = $_POST [ 'transaction_to_void' ];
		try {
			$result = Braintree_Transaction::void( $transaction_id );
			if ( $result->success ) {
				$message = sprintf( __( 'Transaction %s was successfully voided.', 'braintree-payments' ), $transaction_id );
				bt_manager()->add_admin_notice( 'success', $message );
				$braintree_donation->add_note( $message );
			} else {
				$message = sprintf( __( 'Transaction %s could not be voided. Reason: %s', 'braintree-payments' ), $transaction_id, $result->message );
				$braintree_donation->add_note( $message );
				bt_manager()->add_admin_notice( 'error', $message );
			}
		} catch( NotFound $e ) {
			bt_manager()->add_admin_notice( 'error', sprintf( __( 'Transaction %s could not be found in your %s Braintree environment.', 'braintree-payments' ), $donation->get_transaction_id(), bt_manager()->get_environment() ) );
		} catch( Braintree\Exception $e ) {
			bt_manager()->add_admin_notice( 'error', sprintf( __( 'There was an error voiding the transaction.', 'braintree-payments' ) ) );
		}
	}
}
new Braintree_Gateway_Donation_Meta_Box();