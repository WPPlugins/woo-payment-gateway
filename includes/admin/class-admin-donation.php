<?php
class Braintree_Gateway_Admin_Donation_Page extends Braintree_Gateway_Page_API
{

	public function __construct()
	{
		$this->page = 'braintree-donations-page';
		parent::__construct();
	}

	public static function output()
	{
		bfwc_admin_get_template( 'views/donation-page.php' );
	}
}
new Braintree_Gateway_Admin_Donation_Page();