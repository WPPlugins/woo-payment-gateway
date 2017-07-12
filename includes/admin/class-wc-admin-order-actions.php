<?php

/**
 * 
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *
 */
class Braintree_Gateway_WC_Admin_Order_Actions
{

	public function __construct()
	{
		add_filter( 'woocommerce_order_actions', array (
				$this, 
				'add_order_actions' 
		) );
		add_action( 'woocommerce_order_action_braintree_void_transaction', array (
				$this, 
				'void_transaction' 
		) );
		add_action( 'woocommerce_order_action_braintree_submit_for_settlement', array (
				$this, 
				'submit_for_settlement' 
		) );
	}

	public function add_order_actions( $actions )
	{
		global $theorder; // WC_Order object.
		global $braintree_transaction;
		
		if ( ! in_array( $theorder->payment_method, bwc_get_payment_gateways() ) ) {
			return $actions; // This order is not a braintree order.
		}
		
		if ( get_class( $theorder ) !== 'WC_Order' ) {
			return $actions;
		}
		
		if ( $theorder->get_status() === 'failed' ) {
			return $actions; // payment failed so there is not a transaction id yet.
		}
		
		$id = $theorder->get_transaction_id();
		
		if ( empty( $id ) ) {
			return $actions;
		}
		
		try {
			if ( ! $braintree_transaction ) {
				$braintree_transaction = Braintree_Transaction::find( $id );
			}
			
			switch( $braintree_transaction->status) {
				case Braintree_Transaction::AUTHORIZED :
					$actions [ 'braintree_submit_for_settlement' ] = __( 'Capture Authorized Transaction', 'braintree-payments' );
					$actions [ 'braintree_void_transaction' ] = __( 'Void Transaction', 'braintree-payments' );
					break;
				case Braintree_Transaction::SUBMITTED_FOR_SETTLEMENT :
					$actions [ 'braintree_void_transaction' ] = __( 'Void Transaction', 'braintree-payments' );
					break;
				default :
					break;
			}
		} catch( \Braintree\Exception $e ) {
			do_action( 'braintree_transaction_notfound_exception' );
			$message = sprintf( __( 'Transaction %s was not found in environment %s.', 'braintree-payments' ), $id, bt_manager()->get_environment() );
			bt_manager()->error( $message );
		}
		return $actions;
	}

	/**
	 * Void the transaction associated with the WC_Order.
	 *
	 * @param WC_Order $order        	
	 */
	public function void_transaction( $order )
	{
		$id = $order->get_transaction_id();
		try {
			$result = Braintree_Transaction::void( $id );
			if ( $result->success ) {
				$order->update_status( 'cancelled' );
				$order->add_order_note( sprintf( __( 'Transaction %s has been voided.', 'braintree-payments' ), $id ) );
			} else {
				$order->add_order_note( sprintf( __( 'There was an error voiding the transaction. Reason: %s', 'braintree-payments' ), $result->message ) );
			}
		} catch( \Braintree\Exception $e ) {
			$message = sprintf( __( 'Transaction %s could not be voided. Exception: %s', 'braintree-payments' ), $id, get_class( $e ) );
			$order->add_order_note( $message );
			bt_manager()->error( $message );
			do_action( 'braintree_transaction_void_exception', $e, $order );
		}
	}

	/**
	 * Submit the transaction associated with the WC_Order for settlement.
	 *
	 * @param WC_Order $order        	
	 */
	public function submit_for_settlement( $order )
	{
		$id = $order->get_transaction_id();
		$amount = $_POST [ 'braintree_settlement_amount' ];
		try {
			$result = Braintree_Transaction::submitForSettlement( $id, $amount );
			if ( $result->success ) {
				
				$order->set_total( $amount ); // update the order total with the
				                              // captured amount.
				$order->add_order_note( sprintf( __( 'Transaction %s was submitted for settlement in the amount of %s%s.', 'braintree-payments' ), $id, get_woocommerce_currency_symbol( $order->order_currency ), $amount ) );
			} else {
				$order->add_order_note( sprintf( __( 'There was an error submitting transaction %s for settlement. Reason: %s', 'braintree-payments' ), $id, $result->message ) );
			}
		} catch( \Braintree\Exception $e ) {
			$message = sprintf( __( 'There was an error submitting transaction %s for settlement. Exception: %s', 'braintree-payments' ), $id, get_class( $e ) );
			$order->add_order_note( $message );
			bt_manager()->error( $message );
			do_action( 'braintree_transaction_settle_exception', $e, $order );
		}
	}
}
new Braintree_Gateway_WC_Admin_Order_Actions();