<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressProjectAjaxController' ) ) :

require_once( 'model/TPressProject.php' );

class TPressProjectAjaxController extends TPressAjaxController {

	public function __construct() {
		$this->registerCallback( 'tpress_create_project', array( &$this, 'createProject' ) );
		$this->registerCallback( 'tpress_delete_project', array( &$this, 'deleteProject' ) );
	}

	/**
	 * Delete a project. The current user must have the 'tpress_manage_projects' capability.
	 * 
	 * Parameters:
	 * - (*) project_id
	 * - (*) nonce
	 */
	public function deleteProject() {
		if ( !current_user_can( 'tpress_manage_projects' ) ) {
			echo TPressAjaxResult::getFailureResult( __( 'You are not allowed to create projects', 'tpress' ) );
			die;
		}
		
		if ( !wp_verify_nonce( $_REQUEST[ 'nonce' ], 'tpress_delete_project') ) {
			echo TPressAjaxResult::getFailureResult( __( 'Form security error', 'tpress' ) );
			die;
		}
		
		if ( !isset( $_REQUEST[ 'project_id' ] ) ) {
			echo TPressAjaxResult::getFailureResult( __( 'The project ID must be provided', 'tpress' ) );
			die;
		}

		TPressProject::delete( $_REQUEST[ 'project_id' ] );		
		
		echo TPressAjaxResult::getSuccessResult( array(
					'redirect' => TPressAdminInterfacePlugin::instance()->getDashboardUrl()
				));
		die;
	}

	/**
	 * Create a project. The current user must have the 'tpress_manage_projects' capability.
	 * 
	 * Parameters:
	 * - (*) project_name
	 * - ( ) project_description
	 */
	public function createProject() {
		if ( !current_user_can( 'tpress_manage_projects' ) ) {
			echo TPressAjaxResult::getFailureResult( __( 'You are not allowed to create projects', 'tpress' ) );
			die;
		}
		
		if ( !check_admin_referer( 'tpress_new_project') ) {
			echo TPressAjaxResult::getFailureResult( __( 'Form security error', 'tpress' ) );
			die;
		}
		
		if ( !isset( $_REQUEST[ 'project_name' ] ) ) {
			echo TPressAjaxResult::getFailureResult( __( 'The project title must be provided', 'tpress' ) );
			die;
		}
		
		$pp = TPressProjectsPlugin::instance();

		$project_id = TPressProject::create( $_REQUEST );		
		if ( $project_id < 0 ) {
			echo TPressAjaxResult::getFailureResult( __( 'The project could not be created', 'tpress' ) );
			die;
		}
		
		echo TPressAjaxResult::getSuccessResult( array(
					'project_id' => $project_id,
					'redirect' => $pp->getProjectHomeLink( $project_id )
				));
		die;
	}
	
	/** Instance variables *******************************************************************************************/
	
}

endif; // class_exists