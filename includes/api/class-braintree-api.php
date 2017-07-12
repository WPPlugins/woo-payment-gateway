<?php
/**
 * Base class that initializes all API controllers.
 * @author Payment Plugins
 *
 */
class Braintree_Gateway_API
{

	public static function init()
	{
		add_action( 'rest_api_init', array ( 
				__CLASS__, 
				'register_controllers' 
		) );
	}

	/**
	 * Register all of the api controllers with Wordpress.
	 */
	public static function register_controllers()
	{
		$controllers = apply_filters( 'bfwc_register_route_controllers', array () );
		
		foreach ( $controllers as $class ) {
			$controller = new $class();
			foreach ( $controller->get_notification_kinds() as $key => $function ) {
				add_action( "bfwc_webhook_{$key}_notification", $function );
			}
		}
	}
}
Braintree_Gateway_API::init();