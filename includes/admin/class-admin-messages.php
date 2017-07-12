<?php
/**
 * 
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *
 */
class Braintree_Gateway_Admin_Messages
{

	public static function output()
	{
		bfwc_admin_get_template( 'views/admin-messages.php' );
		
		add_action( 'wp_ajax_bfwc_admin_fetch_messages', __CLASS__ . '::get_translated_messages' );
	}

	public static function save()
	{
		if ( ! empty( $_POST ) ) {
			if ( ! empty( $_POST [ 'bfwc_admin_messages' ] ) ) {
				$messages = json_decode( stripslashes( $_POST [ 'bfwc_admin_messages' ] ), true );
				update_option( 'bfwc_error_messages', $messages );
				bt_manager()->add_admin_notice( 'success', __( 'Your messages have been saved.', 'braintree-payments' ) );
			} else {
				if ( isset( $_POST [ 'bfwc_reset_messages' ] ) ) {
					update_option( 'bfwc_error_messages', array () );
					bt_manager()->add_admin_notice( 'success', __( 'Your messages have been reset.', 'braintree-payments' ) );
				}
			}
		}
	}

	public static function localize_messages()
	{
		$messages = bfwc_get_combined_error_messages();
		return $messages;
	}

}