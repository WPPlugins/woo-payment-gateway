<?php

abstract class Braintree_Gateway_Page_API
{
	
	public $page = '';
	
	/**
	 * Id of the settings class extending the settings api.
	 *
	 * @var string
	 */
	public $id = '';
	
	public $tab = '';
	
	public $title = array ();

	public function __construct()
	{
		if ( $this->tab ) {
			add_action( "braintree_gateway_{$this->tab}_title", array (
					$this, 
					'generate_title_html' 
			) );
		}
	}

	public function generate_title_html( $data = null )
	{
		if ( $data == null ) {
			$data = $this->title;
		}
		$data = wp_parse_args( $data, array (
				'title' => '', 
				'description' => '', 
				'class' => 'thin', 
				'helper' => array (
						'enabled' => false, 
						'type' => '', 
						'url' => '', 
						'description' => '' 
				) 
		) );
		ob_start();
		bfwc_admin_get_template( 'html-helpers/title-html.php', array (
				'data' => $data, 
				'page' => $this 
		) );
		echo ob_get_clean();
	}
}