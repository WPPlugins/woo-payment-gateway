<?php

use Braintree\WebhookNotification;

/**
 * WooCommerce admin functions.
 */

/**
 * Return an array of admin pages that are associated with the plugin.
 *
 * @return mixed
 */
function bfwc_admin_pages()
{
	global $bfwc_admin_pages;
	return apply_filters( 'braintree_wc_admin_pages', $bfwc_admin_pages );
}

function bfwc_add_submenu_page( $menu )
{
	global $bfwc_admin_pages;
	$bfwc_admin_pages [] = $menu [ 'menu_slug' ];
	$page = add_submenu_page( $menu [ 'parent_slug' ], $menu [ 'page_title' ], $menu [ 'menu_title' ], $menu [ 'capability' ], $menu [ 'menu_slug' ], $menu [ 'callback' ] );
	if ( isset( $menu [ 'load_page_callback' ] ) ) {
		add_action( 'load-' . $page, $menu [ 'load_page_callback' ] );
	}
}

function bwc_admin_webhooks()
{
	return apply_filters( 'bwc_admin_webhooks', array (
			WebhookNotification::SUBSCRIPTION_CANCELED => __( 'Subscription Cancelled', 'braintree-payments' ), 
			WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY => __( 'Subscription Charged Successfully', 'braintree-payments' ), 
			WebhookNotification::SUBSCRIPTION_CHARGED_UNSUCCESSFULLY => __( 'Subscription Charged Unsuccesfully', 'braintree-payments' ), 
			WebhookNotification::SUBSCRIPTION_EXPIRED => __( 'Subscription Expired', 'braintree-payments' ), 
			WebhookNotification::SUBSCRIPTION_TRIAL_ENDED => __( 'Subscription Trial Ended', 'braintree-payments' ), 
			WebhookNotification::SUBSCRIPTION_WENT_ACTIVE => __( 'Subscription Went Active', 'braintree-payments' ), 
			WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE => __( 'Subscription Went Past Due', 'braintree-payments' ), 
			// WebhookNotification::TRANSACTION_SETTLED => __( 'Transaction Settled', 'braintree-payments' ),
			// WebhookNotification::TRANSACTION_SETTLEMENT_DECLINED => __( 'Transaction Settlement Declined', 'braintree-payments' ),
			WebhookNotification::CHECK => __( 'Webhook Connection Check', 'braintree-payments' ) 
	) );
}

/**
 * function that parses content between a start and end tag.
 *
 * @param string $content        	
 * @param string $start        	
 * @param string $end        	
 * @param bool $include_start_end        	
 */
function bwc_admin_parse_contents( $content, $start, $end, $include_start_end = true )
{
	// <subscription><add-ons></add-ons></subscription>
	$strpos = strpos( $content, $start ) + strlen( $start ); // $start = $subscription
	$strpos2 = strrpos( $content, $end ); // last occurance of $end
	$length = absint( $strpos2 - $strpos );
	$contents = substr( $content, $strpos, $length );
	return $include_start_end ? trim( sprintf( '%s%s%s', $start, $contents, $end ) ) : trim( $contents );
}

function bfwc_admin_get_template( $template, $args = array() )
{
	extract( $args );
	$file = bt_manager()->plugin_admin_path() . $template;
	if ( ! file_exists( $file ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( 'template file %s does not exist.', 'braintree-payments' ), bt_manager()->version );
	}
	include $file;
}

/**
 * Return an array of WC order statuses.
 */
function bwc_admin_wc_get_order_statuses()
{
	$order_statuses = array (
			'wc-pending' => _x( 'Pending Payment', 'Order status', 'woocommerce' ), 
			'wc-processing' => _x( 'Processing', 'Order status', 'woocommerce' ), 
			'wc-on-hold' => _x( 'On Hold', 'Order status', 'woocommerce' ), 
			'wc-completed' => _x( 'Completed', 'Order status', 'woocommerce' ), 
			'wc-cancelled' => _x( 'Cancelled', 'Order status', 'woocommerce' ), 
			'wc-refunded' => _x( 'Refunded', 'Order status', 'woocommerce' ), 
			'wc-failed' => _x( 'Failed', 'Order status', 'woocommerce' ) 
	);
	return $order_statuses;
}

function bfwc_admin_get_subscription_plans()
{
	$key = sprintf( 'braintree_wc_%s_plans', bt_manager()->get_environment() );
	return get_option( $key, array () );
}

function bfwc_admin_status_name( $status )
{
	$statuses = array (
			'active' => __( 'Active', 'braintree-payments' ), 
			'inactive' => __( 'Inactive', 'braintree-payments' ), 
			'expired' => __( 'Expired', 'braintree-payments' ), 
			'pending' => __( 'Pending', 'braintree-payments' ) 
	);
	return isset( $statuses [ $status ] ) ? $statuses [ $status ] : $status;
}

function bfwc_admin_add_help_center_tab( $tabs )
{
	$tabs [] = array (
			'url' => 'https://support.paymentplugins.com', 
			'label' => __( 'Help Center', 'braintree-payments' ), 
			'id' => '' 
	);
	return $tabs;
}
add_filter( 'bfwc_admin_header_tabs', 'bfwc_admin_add_help_center_tab' );

function bfwc_admin_license_status_notice()
{
	echo '<div class="notice notice-warning is-dismissible">';
	echo '<p style="font-size: 18px">' . sprintf( __( 'Attention: Braintree For WooCommerce Pro license status is %s.', 'braintree-payments' ), bt_manager()->get_license_status() ) . '</p>';
	echo '<p>' . __( 'To accept live payments, ensure your license status is active.', 'braintree-payments' ) . '</p>';
	echo '</div>';
}

function bfwc_admin_domain_mistmatch_notice()
{
	$reg_domain = get_option( 'bfwc_registered_domain' );
	echo '<div class="notice notice-warning is-dismissible">';
	echo '<p style="font-size: 18px">' . sprintf( __( 'Attention: Braintree For WooCommerce Pro is registered under domain <strong>%s</strong>. To prevent interruption of payments on <strong>%s</strong> you must refresh your license and activate on this domain.', 'braintree-payments' ), $reg_domain, bt_manager()->get_domain() ) . '</p>';
	echo '</div>';
}

function bfwc_add_admin_notice( $message, $type = 'success' )
{
	$messages = get_option( 'bfwc_admin_notices', array () );
	$messages [ $type ] [] = $message;
	update_option( 'bfwc_admin_notices', $messages );
}

function bfwc_print_admin_notices()
{
	$messages = get_option( 'bfwc_admin_notices', array () );
	if ( ! empty( $messages ) ) {
		foreach ( $messages as $type => $messages ) {
			foreach ( $messages as $message ) {
				echo '<div class="notice notice-' . $type . ' is-dismissible"><p style="font-size: 14px">' . $message . '</p></div>';
			}
		}
	}
	delete_option( 'bfwc_admin_notices' );
}

function bfwc_admin_modal_template( $ob_start = false, $args )
{
	if ( $ob_start ) {
		ob_start();
	}
	bfwc_admin_get_template( 'views/modal-template.php', $args );
	if ( $ob_start ) {
		return ob_get_clean();
	}
}

function bfwc_admin_backbone_template( $name, $ob_start = false, $args = array() )
{
	if ( $ob_start ) {
		ob_start();
	}
	bfwc_admin_get_template( 'templates/' . $name . '.php', $args );
	if ( $ob_start ) {
		return ob_get_clean();
	}
}