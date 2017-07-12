<div
	class="options_group braintree_subscription_pricing show_if_braintree-subscription">
<?php
woocommerce_wp_text_input( array ( 
		'label' => sprintf( __( 'Subscription Price (%s)', 'braintree-payments' ), get_woocommerce_currency_symbol( get_woocommerce_currency() ) ), 
		'placeholder' => '0.00', 
		'id' => '_subscription_price', 
		'name' => '_subscription_price', 
		'desc_tip' => true, 
		'description' => __( 'The price that is billed for the subscription on the period and interval that you assign.', 'braintree-payments' ) 
) );
woocommerce_wp_select( array ( 
		'id' => '_subscription_period_interval', 
		'name' => '_subscription_period_interval', 
		'label' => 'Billing Interval', 
		'options' => bfwcs_billing_interval_string(), 
		'desc_tip' => true, 
		'description' => __( 'Braintree only allows monthly subscriptions. The frequency can be customized by changing the Braintree plan that is assigned.', 'braintree-payments' ) 
) );
woocommerce_wp_select( array ( 
		'id' => '_subscription_length', 
		'name' => '_subscription_length', 
		'options' => bfwc_subscription_length_string(), 
		'label' => __( 'Length', 'braintree-payments' ), 
		'desc_tip' => true, 
		'description' => __( 'The duration in which the subscription will be active.', 'braintree-payments' ) 
) );
woocommerce_wp_text_input( array ( 
		'label' => __( 'Sign Up Fee', 'braintree-payments' ), 
		'id' => '_subscription_sign_up_fee', 
		'placeholder' => '0.00', 
		'name' => '_subscription_sign_up_fee', 
		'desc_tip' => true, 
		'description' => __( 'If you would like the subscription to have a one time sign up fee, you can add it here.', 'braintree-payments' ) 
) );
woocommerce_wp_text_input( array ( 
		'label' => __( 'Trial Length', 'braintree-payments' ), 
		'id' => '_subscription_trial_length', 
		'name' => '_subscription_trial_length', 
		'desc_tip' => true, 
		'description' => __( 'The length of the trial associated with the subscription.', 'braintree-payments' ) 
) );

$type = get_post_meta( $post->ID, '_subscription_trial_length', true ) > 1 ? 'plural' : 'singular';
woocommerce_wp_select( array ( 
		'label' => __( 'Trial Period', 'braintree-payments' ), 
		'id' => '_subscription_trial_period', 
		'name' => '_subscription_trial_period', 
		'options' => bfwc_billing_periods_string( $type ), 
		'desc_tip' => true ,
		'description' => __('The period in which the trial length is associated with. Braintree accepts days and months as trial periods.', 'braintree-payments' )
) );
?>
	<div class="options_group sandbox">
		<p class="form-field">
			<label>
				<strong><?php _e('Sandbox Data', 'braintree-payments' )?></strong>
			</label>
			<a href="#" data-environment="sandbox" type="submit"
				class="button bt-add-plan"><?php _e('Add Sandbox Plan', 'braintree-payments' )?></a>
		</p>
	<?php
	$plans = array ();
	foreach ( $sandbox_plans as $id => $plan ) {
		$plans[ $id ] = sprintf( '%s (%s)', $plan[ 'name' ], $plan[ 'currencyIsoCode' ] );
	}
	
	woocommerce_wp_select( array ( 
			'label' => __( 'Sandbox Braintree Plans', 'braintree-payments' ), 
			'id' => '_sandbox_braintree_plans', 
			'name' => '_sandbox_braintree_plans', 
			'class' => 'braintree-plans', 
			'options' => $plans, 
			'desc_tip' => true, 
			'description' => __( 'You must assign a Braintree Plan for each currency that you plan on accepting for this subscription.', 'braintree-payments' ) 
	) );
	
	$sandbox_product_plans = get_post_meta( $post->ID, '_braintree_sandbox_plans', true );
	$has_sand_plans = ( bool ) $sandbox_product_plans;
	?>
		<div class="plan-container">
			<ul class="ul-choices sandbox">
			<?php
			
			if ( $has_sand_plans ) {
				foreach ( $sandbox_product_plans as $currency => $id ) {
					$plan = $sandbox_plans[ $id ];
					?>
					<li class="product-plan">
					<a href="#" class="select2"></a>
					<span><?php printf('%s ( %s )', $plan['name'], $currency)?></span>
					<input type="hidden"
						name="<?php echo "_braintree_sandbox_plans[{$currency}]" ?>" id=""
						value="<?php echo $id?>">
				</li>
					<?php
				}
			}
			?>
			</ul>
		</div>
	</div>

	<?php $account_active = false;?>
	<div class="options_group production">
		<p class="form-field">
			<label>
				<strong><?php _e('Production Data', 'braintree-payments' )?></strong>
			</label>
			<a href="#" data-environment="production" type="submit"
				class="button bt-add-plan <?php if(!$account_active){echo 'disabled';}?>"><?php _e('Add Production Plan', 'braintree-payments' )?></a>
		</p>
	<?php
	$plans = array ();
	
	if ( ! $account_active ) {
		?>
		<p><?php
		
		esc_html( _e( 'Your license is not active. In order to add production Braintree Plans to a subscription product you must have an active license. 
				You can purchase a license <a target="_blank" href="https://wordpress.paymentplugins.com/product-category/braintree-plugins/">Here</a>', 'braintree-payments' ) )?></p>
		<?php
	}
	?>
	</div>
</div>