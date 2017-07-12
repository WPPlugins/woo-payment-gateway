<section class="">
	<div class="container">
		<div class="row">
			<div class="input-field col s12 m12 l12">
				<h1 class="<?php echo $data['class']?> bfwc-admin-title">
					<?php echo __($data['title'], 'braintree-payments')?>
					<?php
					if ( $data[ 'helper' ][ 'enabled' ] ) :
						$page->generate_helper_modal( 'title_helper', $data );
					
					endif;
					?>
				</h1>
				<?php if(!empty($data['description'])){?>
					<p><?php echo __($data['description'], 'braintree-payments')?></p>
					<div class="row">
						<?php do_action('bfwc_settings_title_after_description', $page, $data)?>
					</div>
				<?php }?>
			</div>
		</div>
	</div>
</section>