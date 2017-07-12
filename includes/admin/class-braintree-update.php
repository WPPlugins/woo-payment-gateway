<?php
class Braintree_Gateway_Update
{

	private static function get_updates()
	{
		return array (
				'2.5.4' => bt_manager()->plugin_admin_path() . 'updates/braintree-update-2.5.4.php', 
				'2.6.0' => bt_manager()->plugin_admin_path() . 'updates/braintree-update-2.6.0.php' 
		);
	}

	public static function init()
	{
		
		/* Run updates when admin_init action is called. */
		add_action( 'braintree_wc_before_init', __CLASS__ . '::check_version' );
		
		add_action( 'in_plugin_update_message-woo-payment-gateway/braintree-payments.php', __CLASS__ . '::get_admin_update_notice' );
		add_action( 'activate_woo-payment-gateway/braintree-payments.php', __CLASS__ . '::maybe_install' );
	}

	/**
	 * On plugin activate, add the version if necessary.
	 */
	public static function maybe_install()
	{
		$version = get_option( 'braintree_for_woocommerce_version' );
		if ( ! $version ) {
			
			$previous_versions = array ();
			foreach ( self::get_updates() as $v => $file ) {
				$previous_versions [ $v ] = $v;
			}
			
			update_option( 'braintree_for_woocommerce_version', array (
					'currentVersion' => bt_manager()->version, 
					'previousVersions' => $previous_versions 
			) );
		}
	}

	/**
	 * Check the version of the current installation.
	 */
	public static function check_version()
	{
		$version = get_option( 'braintree_for_woocommerce_version' );
		if ( ! $version || version_compare( $version [ 'currentVersion' ], bt_manager()->version, '<' ) ) {
			self::update();
			add_action( 'admin_notices', __CLASS__ . '::update_notice' );
		}
	}

	public static function update()
	{
		if ( ! get_option( 'braintree_for_woocommerce_version' ) ) {
			$previousVersions = array ();
			foreach ( self::get_updates() as $version => $update ) {
				if ( file_exists( $update ) ) {
					include_once ( $update );
					$previousVersions [ $version ] = $version;
				}
			}
		} else {
			$versions = get_option( 'braintree_for_woocommerce_version' );
			if ( ! is_array( $versions ) ) {
				delete_option( 'braintree_for_woocommerce_version' );
				$versions = array (
						'previousVersions' => array () 
				);
			}
			$previousVersions = $versions [ 'previousVersions' ];
			foreach ( self::get_updates() as $version => $update ) {
				if ( ! array_key_exists( $version, $previousVersions ) ) {
					if ( file_exists( $update ) ) {
						include_once $update;
						$previousVersions [ $version ] = $version;
					}
				}
			}
		}
		update_option( 'braintree_for_woocommerce_version', array (
				'currentVersion' => bt_manager()->version, 
				'previousVersions' => $previousVersions 
		) );
		
		do_action( 'bfwc_admin_after_plugin_update' );
	}

	public static function update_notice()
	{
		$message = sprintf( __( 'Thank you for updating Braintree For WooCommerce to version %s.', 'braintree-payments' ), bt_manager()->version );
		echo '<div class="notice notice-success is-dismissible"><p>' . $message . '</p></div>';
	}

	public static function get_admin_update_notice( $args )
	{
		$response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/woo-payment-gateway/trunk/readme.txt' );
		if ( $response instanceof WP_Error ) {
			bt_manager()->error( sprintf( 'There was an error retrieving the update notices. %s', print_r( $response, true ) ) );
		} else {
			$content = ! empty( $response [ 'body' ] ) ? $response [ 'body' ] : '';
			self::parse_update_notice_content( $content );
		}
	}

	/**
	 * Parse the content for the update notice.
	 *
	 * @param string $content
	 *        	The content retrieved from the readme.txt file.
	 */
	public static function parse_update_notice_content( $content )
	{
		$pattern = '/==\s*Upgrade Notice\s*==\s*=\s*([0-9.]*)\s*=\s*(.*)/';
		if ( preg_match( $pattern, $content, $matches ) ) {
			$version = $matches [ 1 ];
			$notice = $matches [ 2 ];
			if ( version_compare( $version, bt_manager()->version, '>' ) ) {
				echo '<div class="wc_plugin_upgrade_notice">' . $notice . '</div>';
			}
		}
	}
}
Braintree_Gateway_Update::init();