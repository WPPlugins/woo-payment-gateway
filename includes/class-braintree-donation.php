<?php

class Braintree_Gateway_Donation
{
	
	public $id = 0;
	
	public $post;
	
	public $formatted_billing_address;

	/**
	 *
	 * @param number $id        	
	 */
	public function __construct( $post )
	{
		if ( $post instanceof WP_Post ) {
			$this->id = $post->ID;
			$this->post = $post;
		} elseif ( is_numeric( $post ) ) {
			$this->id = absint( $post );
			$this->post = get_post( $this->id );
		}
	}

	public function __get( $key )
	{
		return get_post_meta( $this->id, $key, true );
	}

	/**
	 *
	 * @param string $key        	
	 * @param mixed $value        	
	 */
	public function __set( $key, $value )
	{
		update_post_meta( $this->id, $key, $value );
	}

	public function __isset( $key )
	{
		return metadata_exists( 'post', $this->id, $key );
	}

	public function get_status()
	{
		return $this->post->post_status;
	}

	/**
	 * Update the status of the donation.
	 * <ul><li>complete</li><li>processing</li><li>cancelled</li></ul>
	 *
	 * @param unknown $status        	
	 */
	public function update_status( $status )
	{
		if ( strpos( $status, 'btd' ) === false ) {
			$status = 'btd-' . $status;
		}
		wp_update_post( array (
				'ID' => $this->id, 
				'post_status' => $status 
		) );
	}

	public function get_formatted_billing_address()
	{
		$fields = apply_filters( 'braintree_donation_get_billing_address_fields', array (
				'first_name' => $this->billing_first_name, 
				'last_name' => $this->billing_last_name, 
				'company' => $this->billing_company, 
				'country' => $this->billing_country ? $this->billing_country : 'US', 
				'city' => $this->billing_city, 
				'address_1' => $this->billing_address_1, 
				'address_2' => $this->billing_address_2, 
				'state' => $this->billing_state, 
				'postalcode' => $this->billing_postalcode 
		) );
		$this->formatted_billing_address = Braintree_Gateway_Countries::get_formatted_address( $fields );
		return $this->formatted_billing_address;
	}

	public function get_billing_address_fields()
	{
		$address_fields = array ();
		$fields = Braintree_Gateway_Countries::get_address_fields( $this->billing_country );
		foreach ( $fields as $key => $field ) {
			$address_fields [ 'billing_' . $key ] = $field;
		}
		return $address_fields;
	}

	public function get_transaction_id()
	{
		return $this->transaction_id;
	}

	public function set_amount( $amount )
	{
		$this->amount = $amount;
	}

	public function get_refunds()
	{
		$refunds = get_post_meta( $this->id, 'refunds', true );
		if ( ! $refunds ) {
			$refunds = array ();
		}
		return $refunds;
	}

	/**
	 * Add a refund for the donation.
	 * Refund array should be in the following format.
	 * <div><code>array('time' => '2016-13-10', 'amount' => 5)</code></div>
	 * When a refund is added, the donation amount is updated in this method.
	 *
	 * @param array $refund        	
	 */
	public function add_refund( $refund )
	{
		$this->amount = $this->amount - $refund [ 'amount' ];
		$refunds = $this->get_refunds();
		$refunds [] = $refund;
		update_post_meta( $this->id, 'refunds', $refunds );
	}

	public function update_amount( $amount )
	{
		$this->amount = $amount;
	}

	public function get_formatted_status()
	{
		return get_post_status_object( $this->post->post_status )->label;
	}

	/**
	 * Add a note associated with the donation.
	 *
	 * @param string $message        	
	 */
	public function add_note( $message = '' )
	{
		$note = array (
				'comment_post_ID' => $this->id, 
				'comment_content' => $message, 
				'comment_author' => 'Braintree Plugin' 
		);
		
		wp_insert_comment( $note );
	}

	public function get_notes()
	{
		$notes = get_comments( array (
				'post_id' => $this->id 
		) );
		return $notes;
	}
}