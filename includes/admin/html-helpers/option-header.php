<th class="title-description">
		<?php echo __($data['title'], 'braintree-payments')?>
		<?php echo $settings->get_tooltip_html($data)?>
        <?php echo $settings->generate_helper_modal($key, $data);?>
</th>