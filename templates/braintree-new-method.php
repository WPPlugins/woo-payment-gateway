<?php 
/**
 * @version 2.6.9
 */
if ( bwc_is_custom_form() ) {
	bwc_get_template( 'custom-form.php' );
} else {
	bwc_get_template( 'dropin-form.php' );
}