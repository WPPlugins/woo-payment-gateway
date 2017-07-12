<?php

/**
 * Return true if the drop-in form is enabled for donations.
 * @return boolean
 */
function bfwcd_dropin_enabled()
{
	return bt_manager()->get_option( 'donation_form_type' ) === 'dropin';
}

/**
 * Return true if custom forms are enabled for donations.
 *
 * @return boolean
 */
function bfwcd_custom_form_enabled()
{
	return bt_manager()->get_option( 'donation_form_type' ) === 'custom';
}

function bfwcd_paypal_enabled()
{
	return bt_manager()->is_active( 'paypal_donation_enabled' );
}

function bfwcd_paypal_credit_enabled()
{
	return bt_manager()->is_active( 'paypal_credit_donation_enabled' );
}

function bfwcd_get_custom_form()
{
	$form = bt_manager()->get_option( 'donation_custom_form_design' );
	return bfwcd_get_custom_forms() [ $form ];
}

function bfwcd_get_custom_forms()
{
	return include bt_manager()->plugin_include_path() . 'braintree-custom-donation-forms.php';
}

function bfwcd_get_paypal_button()
{
	$button = bt_manager()->get_option( 'paypal_donation_button_design' );
	$buttons = bfwcd_get_paypal_buttons();
	return $buttons [ $button ];
}

function bfwcd_get_paypal_buttons()
{
	return include bt_manager()->plugin_include_path() . 'paypal-donation-buttons.php';
}

function bfwcd_get_paypal_credit_buttons()
{
	return include bt_manager()->plugin_include_path() . 'paypal-credit-donation-buttons.php';
}

function bfwcd_get_paypal_credit_button()
{
	$button = bt_manager()->get_option( 'paypal_credit_donation_button' );
	$buttons = bfwcd_get_paypal_credit_buttons();
	return $buttons [ $button ];
}

/**
 * Return true if the current page is the donation page.
 *
 * @return boolean
 */
function bfwcd_is_donation_page()
{
	global $post;
	if ( ! $post ) {
		return;
	}
	return has_shortcode( $post->post_content, 'braintree_donations' ) || has_shortcode( $post->post_content, 'braintree_recurring_donation' );
}

function bfwcd_is_recurring_donation_page()
{
	global $post;
	if ( ! $post ) {
		return;
	}
	return has_shortcode( $post->post_content, 'braintree_recurring_donation' );
}

/**
 * Return the currency configured for donations.
 *
 * @return string
 */
function bfwcd_get_donation_currency()
{
	return bt_manager()->get_option( 'donation_currency' );
}

function bfwcd_add_notice( $message, $type = 'success' )
{
	global $bfwcd_messages;
	$bfwcd_messages = ! $bfwcd_messages ? array () : $bfwcd_messages;
	$bfwcd_messages [ $type ] [] = $message;
}

/**
 * Return true if there are error messages associated with the donation.
 *
 * @return boolean
 */
function bfwcd_has_errors()
{
	global $bfwcd_messages;
	
	return $bfwcd_messages ? ! empty( $bfwcd_messages [ 'error' ] ) : false;
}

function bfwcd_get_error_messages()
{
	global $bfwcd_messages;
	return $bfwcd_messages ? ( ! empty( $bfwcd_messages [ 'error' ] ) ? $bfwcd_messages [ 'error' ] : '' ) : false;
}

/**
 * Return a donation object.
 *
 * @param WP_POST|int $post        	
 * @return Braintree_Gateway_Donation
 */
function bfwcd_get_donation( $post )
{
	$post = is_object( $post ) ? $post : get_post( $post );
	$class = '';
	switch( $post->post_type ) {
		case 'braintree_donation' :
			$class = 'Braintree_Gateway_Donation';
			break;
		case 'bt_rc_donation' :
			$class = 'Braintree_Gateway_Recurring_Donation';
			break;
	}
	return new $class( $post );
}

function bfwcd_create_donation( $args = array() )
{
	$args = wp_parse_args( $args, array (
			'donation_message' => '', 
			'first_name' => 'N/A', 
			'last_name' => 'N/A', 
			'billing_address1' => '', 
			'billing_address2' => '', 
			'billing_country' => '', 
			'billing_city' => '', 
			'billing_state' => '', 
			'email_address' => '', 
			'currency' => '' 
	) );
	$post_args = array (
			'post_title' => sprintf( __( 'Donation %s', 'braintree-payments' ), strftime( '%b %d %Y @ %H %M %S', time() ) ), 
			'post_content' => '', 
			'post_excerpt' => '', 
			'post_type' => 'braintree_donation', 
			'post_status' => 'btd-processing', 
			'post_author' => 0 
	);
	$post_id = wp_insert_post( $post_args );
	foreach ( $args as $arg => $value ) {
		update_post_meta( $post_id, $arg, $value );
	}
	return $post_id;
}

function bfwcd_create_recurring_donation( $args = array() )
{
	$args = wp_parse_args( $args, array (
			'donation_message' => '', 
			'first_name' => 'N/A', 
			'last_name' => 'N/A', 
			'billing_address1' => '', 
			'billing_address2' => '', 
			'billing_country' => '', 
			'billing_city' => '', 
			'billing_state' => '', 
			'email_address' => '', 
			'currency' => '' 
	) );
	$post_args = array (
			'post_title' => sprintf( __( 'Donation %s', 'braintree-payments' ), strftime( '%b %d %Y @ %H %M %S', time() ) ), 
			'post_content' => '', 
			'post_excerpt' => '', 
			'post_type' => 'bt_rc_donation', 
			'post_status' => 'btd-processing', 
			'post_author' => 0 
	);
	$post_id = wp_insert_post( $post_args );
	foreach ( $args as $arg => $value ) {
		update_post_meta( $post_id, $arg, $value );
	}
	return $post_id;
}

function bfwcd_update_donation( $post_id, $args = array() )
{
	foreach ( $args as $arg => $value ) {
		update_post_meta( $post_id, $arg, $value );
	}
}

function bfwcd_get_recurring_donation( $post )
{
	return new Braintree_Gateway_Recurring_Donation( $post );
}

function bfwcd_get_donation_statuses()
{
	return array (
			'btd-processing' => __( 'Processing', 'braintree-payments' ), 
			'btd-complete' => __( 'Complete', 'braintree-payments' ), 
			'btd-cancelled' => __( 'Cancelled', 'braintree-payments' ) 
	);
}

function bfwcd_modal_enabled()
{
	return bt_manager()->get_option( 'donation_form_layout' ) === 'modal';
}

function bfwcd_get_loader()
{
	$name = bt_manager()->get_option( 'donation_custom_form_loader_file' );
	return 'donations/loader/' . $name;
}

function bfwcd_advanced_fraud_enabled()
{
	return bt_manager()->is_active( 'donation_fraud_tools' );
}