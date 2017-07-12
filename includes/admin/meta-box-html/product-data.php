<div class="bfwc-subscription-options">
	<div class="options_group show_if_subscription">
	<?php
	woocommerce_wp_checkbox( array ( 
			'label' => __( 'Sell As Braintree Subscription', 'braintree-payments' ), 
			'name' => '_braintree_subscription', 
			'id' => '_braintree_subscription', 
			'cbvalue' => 'yes', 
			'desc_tip' => true, 
			'description' => __( 'If enabled, your monthly subscription will be managed by Braintree instead of WooCommerce Subscriptions. Having WC subscriptions manage your subscription is preferred. If enabled, you will need to select all of the Braintree plans that are to be associated with this subscription product.', 'braintree-payments' ) 
	) );
	?>
	</div>
	<div class="show_if_braintree_subscription_checked" style="display:none">
		<div class="options_group show_if_subscription sandbox">
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
		
		<div class="options_group production show_if_subscription production_data">
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
			
			esc_html( _e( 'Your are using the Free version. In order to add production Braintree Plans to a subscription product you must have an active license. 
					You can purchase a license <a target="_blank" href="https://wordpress.paymentplugins.com/product-category/braintree-plugins/">Here</a>', 'braintree-payments' ) )?></p>
			<?php
		}
		$production_product_plans = get_post_meta( $post->ID, '_braintree_production_plans', true );
		$has_prod_plans = ( bool ) $production_product_plans;
		?>
		</div>
	</div>
</div>