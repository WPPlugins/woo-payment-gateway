<main class="donation-page">
<div class="container">
	<section>
		<div class="row">
			<div class="col s12">
				<h1 class="thin"><?php _e('Donations', 'braintree-payments' )?></h1>
			</div>
		</div>
	</section>
</div>
<div class="container">
	<div class="row">
		<div class="col s12 m6 l4">
			<a
				href="<?php echo admin_url() . 'edit.php?post_type=braintree_donation'?>">
				<div class="card-panel white hoverable">
					<div class="center promo">
						<i class="material-icons">attach_money</i>
						<p class="promo-caption black-text">
						<?php _e('Donations', 'braintree-payments' )?>
					</p>
						<span class="black-text"><?php _e('Manage your donations. You can refund, change the status or capture authorized transactions.', 'braintree-payments' )?></span>
					</div>
				</div>
			</a>
		</div>
		<div class="col s12 m6 l4 offset-l2">
			<a
				href="<?php echo admin_url() . 'edit.php?post_type=bt_rc_donation'?>">
				<div class="card-panel white hoverable">
					<div class="center promo">
						<i class="material-icons">attach_money</i>
						<i class="material-icons">repeat</i>
						<p class="promo-caption black-text">
        						<?php _e('Recurring Donations', 'braintree-payments' )?>
    					</p>
						<span class="black-text"><?php _e('Manage your recurring donations. You can refund, change the status or capture authorized transactions.', 'braintree-payments' )?></span>
					</div>
				</div>
			</a>
		</div>
	</div>
</div>
</main>