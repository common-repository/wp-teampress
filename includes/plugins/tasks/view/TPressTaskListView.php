<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressTaskListView' ) ) :

require_once( 'TPressTaskListTable.php' );

/**
 * View presenting a list of tasks. 
 */
abstract class TPressTaskListView extends TPressView {

	/** TPressView overrides *****************************************************************************************/

	// @Override
	public function addAdminMenu( $dispatcher ) {
		add_meta_box('metabox-task-status', __( 'Status', 'tpress' ), array( &$this, 'metaBoxFilterTaskStatus' ), 'tpress-task-list', 'side', 0 );
		add_meta_box('metabox-task-assignee', __( 'Assignee', 'tpress' ), array( &$this, 'metaBoxFilterAssignee' ), 'tpress-task-list', 'side', 0 );
		add_meta_box('metabox-task-tag', __( 'Tag', 'tpress' ), array( &$this, 'metaBoxFilterTag' ), 'tpress-task-list', 'side', 0 );
	}
	
	// @Override
	public function enqueueScripts() {
		parent::enqueueScripts();
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-dialog' );
        wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'teampress', TeamPress::instance()->getIncludesUrl() . 'js/teampress.js', 
				array( 'jquery' ) );
		wp_enqueue_script( 'jquery-validate', TeamPress::instance()->getIncludesUrl() . 'js/jquery.validate.min.js',
				array( 'jquery' ) );
	}
	
	// @Override
	public function enqueueStyles() {
		parent::enqueueStyles();

		wp_dequeue_style( 'jquery-ui-css' );
        wp_enqueue_style( 'jquery-ui-css', TeamPress::instance()->getAdminThemeUrl() . 'jquery-ui-aristo.css', 
 				false, '2.5.0', 'screen' );	
	}

	// @Override
	public function onPostDataSubmitted( $post_data ) {
		global $post, $user_ID;
		
		do_action( 'tpress_tasks_on_post_data_submitted', $post_data );
	}
	
	// @Override	
	public function render() {
		if ( isset( $_GET[ 'project-id' ] ) ) $project_id = $_GET[ 'project-id' ];
		else $project_id = 0;
	
		global $task_query, $project_query;
		$project_query = $project_id==0 ? null : TPressProject::findById( $project_id );
		$task_query = $project_id==0 ? TPressTask::findAll() : TPressTask::findByProject( $project_id );
	
		parent::render();
	}
	
	/** Meta boxes ***************************************************************************************************/
	
	public function metaBoxFilterTaskStatus() {
		echo 'hello';
	}
	
	public function metaBoxFilterAssignee() {
		echo 'world';
	}
	
	public function metaBoxFilterTag() {
		echo '!';
	}
	
	/** General Methods **********************************************************************************************/
	
	
	/** Instance variables *******************************************************************************************/

}

endif; // interface_exists