<?php

class Braintree_Post_Types
{

	public function __construct()
	{
		add_action( 'init', array (
				$this, 
				'register_post' 
		) );
		add_action( 'init', array (
				$this, 
				'register_posts_statuses' 
		) );
		add_filter( 'manage_braintree_donation_posts_columns', array (
				$this, 
				'donation_columns' 
		) );
		add_filter( 'manage_braintree_donation_posts_custom_column', array (
				$this, 
				'render_donation_column_data' 
		) );
		add_filter( 'manage_bt_rc_donation_posts_columns', array (
				$this, 
				'donation_columns' 
		) );
		add_filter( 'manage_bt_rc_donation_posts_custom_column', array (
				$this, 
				'render_donation_column_data' 
		) );
		add_filter( 'manage_bfwc_subscription_posts_columns', array (
				$this, 
				'subscription_columns' 
		) );
		add_filter( 'manage_bfwc_subscription_posts_custom_column', array (
				$this, 
				'render_subscription_columns' 
		) );
		add_filter( 'manage_edit-bfwc_subscription_sortable_columns', array (
				$this, 
				'subscription_sortable_columns' 
		) );
		
		add_filter( 'posts_clauses', array (
				$this, 
				'posts_clauses' 
		), 10, 2 );
		add_filter( 'request', array (
				$this, 
				'request_query' 
		), 100 );
	}

	public function register_post()
	{
		register_post_type( 'braintree_donation', array (
				'label' => __( 'Braintree Donation', 'braintree-payments' ), 
				
				'labels' => array (
						'name' => __( 'Braintree Donation', 'braintree-payments' ), 
						'singular_name' => __( 'Braintree Donation', 'braintree-payments' ) 
				), 
				'description' => __( 'Donation made through the Braintree Plugin.', 'braintree-payments' ), 
				'public' => false, 
				'capabilities' => array (
						'create_posts' => false 
				), 
				'map_meta_cap' => true, 
				'show_in_menu' => 'edit.php', 
				'show_in_nav_menus' => false, 
				'show_in_admin_bar' => false, 
				'show_ui' => true 
		) );
		
		// Braintree Recurring Donation (bt_rc_donation)
		register_post_type( 'bt_rc_donation', array (
				'label' => __( 'Recurring Donation', 'braintree-payments' ), 
				
				'labels' => array (
						'name' => __( 'Recurring Donation', 'braintree-payments' ), 
						'singular_name' => __( 'Recurring Donation', 'braintree-payments' ) 
				), 
				'description' => __( 'Recurring donation made through the Braintree Plugin.', 'braintree-payments' ), 
				'public' => false, 
				'capabilities' => array (
						'create_posts' => false 
				), 
				'map_meta_cap' => true, 
				'show_in_menu' => 'edit.php', 
				'show_in_nav_menus' => false, 
				'show_in_admin_bar' => false, 
				'show_ui' => true 
		) );
	}

