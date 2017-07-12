<tr>
	<?php bfwc_admin_get_template('html-helpers/option-header.php', array('data'=>$data, 'key'=>$key, 'settings'=>$this))?>
	<td>
		<div class="row">
			<div class="input-field col s12 m12 l12">
				<label for="<?php echo $field_key?>"><?php echo $data['title']?></label>
				<input type="text" class="<?php echo $data['class']?>"
					name="<?php echo $field_key?>" id="<?php echo $field_key?>"
					value="<?php echo apply_filters('bfwc_admin_option_' . $key, $this->get_option($key), $key)?>"
					maxlength="<?php echo $data['maxlength']?>"
					<?php braintree_get_html_field_attributes($data['attributes'], true)?>>
			</div>
		</div>
		<?php echo $this->display_link($key, $data);?>
	</td>
</tr>