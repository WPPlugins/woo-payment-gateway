<tr class="top">
	<?php bfwc_admin_get_template('html-helpers/option-header.php', array('data'=>$data, 'key'=>$key, 'settings'=>$this))?>
	<td>
		<div class="row">
			<div class="input-field col s12 m12 l12">
				<select name="<?php echo $field_key?>" id="<?php echo $field_key?>"
					class="bfwc-select2 <?php echo $data['class']?>" style="width: 100%"
					<?php braintree_get_html_field_attributes($data['attributes'], true)?>>
                        			<?php foreach($data['options'] as $option => $value){?>
                        				<option value="<?php echo $option?>"
						<?php selected($option, $this->get_option($key), true)?>><?php echo $value?></option>
                        			<?php }?>
                        		</select>
			</div>
		</div>
		<?php echo $this->display_link($key, $data);?>
	</td>
</tr>