<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressAjaxController' ) ) :

require_once( 'TPressAjaxResult.php' );

class TPressAjaxController {

	public function __construct() {
	}

	protected function registerCallback( $name, $callback ) {
		add_action('wp_ajax_' . $name, $callback );
	}
		
	/** Instance variables *******************************************************************************************/
	
}

endif; // class_exists