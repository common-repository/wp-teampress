<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressProjectHomeView' ) ) :

/**
 * View of a single project. The project to show shall be provided as a GET parameter named project_id.
 */
class TPressProjectHomeView extends TPressView {

	/** TPressPanel overrides ****************************************************************************************/
	
	// @Override
	public function getSlug() {
		return 'tpress-project-home';
	}
	
	// @Override
	public function enqueueScripts() {
		parent::enqueueScripts();
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
	public function checkAdminReferer( $post_data ) {
		if 		( isset( $post_data[ 'create_project' ] ) ) return check_admin_referer( 'tpress_project_create');
		else if ( isset( $post_data[ 'update_project' ] ) ) return check_admin_referer( 'tpress_project_update');
		else if ( isset( $post_data[ 'update_users' ] ) ) 	return check_admin_referer( 'tpress_project_manage_users');
		else if ( isset( $post_data[ 'create_task' ] ) ) 	return check_admin_referer( 'tpress_task_create');
		else if ( isset( $post_data[ 'update_task' ] ) ) 	return check_admin_referer( 'tpress_task_update');
		else return false;
	}

	// @Override
	public function onPostDataSubmitted( $post_data ) {
		global $post, $user_ID;
		
		// Handle project creation
		if ( isset( $post_data[ 'create_project' ] ) ) {
			if ( !current_user_can( "tpress_manage_projects" ) ) {
				TeamPress::addError( __( 'You are not allowed to create projects', 'tpress' ) ); 
				return;				
			}
			
			$project_id = TPressProject::create( $post_data );
			if ( $project_id > 0 ) {
				$_POST[ 'project-id' ] = $project_id;
				
				TeamPress::addMessage( __( 'The project has been created', 'tpress' ) ); 
			} else {
				TeamPress::addError( __( 'The project could not be created', 'tpress' ) ); 
				return;
			}
		} 
		// Handle project update
		else if ( isset( $post_data[ 'update_project' ] ) ) {
			if ( !TPressProject::userCan( $post, $user_ID, 'tpress_edit_project_details' ) ) {
				TeamPress::addError( __( 'You are not allowed to edit project details', 'tpress' ) ); 
				return;				
			}
			
			$project_id = TPressProject::update( $post_data[ 'project-id' ], $post_data );
			if ( $project_id > 0 ) {
				$_POST[ 'project-id' ] = $project_id;
				
				TeamPress::addMessage( __( 'The project has been updated', 'tpress' ) ); 
			} else {
				TeamPress::addError( __( 'The project could not be updated', 'tpress' ) ); 
				return;
			}
			
		} 
		// Handle user udpates
		else if ( isset( $post_data[ 'update_users' ] ) ) {
			if ( !TPressProject::userCan( $post, $user_ID, 'tpress_manage_project_users' ) ) {
				TeamPress::addError( __( 'You are not allowed to manage project users', 'tpress' ) ); 
				return;				
			}
			
			$project_id = $post_data[ 'project-id' ];
			
			$users = array();
			if ( isset(  $post_data[ 'project-leaders' ] ) ) { 
				$users[ TPressProjectsPlugin::$ROLE_PROJECT_LEADER ] 	= $post_data[ 'project-leaders' ];
			}
			if ( isset(  $post_data[ 'project-coworkers' ] ) ) { 
				$users[ TPressProjectsPlugin::$ROLE_PROJECT_COWORKER ] 	= $post_data[ 'project-coworkers' ];
			}
			if ( isset(  $post_data[ 'project-clients' ] ) ) { 
				$users[ TPressProjectsPlugin::$ROLE_PROJECT_CLIENT ] 	= $post_data[ 'project-clients' ];
			}
			
			TPressProject::setUsers( $project_id, $users );

			TeamPress::addMessage( __( 'The project participants have been updated', 'tpress' ) );
		}
		
		do_action( 'tpress_project_home_on_post_data_submitted', $post_data );
	}
	
	// @Override	
	public function render() {
		if ( isset( $_GET[ 'project-id' ] ) ) $project_id = $_GET[ 'project-id' ];
		else if ( isset( $_POST[ 'project-id' ] ) ) $project_id = $_POST[ 'project-id' ];
		else $project_id = 0;
	
		global $project_query;	
		$project_query = $project_id==0 ? null : TPressProject::findById( $project_id );
	
		parent::render();
	}
	
	
	
	/** General Methods **********************************************************************************************/
	
	
	/** Instance variables *******************************************************************************************/

}

endif; // interface_exists