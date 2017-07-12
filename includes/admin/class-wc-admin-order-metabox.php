<?php

class Braintree_Gateway_Admin_Order_Metabox
{

	public function __construct()
	{
		add_action( 'add_meta_boxes', array (
				$this, 
				'add_metaboxes' 
		) );
		add_action( 'woocommerce_admin_order_data_after_order_details', array (
				$this, 
				'output_status' 
		) );
	}

	public function add_metaboxes()
	{
		add_meta_box( 'braintree-woocommerce-settlement-amount', __( 'Capture Amount', 'braintree-payments' ), array (
				$this, 
				'output_settlement' 
		), 'shop_order', 'side' );
	}

	public function output_settlement( $post )
	{
		include bt_manager()->plugin_admin_path() . 'meta-box-html/order-settlement.php';
	}

	/**
	 *
	 * @param WC_Order $order        	
	 */
	public function output_status( $order )
	{
		if ( ! in_array( bwc_get_order_property( 'payment_method', $order ), bwc_get_payment_gateways() ) ) {
			return; // This order is not a braintree order.
		}
		
		if ( get_class( $order ) !== 'WC_Order' ) {
			return;
		}
		
		global $braintree_transaction;
		
		$id = $order->get_transaction_id();
		
		if ( empty( $id ) ) {
			return;
		}
		
		if ( ! $braintree_transaction ) {
			try {
				$braintree_transaction = Braintree_Transaction::find( $id );
			} catch( \Braintree\Exception $e ) {
				return;
			}
		}
		echo '<p class="form-field form-field-wide"><label for=""> ' . __( 'Braintree Transaction Status', 'braintree-payments' ) . '</label><p>' . strtoupper( $braintree_transaction->status ) . '</p>';
	}

}
new Braintree_Gateway_Admin_Order_Metabox();