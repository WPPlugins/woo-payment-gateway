<tr>
	<?php bfwc_admin_get_template('html-helpers/option-header.php', array('data'=>$data, 'key'=>$key, 'settings'=>$this))?>
	<td>
		<div class="row">
			<div class="col s12 m12 l12">
				<label for="<?php echo $field_key?>"><?php echo $data['title']?></label>
				<textarea class="materialize-textarea"
					name="<?php echo $field_key?>" id="<?php echo $field_key?>"
					class="<?php $data['class']?>"
					<?php braintree_get_html_field_attributes($data['attributes'], true)?>><?php echo $this->get_option($key)?></textarea>
			</div>
		</div>
	</td>
</tr>