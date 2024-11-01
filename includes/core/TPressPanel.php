<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressPanel' ) ) :

/**
 * Panels are simply components to be rendered within a view
 */
abstract class TPressPanel {
	
	/**
	 * Gets the slug of that particular panel
	 * @return string the slug (shall be unique amongst the views of the dispatcher)
 	 */
	abstract public function getSlug();
	
	/**
	 * Display the panel. By default, we are including a file which is named as the class,
	 * located in the same folder and suffixed by ".template.php"
	 *
	 * E.g.: for a class "TPressProjectPanel", we will include the file named "TPressProjectPanel.template.php"
	 */
	public function render() {
		$class = get_class( $this );
		$rc = new ReflectionClass( $class );
        $class_dir = trailingslashit( dirname( $rc->getFileName() ) );
		
		include( $class_dir . $class . '.template.php' );
	}
	
	/**
	 * Called when POST data has been submitted. 
	 *
	 * @param $post_data array The $_POST array content
	 */
	public function onPostDataSubmitted( $post_data ) {
	}
}

endif; // class_exists