<?php 
/**
 * @version 2.6.9
 */
if ( bwc_is_custom_form() ) {
	bwc_get_template( 'myaccount/custom-form.php' );
} else {
	bwc_get_template( 'myaccount/dropin-form.php' );
}