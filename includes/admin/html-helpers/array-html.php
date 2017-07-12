<div class="row">
	<th class="title-description array-title">
    	<?php echo __($data['title'], 'braintree-payments')?>
    	<?php echo $this->get_tooltip_html($data)?>
    	<?php echo $this->generate_helper_modal($key, $data)?>
    </th>
	<td>
    		<?php foreach($data['children'] as $k => $child){?>
    	<div class="col s6 m4 l3">
			<table>
				<tbody>
		    	<?php echo $this->{"generate_{$child['type']}_html"}($k, $child)?>
		    	</tbody>
			</table>
		</div>
		    <?php }?>
	</td>
</div>
