<?php
/**
 * @since 2.6.0
 * @author Payment Plugins
 *
 */
class Braintree_Gateway_WC_Ajax
{

	public static function init()
	{
		add_action( 'template_redirect', __CLASS__ . '::do_bfwc_ajax' );
	}

	/**
	 * Return an endpoint.
	 * The endpoint is not a full url.
	 *
	 * @param string $request        	
	 * @return string
	 */
	public static function get_endpoint( $request = '' )
	{
		return apply_filters( 'bfwc_ajax_endpoint', esc_url_raw( add_query_arg( 'bfwc-ajax', $request ) ), $request );
	}

	public static function do_bfwc_ajax()
	{
		if ( isset( $_REQUEST [ 'bfwc-ajax' ] ) ) {
			$action = sanitize_text_field( $_REQUEST [ 'bfwc-ajax' ] );
			do_action( 'bfwc_ajax_' . $action );
			die();
		}
	}
}
Braintree_Gateway_WC_Ajax::init();