<tr>
	<?php bfwc_admin_get_template('html-helpers/option-header.php', array('data'=>$data, 'key'=>$key, 'settings'=>$this))?>
	<td>
		<input type="radio" class="<?php echo $data['class']?>"
			name="<?php echo $field_key?>" id="<?php echo $field_key?>"
			<?php checked($data['value'], $this->get_option($key))?>
			<?php braintree_get_html_field_attributes($data['attributes'], true)?> />
	</td>
</tr>