<div class="row">
	<div class="input-field col s12 m12 l12">
		<a href="#<?php echo $field_key ?>_modal"
			class="modal-trigger medium-text <?php echo $data['class']?>"
			<?php braintree_get_html_field_attributes($data['attributes'], true)?>><?php echo$this->title['title']?></a>
	</div>
</div>
<div class="modal modal-<?php echo $field_key?>" id="<?php echo $field_key ?>_modal">
	<div class="modal-content">
		<h4 class="thin"><?php echo $this->title['title']?></h4>
		<?php if($this->title['description']){?>
			<p><?php echo $this->title['description']?></p>
		<?php }?>
		<table class="table-<?php echo $field_key?>">
			<tbody>
			<?php $this->generate_settings_html(true)?>
			</tbody>
		</table>
		<?php if(isset($data['extra_html'])):?>
			<?php echo $data['extra_html']?>
		<?php endif;?>
	</div>
	<div class="modal-footer">
		<a href="#!"
			class="modal-action modal-close waves-effect waves-green btn-flat"><?php echo __('Close', 'stripe_gateway')?></a>
	</div>
</div>
<?php
