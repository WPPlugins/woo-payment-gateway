
<?php $icon = $data['helper']['type'] === 'img' ? 'photo' : 'videocam';?>
<a class="modal-trigger black-text" href="#<?php echo "{$field_key}_{$icon}_modal"?>">
	<i class="material-icons right"><?php echo $icon?></i>
</a>
<div class="modal" id="<?php echo "{$field_key}_{$icon}_modal"?>">
	<div class="modal-content">
		<h4 class="thin"><?php echo $data['helper']['title']?></h4>
		<div class="row">
			<div class="col s12">
				<p class="small">
					<?php echo $data['helper']['description']?>
				</p>
			</div>
		</div>
		<div class="row">
			<div class="col s12">
				<?php 
				switch($data['helper']['type']):
					case 'img':?>
						<?php if(is_array($data['helper']['url'])):?>
							<?php foreach($data['helper']['url'] as $type_data):?>
								<img class="modal-helper-img" src="<?php echo $type_data['url']?>" />
							<?php endforeach;?>
						<?php else:?>
							<img class="modal-helper-img" src="<?php echo $data['helper']['url']?>" />
						<?php endif;?>
					<?php break;
					case 'video':?>
						<?php if(is_array($data['helper']['type'])):?>
							<?php foreach($data['helper']['url'] as $type_data):?>
								<h5 class="thin"><?php echo $type_data['title']?></h5>
								<div class="video-container">
	       							<iframe width="853" height="480" src="<?php echo $type_data['url']?>" frameborder="0" allowfullscreen></iframe>
	      						</div>
							<?php endforeach;?>
						<?php else:?>
							<div class="video-container">
	       					<iframe width="853" height="480" src="<?php echo $data['helper']['url']?>" frameborder="0" allowfullscreen></iframe>
	      				</div>
						<?php endif;?>
					<?php break;
				endswitch;
				?>
			</div>
		</div>
		<?php do_action("bfwc_modal_helper_$key")?>
	</div>
	<div class="modal-footer">
		<a href="#!"
			class="modal-action bfwc-close-modal waves-effect waves-red btn-flat"><?php echo __('Close', 'stripe_gateway')?></a>
	</div>
</div>