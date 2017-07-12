<script type="text/template">
<td>
	<input type="text" name="<?php echo $key?>[<%=index%>][merchant_account]" value="<%=merchant_account%>"/>
</td>
<td>
	<select class="bfwc-backbone-selec2" name="<?php echo $key?>[<%=index%>][currency]"">
			<?php foreach($currencies as $code => $currency):?>
				<option value="<?php echo $code?>"><?php echo $code?></option>
			<?php endforeach;?>
	</select>
<i class="material-icons bfwc-delete-row">delete</i>
</td>
</script>
