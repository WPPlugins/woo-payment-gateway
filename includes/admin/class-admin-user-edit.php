<?php

/**
 * Admin class used to save and display user information.
 * 
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *
 */
class Braintree_Gateway_Admin_User_Edit
{

	public function __construct()
	{
		add_action( 'edit_user_profile', array (
				$this, 
				'output' 
		) );
		add_action( 'show_user_profile', array (
				$this, 
				'output' 
		) );
		add_action( 'edit_user_profile_update', array (
				$this, 
				'save' 
		) );
		add_action( 'personal_options_update', array (
				$this, 
				'save' 
		) );
		
		add_action( 'bulk_actions-users', __CLASS__ . '::add_bulk_actions' );
		
		// add_action( 'restrict_manage_users', __CLASS__ . '::users_maintenance_actions' );
		
		add_action( 'handle_bulk_actions-users', __CLASS__ . '::handle_user_bulk_actions', 10, 3 );
	}

	public function output( $user )
	{
		$sandbox_saved_methods = braintree_get_user_payment_methods( $user->ID, 'sandbox' );
		$production_saved_methods = braintree_get_user_payment_methods( $user->ID, 'production' );
		include bt_manager()->plugin_admin_path() . 'views/user-edit.php';
	}

	public function save( $user_id )
	{
		$envs = array (
				'sandbox'
		);
		$keys = array (
				'braintree_%s_customer_id' => 'braintree_%s_vault_id', 
				'braintree_%s_customer_id' => 'braintree_%s_vault_id' 
		);
		$customer_updated = array ();
		
		foreach ( $envs as $env ) {
			
			foreach ( $keys as $key => $vault_key ) {
				$key = sprintf( $key, $env );
				$vault_key = sprintf( $vault_key, $env );
				if ( isset( $_POST [ $key ] ) ) {
					$old_id = get_user_meta( $user_id, $vault_key, true );
					$new_id = $_POST [ $key ];
					if ( $old_id !== $new_id ) {
						braintree_delete_user_payment_methods( $user_id, $env );
						bt_manager()->save_customer_id( $user_id, $new_id, $env );
						$customer_updated [] = $env;
					}
				}
			}
			
			bt_manager()->initialize_braintree( $env );
			
			if ( ! in_array( $env, $customer_updated ) ) {
				$saved_methods = array_keys( braintree_get_user_payment_methods( $user_id, $env ) );
				$posted_methods = isset( $_POST [ 'braintree_' . $env . '_customer_payment_methods' ] ) ? $_POST [ 'braintree_' . $env . '_customer_payment_methods' ] : array ();
				$diff = array_diff( $saved_methods, $posted_methods );
				if ( ! empty( $diff ) ) {
					foreach ( $diff as $token ) {
						$error = apply_filters( 'bfwc_admin_can_delete_payment_method', new WP_Error(), $token );
						if ( ! $error->get_error_message() ) {
							try {
								$result = Braintree_PaymentMethod::delete( $token );
								if ( $result->success ) {
									braintree_delete_user_payment_method( $user_id, $token, $env );
								}
							} catch( \Braintree\Exception $e ) {
								braintree_delete_user_payment_method( $user_id, $token, $env );
								bfwc_add_admin_notice( sprintf( __( 'Payment method %s could not be deleted within Braintree. Reason: %s', 'braintree-payments' ), braintree_get_payment_title_from_token( $user_id, $token, $env ), bfwc_get_error_message( $e ) ), 'error' );
							}
						} else {
							$message = sprintf( __( 'Payment method %s could not be deleted. Reason: %s', 'braintree-payments' ), braintree_get_payment_title_from_token( $user_id, $token, $env ), $error->get_error_message() );
							bfwc_add_admin_notice( $message, 'error' );
						}
					}
				}
				if ( ! empty( $_POST [ 'bfwc_' . $env . '_payment_nonce' ] ) ) {
					self::save_payment_method( $_POST [ 'bfwc_' . $env . '_payment_nonce' ], $user_id, $env );
				}
			} else {
				self::fetch_customer_payment_methods( $user_id, $env );
			}
		}
		bt_manager()->initialize_braintree();
	}

