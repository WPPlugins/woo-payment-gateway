<?php 
$values = $this->get_option($key);
if(!$values){
	$values = array();
}
?>
<tr class="top">
	<?php bfwc_admin_get_template('html-helpers/option-header.php', array('data'=>$data, 'key'=>$key, 'settings'=>$this))?>
	<td>
		<div class="row">
			<div class="input-field col s12 m8 l8">
				<select multiple id="<?php echo $field_key ?>" name="<?php echo "{$field_key}[]" ?>"
					class="<?php echo $data['class']?> settings-chip-options bfwc-multiselect" style="width: 100%">
                <?php foreach($data['options'] as $option => $value):?>
                        <option value="<?php echo $option?>" <?php selected(in_array($option, $values), true)?>><?php echo $value?></option>
                <?php endforeach;?>
        		</select>
			</div>
		</div>
	</td>
</tr>