<?php
/**
 * @version 2.6.9
 */
?>
<div class="field-container">
	<div>
		<label><?php echo $data['label']?></label>
	</div>
	<?php
	$attributes = array ();
	foreach ( $data [ 'attributes' ] as $k => $v ) {
		$attributes [] = $k . '="' . $v . '"';
	}
	?>
	<input type="text" name="<?php echo $key?>" id="<?php echo $key?>"
		class="<?php echo $data['class']?>"
		placeholder="<?php echo $data['placeholder']?>"
		<?php echo implode(' ', $attributes)?> value="<?php echo $data['value']?>">
</div>
