<?php

class Braintree_Gateway_Admin_Menus
{

	public static function init()
	{
		add_action( 'admin_menu', array (
				__CLASS__, 
				'add_menus' 
		) );
	}

	public static function add_menus()
	{
		$submenu_pages = self::submenu_pages();
		
		foreach ( self::menus_pages() as $page ) {
			add_menu_page( $page [ 'page_title' ], $page [ 'menu_title' ], $page [ 'capability' ], $page [ 'menu_slug' ], $page [ 'callback' ], $page [ 'icon_url' ], $page [ 'position' ] );
			
			foreach ( $submenu_pages as $submenu_page ) {
				if ( $submenu_page [ 'parent_slug' ] === $page [ 'menu_slug' ] ) {
					bfwc_add_submenu_page( $submenu_page );
				}
			}
		}
		
		remove_submenu_page( 'braintree-gateway-page', 'braintree-gateway-page' );
	}

	public static function menus_pages()
	{
		return apply_filters( 'bwc_admin_menus_pages', array (
				array (
						'page_title' => __( 'Braintree Gateway', 'braintree-payments' ), 
						'menu_title' => __( 'Braintree Gateway', 'braintree-payments' ), 
						'capability' => 'manage_options', 
						'menu_slug' => 'braintree-gateway-page', 
						'callback' => null, 
						'icon_url' => null, 
						'position' => 7.528 
				) 
		) );
	}

	public static function submenu_pages()
	{
		return apply_filters( 'bwc_admin_submenu_pages', array (
				array (
						'parent_slug' => 'braintree-gateway-page', 
						'page_title' => __( 'Settings', 'braintree-payments' ), 
						'menu_title' => __( 'Settings', 'braintree-payments' ), 
						'capability' => 'manage_options', 
						'menu_slug' => 'braintree-gateway-settings', 
						'callback' => __CLASS__ . '::settings_page' 
				), 
				array (
						'parent_slug' => 'braintree-gateway-page', 
						'page_title' => __( 'Message Customization', 'braintree-payments' ), 
						'menu_title' => __( 'Message Customization', 'braintree-payments' ), 
						'capability' => 'manage_options', 
						'menu_slug' => 'braintree-messages-page', 
						'callback' => __CLASS__ . '::messages_page', 
						'load_page_callback' => 'Braintree_Gateway_Admin_Messages::save' 
				), 
				array (
						'parent_slug' => 'braintree-gateway-page', 
						'page_title' => __( 'Logs', 'braintree-payments' ), 
						'menu_title' => __( 'Logs', 'braintree-payments' ), 
						'capability' => 'manage_options', 
						'menu_slug' => 'braintree-gateway-logs', 
						'callback' => __CLASS__ . '::logs_page' 
				), 
				array (
						'parent_slug' => 'braintree-gateway-page', 
						'page_title' => __( 'Webhook Test', 'braintree-payments' ), 
						'menu_title' => __( 'Webhook Test', 'braintree-payments' ), 
						'capability' => 'manage_options', 
						'menu_slug' => 'braintree-webhook-test', 
						'callback' => __CLASS__ . '::webhook_page' 
				), 
				array (
						'parent_slug' => 'braintree-gateway-page', 
						'page_title' => __( 'TLS 1.2 Test', 'braintree-payments' ), 
						'menu_title' => __( 'TLS 1.2 Test', 'braintree-payments' ), 
						'capability' => 'manage_options', 
						'menu_slug' => 'braintree-tls-test', 
						'callback' => __CLASS__ . '::tls_page' 
				), 
				array (
						'parent_slug' => 'braintree-gateway-page', 
						'page_title' => __( 'Donations', 'braintree-payments' ), 
						'menu_title' => __( 'Donations', 'braintree-payments' ), 
						'capability' => 'manage_options', 
						'menu_slug' => 'braintree-donations-page', 
						'callback' => __CLASS__ . '::donations_page' 
				), 
				array (
						'parent_slug' => 'braintree-gateway-page', 
						'page_title' => __( 'Test Data', 'braintree-payments' ), 
						'menu_title' => __( 'Test Data', 'braintree-payments' ), 
						'capability' => 'manage_options', 
						'menu_slug' => 'braintree-test-data-page', 
						'callback' => __CLASS__ . '::test_data_page' 
				), 
				array (
						'parent_slug' => 'braintree-gateway-page', 
						'page_title' => __( 'Help Center', 'braintree-payments' ), 
						'menu_title' => __( 'Help Center', 'braintree-payments' ), 
						'capability' => 'manage_options', 
						'menu_slug' => 'braintree-help-center', 
						'callback' => 'trivial_function_bfwc_dont_matter', 
						'load_page_callback' => __CLASS__ . '::help_center_link' 
				) 
		) );
	}

	public static function settings_page()
	{
		Braintree_Gateway_Admin_Settings::output();
	}

	public static function logs_page()
	{
		Braintree_Gateway_Admin_Logs::output();
	}

	public static function tls_page()
	{
		Braintree_Gateway_Admin_TLS::output();
	}

	public static function webhook_page()
	{
		Braintree_Gateway_Admin_Webhooks::output();
	}

	public static function donations_page()
	{
		Braintree_Gateway_Admin_Donation_Page::output();
	}

	public static function license_page()
	{
		Braintree_Gateway_Admin_License::output();
	}

	public static function messages_page()
	{
		Braintree_Gateway_Admin_Messages::output();
	}

	public static function test_data_page()
	{
		bfwc_admin_get_template( 'views/admin-test-data.php' );
	}

	public function help_center_link()
	{
		wp_redirect( 'https://support.paymentplugins.com' );
		exit();
	}
}
Braintree_Gateway_Admin_Menus::init();