<tr class="top <?php echo $key ?>">
	<?php bfwc_admin_get_template('html-helpers/option-header.php', array('data'=>$data, 'key'=>$key, 'settings'=>$this))?>
	<td>
		<?php  call_user_func_array($data['function'], array($key, $data))?>
	</td>
</tr>