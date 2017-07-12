<script type="text/template">
	<td>
		<input type="text" name="<?php echo $key?>[<%=index%>][name]" value="<%=name%>"/>
	</td>
	<td>
		<input type="text" name="<?php echo $key?>[<%=index%>][calculation]" value="<%=calculation%>"/>
	</td>
	<td>
		<select name="<?php echo $key?>[<%=index%>][tax_status]>">
			<option value="taxable" <%= tax_status === "taxable" ? 'selected' : void 0 %> ><?php _e('Taxable', 'braintree-payments')?></option>
			<option  value="none" <%= tax_status === "none" ? 'selected' : void 0 %>><?php _e('None', 'braintree-payments')?></option>
		</select>
	</td>
	<td>
		<select multiple class="bfwc-backbone-selec2" name="<?php echo $key?>[<%=index%>][gateways][]">
			<?php foreach($gateways as $gateway):?>
				<option value="<?php echo $gateway['id']?>"><?php echo $gateway['title']?></option>
			<?php endforeach;?>
		</select>
		<i class="material-icons bfwc-delete-row">delete</i>
	</td>
</script>