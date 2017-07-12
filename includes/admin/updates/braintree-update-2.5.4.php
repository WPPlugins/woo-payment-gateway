<?php

/**
 * Update for version 2.5.4. This version consists of a complete code rewrite. As such, it is necessary to
 * convert some of the settings to the new format.
 */
function bfwc_admin_plugin_update_two_five_three()
{
	// redirect the user to the update.php page now so that the plugin can update to the pro version.
	$license = bt_manager()->get_option( 'license' );
	if ( ! empty( $license ) ) {
		
		$current = get_site_transient( 'update_plugins' );
		// ensure plugins are fetched again.
		$current->last_checked = $current->last_checked - 12 * HOUR_IN_SECONDS;
		remove_filter( 'pre_set_site_transient_update_plugins', 'bfwc_admin_update_pro' );
		set_site_transient( 'update_plugins', $current );
		$url = add_query_arg( array (
				'plugin' => 'woo-payment-gateway-pro/braintree-payments.php', 
				'action' => 'upgrade-plugin', 
				'_wpnonce' => wp_create_nonce( 'upgrade-plugin_woo-payment-gateway-pro/braintree-payments.php' ) 
		), admin_url( 'update.php' ) );
		wp_redirect( $url );
		exit();
	}
}
add_action( 'bfwc_admin_after_plugin_update', 'bfwc_admin_plugin_update_two_five_three' );
