<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressView' ) ) :

/**
 * Views that are registered in a TPressViewDispatcherPlugin should extend this class.
 */
abstract class TPressView {
	
	/**
	 * Gets the slug of that particular view
	 * @return string the slug (shall be unique amongst the views of the dispatcher)
 	 */
	abstract public function getSlug();
	
	/**
	 * Display the view. By default, we are including a file which is named as the class,
	 * located in the same folder and suffixed by ".template.php"
	 *
	 * E.g.: for a class "TPressProjectView", we will include the file named "TPressProjectView.template.php"
	 */
	public function render( $view_template_file = null ) {
		$class = get_class( $this );
		$rc = new ReflectionClass( $class );
        $class_dir = trailingslashit( dirname( $rc->getFileName() ) );
	        
        // If we are forced to include a file, do it
        // Else we try to look for the file named after the class
        // Else we try to look for the file named after the parent class
        // Else we do nothing
		if ( isset( $view_template_file ) ) {
			include( $class_dir . $view_template_file );
		} else if ( file_exists( $class_dir . $class . '.template.php' ) ) {
			include( $class_dir . $class . '.template.php' );
		} else {
			$class = get_parent_class( $this );
			$rc = new ReflectionClass( $class );
			$class_dir = trailingslashit( dirname( $rc->getFileName() ) );
			
			if ( file_exists( $class_dir . $class . '.template.php' ) ) {
				include( $class_dir . $class . '.template.php' );
			} else {
				// we'll stop looking here, include nothing
			}
		}
		
		// Common stuff for all views
		include( 'TPressView.template.php' );
	}
	
	/**
	 * When POST data is submitted, this function gives a chance to the view to check that the user really meant to do
	 * that. You should use the check_admin_referer function and in your template view, output the token using wp_none
	 *
	 * @return bool True if the token passed the validation
	 */
	public function checkAdminReferer( $post_data ) {
		return true;
	}
	
	/**
	 * Called when POST data has been submitted. 
	 *
	 * @param $post_data array The $_POST array content
	 */
	public function onPostDataSubmitted( $post_data ) {
	}
	
	/**
	 * Add the administration menus
	 */
	public function addAdminMenu( $dispatcher ) {
	}
	
	/**
	 * Enqueue the necessary scripts using wp_enqueue_script
	 */
	public function enqueueScripts() {
		do_action( 'tpress_enqueue_scripts_' . $this->getSlug() );
	}
	
	/**
	 * Enqueue the necessary styles using wp_enqueue_style
	 */
	public function enqueueStyles() {
		do_action( 'tpress_enqueue_styles_' . $this->getSlug() );
	}
}

endif; // class_exists