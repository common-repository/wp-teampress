<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressProjectTasksView' ) ) :

require_once( 'TPressTaskListView.php' );

/**
 * View presenting a list of tasks corresponding to a project.
 */
class TPressProjectTasksView extends TPressTaskListView {

	/** TPressView overrides *****************************************************************************************/

	// @Override
	public function getSlug() {
		return 'tpress-project-tasks';
	}
	
	/** General Methods **********************************************************************************************/
	
	
	/** Instance variables *******************************************************************************************/

}

endif; // interface_exists