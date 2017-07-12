<tr>
	<?php bfwc_admin_get_template('html-helpers/option-header.php', array('data'=>$data, 'key'=>$key, 'settings'=>$this))?>
	<td>
		<div class="row">
			<div class="input-field col s12 m12 l12">
				<p>
					<input type="checkbox" name="<?php echo $field_key?>"
						id="<?php echo $field_key?>" class="<?php echo $data['class']?>"
						value="<?php echo $data['value']?>"
						<?php checked($data['value'], $this->get_option($key))?>
						<?php braintree_get_html_field_attributes($data['attributes'], true)?>>
					<label for="<?php echo $field_key?>"><?php echo $data['title']?></label>
				</p>
			</div>
		</div>
	</td>
</tr>