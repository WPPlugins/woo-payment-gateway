<?php

/**
 * 
 * @author Payment Plugins
 * @copyright Payment Plugins 2016
 *
 */
class Braintree_Gateway_Admin_Settings
{

	public static function init()
	{
		add_action( 'admin_init', __CLASS__ . '::globals' );
	}

	public static function globals()
	{
		global $current_tab, $current_page;
		
		$current_page = isset( $_REQUEST [ 'page' ] ) ? $_REQUEST [ 'page' ] : '';
		$current_tab = isset( $_REQUEST [ 'tab' ] ) ? $_REQUEST [ 'tab' ] : 'general';
	}

	public static function output()
	{
		global $current_tab, $current_page;
		
		if ( ! empty( $_POST ) ) { // Post has data so save first.
			if ( ! empty( $current_tab ) ) {
				self::save();
			}
			bt_manager()->add_admin_notice( 'success', __( 'Your Braintree settings have been saved.', 'braintree-payments' ) );
		}
		$tabs = apply_filters( 'braintree_gateway_settings_tabs', array () );
		bfwc_admin_get_template( 'views/admin-settings.php', array (
				'tabs' => $tabs, 
				'current_page' => $current_page 
		) );
	}

	public static function save()
	{
		global $current_tab;
		do_action( 'braintree_gateway_before_save_settings' );
		do_action( "braintree_gateway_{$current_tab}_save_settings" );
		do_action( "braintree_gateway_after_save_settings" );
	}
}
Braintree_Gateway_Admin_Settings::init();