	public static function save_payment_method( $nonce, $user_id, $env )
	{
		$user = get_user_by( 'id', $user_id );
		try {
			$result = Braintree_PaymentMethod::create( array (
					'customerId' => braintree_get_customer_id( $user->ID, $env ), 
					'paymentMethodNonce' => $nonce, 
					'options' => array (
							'makeDefault' => true, 
							'verifyCard' => true 
					), 
					'cardholderName' => sprintf( '%s %s', $user->first_name, $user->last_name ) 
			) );
			if ( $result->success ) {
				braintree_save_user_payment_method( $user->ID, $result->paymentMethod, $env );
			} else {
				bfwc_add_admin_notice( sprintf( __( 'Error saving payment methods. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $result ) ), 'error' );
			}
		} catch( \Braintree\Exception $e ) {
			bfwc_add_admin_notice( sprintf( __( 'Error saving payment methods. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $e ) ), 'error' );
		}
	}

	public static function fetch_customer_payment_methods( $user_id, $env )
	{
		$customer_id = braintree_get_customer_id( $user_id, $env );
		if ( $customer_id ) {
			try {
				$customer = Braintree_Customer::find( $customer_id );
				$payment_methods = $customer->paymentMethods;
				if ( $payment_methods ) {
					$methods = array ();
					
					foreach ( $payment_methods as $payment_method ) {
						braintree_save_user_payment_method( $user_id, $payment_method, $env );
					}
				}
			} catch( \Braintree\Exception $e ) {
				bfwc_add_admin_notice( sprintf( __( 'Error fetching customer\'s payment methods. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $e ) ), 'error' );
			}
		}
	}

	public static function add_bulk_actions( $actions )
	{
		$actions [ 'bfwc_delete_sandbox' ] = __( 'Delete Sandbox Braintree User Data', 'braintree-payments' );
		return $actions;
	}

	public static function users_maintenance_actions( $which )
	{
		?>
<select name="bfwc_bulk_user_action" id="">
	<option value=""><?php _e('Braintree Bulk Actions', 'braintree-payments')?></option>
	<option value="bfwc_delete_sandbox"><?php _e('Delete Sandbox Data', 'braintree-payments')?></option>
</select>
<?php
	}

	public static function handle_user_bulk_actions( $redirect, $action, $userids )
	{
		global $wpdb;
		$env = null;
		switch( $action ) {
			case 'bfwc_delete_sandbox' :
				$env = 'sandbox';
				break;
		}
		if ( $env ) {
			$meta_keys = array (
					'braintree_' . $env . '_vault_id', 
					'braintree_' . $env . '_payment_methods', 
					'braintree_next_payment_update' 
			);
			$in_meta_keys = vsprintf( implode( ',', array_fill( 0, count( $meta_keys ), "'%s'" ) ), $meta_keys );
			$in_userids = vsprintf( implode( ',', array_fill( 0, count( $userids ), "'%s'" ) ), $userids );
			$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key IN ($in_meta_keys) AND user_id IN ($in_userids)" );
		}
		return $redirect;
	}

	public static function user_params()
	{
		$params = array (
				'production' => array (
						'modal_template' => bfwc_admin_modal_template( true, array (
								'env' => 'production', 
								'title' => __( 'Production Credit Card', 'braintree-payments' ) 
						) ) 
				), 
				'sandbox' => array (
						'modal_template' => bfwc_admin_modal_template( true, array (
								'env' => 'sandbox', 
								'title' => __( 'Sandbox Credit Card', 'braintree-payments' ) 
						) ) 
				), 
				'locale' => get_locale() 
		);
		bt_manager()->initialize_braintree( 'sandbox' );
		$params [ 'sandbox' ] [ 'client_token' ] = bt_manager()->get_client_token();
		bt_manager()->initialize_braintree( 'production' );
		$params [ 'production' ] [ 'client_token' ] = bt_manager()->get_client_token();
		bt_manager()->initialize_braintree();
		return $params;
	}

}
new Braintree_Gateway_Admin_User_Edit();