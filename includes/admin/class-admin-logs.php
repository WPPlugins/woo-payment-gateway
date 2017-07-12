<?php

class Braintree_Gateway_Admin_Logs
{

	public static function output()
	{
		global $current_page, $current_tab;
		
		$current_tab = 'logs';
		
		if ( ! empty( $_POST ) ) {
			self::maybe_delete_logs();
		}
		
		include bt_manager()->plugin_admin_path() . 'views/log-entries.php';
	}

	public static function maybe_delete_logs()
	{
		$nonce = isset( $_POST[ '_wpnonce' ] ) ? $_POST[ '_wpnonce' ] : '';
		if ( isset( $_POST[ 'braintree_gateway_delete_logs' ] ) && wp_verify_nonce( $nonce, 'braintree-gateway-delete-logs' ) ) {
			bt_manager()->log->delete_log_entries();
			bt_manager()->add_admin_notice( 'success', __( 'Your logs have been deleted.', 'braintree-payments' ) );
		}
	}
}