<?php
/**
 * metabox for subscription options such as start date, trial period, etc.
 */
wp_nonce_field( 'bfwc-subscription', '_bfwc_subscription' );

woocommerce_wp_text_input( array (
		'label' => __( 'Trial Length', 'braintree-payments' ), 
		'id' => '_subscription_trial_length', 
		'name' => '_subscription_trial_length', 
) );
$type = $subscription->get_trial_length() > 1 ? 'plural' : 'singular';
woocommerce_wp_select( array (
		'label' => __( 'Trial Period', 'braintree-payments' ), 
		'id' => '_subscription_trial_period', 
		'name' => '_subscription_trial_period', 
		'class' => 'bfwc-admin-select2', 
		'options' => bfwc_billing_periods_string( $type ), 
) );
woocommerce_wp_select( array (
		'id' => '_subscription_length', 
		'name' => '_subscription_length', 
		'options' => bfwc_subscription_length_string(), 
		'class' => 'bfwc-admin-select2', 
		'label' => __( 'Length', 'braintree-payments' ), 
) );
?>
<div class="form-field form-field-wide">
	<h4><?php _e('Subscription Plan', 'braintree-payments' )?></h4>
	<select class="bfwc-admin-select2" id="_subscription_plan"
		name="_subscription_plan">
		<?php foreach($subscription_plans as $plan):?>
			<option value="<?php echo $plan['id']?>"
			<?php selected(bwc_get_order_property( 'braintree_plan', $subscription ), $plan['id'])?>><?php echo $plan['id']?></option>
		<?php endforeach;?>
	</select>
</div>
<div class="form-field form-field-wide">
	<h4><?php _e('Billing Interval', 'braintree-payments' )?></h4>
	<div>
		<span id="billing_frequency"></span>
	</div>
</div>