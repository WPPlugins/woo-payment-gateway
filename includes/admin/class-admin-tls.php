<?php

/**
 * 
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *
 */
class Braintree_Gateway_Admin_TLS extends Braintree_Gateway_Page_API
{

	public function __construct()
	{
		$this->page = 'braintree-tls-test';
		$this->tab = 'tls';
		$this->title = array ( 
				'title' => __( 'TLS 1.2 Test', 'braintree-payments' ), 
				'description' => __( 'Starting Janurary of 2017, Braintree will only be accepting TLS 1.2 for connections to their API. This may affect you
                        if you are using a version of openssl that is older than 1.0.1c. You can perform a test here that will tell you if you need to upgrade your openssl on your server. This <a target="_blank" href="https://github.com/paypal/TLS-update#php">page</a> gives a great explanation of the why and how.', 'braintree-payments' ) 
		);
		parent::__construct();
	}

	public static function output()
	{
		global $current_page, $current_tab;
		
		$current_tab = 'tls';
		
		self::maybe_test_tls();
		
		include bt_manager()->plugin_admin_path() . 'views/tls-page.php';
	}

	/**
	 * Perform the TLS test.
	 */
	public static function maybe_test_tls()
	{
		if ( isset( $_POST[ 'braintree_tls_test' ] ) ) {
			$ch = curl_init();
			
			curl_setopt( $ch, CURLOPT_URL, 'https://tlstest.paypal.com/' );
			curl_setopt( $ch, CURLOPT_CAINFO, bt_manager()->plugin_path() . 'ssl/cacert.pem' );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			$response = curl_exec( $ch );
			
			if ( $err = curl_error( $ch ) ) {
				bt_manager()->add_admin_notice( 'error', sprintf( __( 'TLS test response: %s', 'braintree-payments' ), $err ) );
			} else {
				bt_manager()->add_admin_notice( 'success', sprintf( __( 'Your OpenSSL version is up to date. You\'ve got nothing to worry about! Response: %s', 'braintree-payments' ), $response ) );
			}
		}
	}
}
new Braintree_Gateway_Admin_TLS();