	public function register_posts_statuses()
	{
		// (Braintree Donation bdt)
		$post_statuses = apply_filters( 'braintree_register_post_statuses', array (
				'btd-complete' => array (
						'label' => __( 'Completed', 'braintree-payments' ), 
						'public' => true, 
						'exclude_from_search' => false, 
						'show_in_admin_all_list' => true, 
						'show_in_admin_status_list' => true, 
						'label_count' => _n_noop( 'Complete <span class="count">(%s)</span>', 'Complete <span class="count">(%s)</span>', 'braintree-payments' ) 
				), 
				'btd-processing' => array (
						'label' => __( 'Processing', 'braintree-payments' ), 
						'public' => true, 
						'exclude_from_search' => false, 
						'show_in_admin_all_list' => true, 
						'show_in_admin_status_list' => true, 
						'label_count' => _n_noop( 'Processing <span class="count">(%s)</span>', 'Processing <span class="count">(%s)</span>', 'braintree-payments' ) 
				), 
				'btd-cancelled' => array (
						'label' => __( 'Cancelled', 'braintree-payments' ), 
						'public' => true, 
						'exclude_from_search' => false, 
						'show_in_admin_all_list' => true, 
						'show_in_admin_status_list' => true, 
						'label_count' => _n_noop( 'Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'braintree-payments' ) 
				) 
		) );
		
		foreach ( $post_statuses as $status => $values ) {
			register_post_status( $status, $values );
		}
	
	}

	public function subscription_columns( $existing_columns )
	{
		unset( $existing_columns [ 'title' ] );
		unset( $existing_columns [ 'date' ] );
		unset( $existing_columns [ 'comments' ] );
		$columns = array (
				'cb' => '<input type="checkbox"/>', 
				'status' => __( 'Status', 'braintree-payments' ), 
				'subscription' => __( 'Subscription', 'braintree-payments' ), 
				'items' => __( 'Items', 'braintree-payments' ), 
				'recurring_total' => __( 'Total', 'braintree-payments' ), 
				'start_date' => __( 'Start Date', 'braintree-payments' ), 
				'trial_end' => __( 'Trial End', 'braintree-payments' ), 
				'next_payment' => __( 'Next Payment Date', 'braintree-payments' ), 
				'end_date' => __( 'End Date', 'braintree-payments' ) 
		);
		
		return wp_parse_args( $existing_columns, $columns );
	}

	public function subscription_sortable_columns( $existing_columns )
	{
		$columns = array (
				'status' => 'post_status', 
				'subscription' => 'ID', 
				'recurring_total' => '_order_total', 
				'start_date' => '_start_date', 
				'next_payment' => '_next_payment_date', 
				'end_date' => '_end_date' 
		);
		return wp_parse_args( $columns, $existing_columns );
	}

	public function donation_columns( $existing_columns )
	{
		unset( $existing_columns [ 'title' ] );
		$columns = array (
				'cb' => '<input type="checkbox"/>', 
				'status' => __( 'Status', 'braintree-payments' ), 
				'donation' => __( 'Donation', 'braintree-payments' ), 
				'donor' => __( 'Donor Name', 'braintree-payments' ), 
				'amount' => __( 'Amount', 'braintree-payments' ) 
		);
		return array_merge( $columns, $existing_columns );
	}

	public function render_donation_column_data( $column )
	{
		global $post;
		$donation = bfwcd_get_donation( $post );
		switch( $column ) {
			case 'status' :
				echo '<span>' . $donation->get_formatted_status() . '</span>';
				break;
			case 'donation' :
				echo '<strong><a href="' . get_edit_post_link( $post->ID ) . '">#' . $post->ID . '</a></strong>';
				break;
			case 'donor' :
				echo $donation->billing_first_name . ' ' . $donation->billing_last_name;
				break;
			case 'amount' :
				if ( $post->post_type === 'braintree_donation' ) {
					echo '<span>' . braintree_get_currency_symbol( $donation->currency ) . '' . $donation->amount . '</span>';
				} elseif ( $post->post_type === 'bt_rc_donation' ) {
					echo '<span>' . sprintf( '%s %s / %s', braintree_get_currency_symbol( $donation->currency ), $donation->amount, __( 'Month', 'braintree-payments' ) ) . '</span>';
				}
				break;
			default :
				break;
		}
	}

	public function render_subscription_columns( $column )
	{
		global $post;
		$subscription = bfwcs_get_subscription( $post->ID );
		$status = get_post_status_object( $subscription->post_status );
		$user = get_userdata( $subscription->customer_user );
		switch( $column ) {
			case 'status' :
				echo '<div class="' . $subscription->get_status() . '"><mark class="' . $subscription->get_status() . ' tips">' . $status->label . '</mark></div>';
				break;
			case 'subscription' :
				if ( empty( $user ) ) {
					printf( '<a href="%s"><strong>#%s</strong></a>', get_edit_post_link( $post->ID ), $post->ID );
				} else {
					printf( '<div class="tips"><a href="%s"><strong>#%s</strong></a> %s <a href="%s">%s %s</a> </div>', get_edit_post_link( $post->ID ), $subscription->id, __( 'for', 'braintree-payments' ), get_edit_user_link( $user->ID ), $user->user_firstname, $user->user_lastname );
				}
				break;
			case 'items' :
				foreach ( $subscription->get_items() as $item_id => $item ) {
					$product = wc_get_product( $item [ 'product_id' ] );
					printf( '<div class="order-item"><a href="%s">%s</a></div>', get_edit_post_link( $item [ 'product_id' ] ), $product->get_title() );
				}
				break;
			case 'recurring_total' :
				echo $subscription->get_formatted_total();
				printf( '<small class="meta">%s %s</small>', __( 'Via', 'braintree-payments' ), $subscription->payment_method_title );
				break;
			case 'start_date' :
				$date = $subscription->get_formatted_date( 'start' );
				printf( '<time class="start_date">%s</time>', $date );
				break;
			case 'trial_end' :
				if ( $subscription->has_trial() ) {
					printf( '<time class="start_date">%s</time>', $subscription->get_formatted_date( 'trial_end' ) );
				} else {
					echo __( 'N/A', 'braintree-payments' );
				}
				break;
			case 'next_payment' :
				printf( '<time class="next_payment">%s</time>', $subscription->get_formatted_date( 'next_payment' ) );
				break;
			case 'end_date' :
				if ( $subscription->never_expires() ) {
					echo __( 'Never Expires', 'braintree-payments' );
				} else {
					printf( '<time class="end_date">%s</time>', $subscription->get_formatted_date( 'end' ) );
				}
				break;
		}
	}

	public function request_query( $vars )
	{
		global $typenow, $wp_query, $wp_post_statuses;
		if ( $typenow === 'bfwc_subscription' ) {
			
			$post_statuses = bfwc_get_subscription_statuses();
			
			$post_statuses = wp_parse_args( $post_statuses, wc_get_order_statuses() );
			
			if ( count( $vars [ 'post_status' ] ) !== 1 ) {
				$vars [ 'post_status' ] = array_keys( $post_statuses );
			}
		
		}
		return $vars;
	}

	/**
	 *
	 * @param WP_Query $query        	
	 */
	public function posts_clauses( $pieces, $query )
	{
		if ( $query->is_main_query() && $orderby = $query->get( 'orderby' ) ) {
			global $wpdb;
			$order = $query->get( 'order' );
			switch( $orderby ) {
				case '_start_date' :
				case '_end_date' :
				case '_next_payment_date' :
					$pieces [ 'join' ] = "LEFT JOIN $wpdb->postmeta AS postmeta ON postmeta.post_id = {$wpdb->posts}.ID AND postmeta.meta_key = '{$orderby}'";
					$pieces [ 'orderby' ] = "STR_TO_DATE (postmeta.meta_value, '%Y-%m-%d %H:%i:%s') $order";
					break;
				case '_order_total' :
					$pieces [ 'join' ] = "LEFT JOIN $wpdb->postmeta AS postmeta ON postmeta.post_id = {$wpdb->posts}.ID AND postmeta.meta_key = '{$orderby}'";
					$pieces [ 'orderby' ] = "postmeta.meta_value $order";
					break;
			}
		}
		return $pieces;
	}
}
new Braintree_Post_Types();