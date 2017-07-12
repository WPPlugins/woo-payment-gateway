<tr>
	<?php bfwc_admin_get_template('html-helpers/option-header.php', array('data'=>$data, 'key'=>$key, 'settings'=>$this))?>
	<td>
		<div class="row">
			<div class="input-field col s12 m12 l12">
				<button class="<?php echo $data['class']?>"
					name="<?php echo $field_key?>" id="<?php echo $field_key?>"
					value="<?php echo $data['value']?>"
					<?php braintree_get_html_field_attributes($data['attributes'], true)?>>
					<?php if($data['pre_loader']){
						include 'pre-loader.php';
					}?>
					<?php echo $data['label']?>
				</button>
			</div>
		</div>
	</td>
</tr>