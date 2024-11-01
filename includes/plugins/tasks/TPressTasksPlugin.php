<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressTasksPlugin' ) ) :

require_once( 'model/TPressTask.php' );
require_once( 'view/template_functions.php' );
require_once( 'view/TPressProjectTasksView.php' );

/** TeamPress plugin registration ************************************************************************************/
TeamPress::registerPlugin( 'TPressTasksPlugin' );

/**
 * Defines the core object of TeamPress: tasks
 */
class TPressTasksPlugin extends TPressPluginBase {
	
	/** TPressPluginBase overrides ***********************************************************************************/
	
	// @Override
	public function onPluginRegistered($tpress) {
		parent::onPluginRegistered($tpress);
			
		add_action( 'init', array( 'TPressTask', 'registerPostType' ) );

		add_action( 'tpress_admin_interface_register_views', array( &$this, 'registerAdminInterfaceViews' ) );
		add_filter( 'tpress_dashboard_right_now_statistics', array( &$this, 'appendRightNowStatistics' ), 100 );
		
		add_filter( 'tpress_projects_leaders_capabilities', array( &$this, 'addProjectLeadersCapabilities' ) );
		add_filter( 'tpress_projects_coworkers_capabilities', array( &$this, 'addProjectCoWorkersCapabilities' ) );
		add_filter( 'tpress_projects_clients_capabilities', array( &$this, 'addProjectClientsCapabilities' ) );
		
		add_action( 'tpress_project_delete', array( &$this, 'onProjectDeleted' ) );
		add_action( 'tpress_end_project_blocks', array( &$this, 'addProjectHomeBlocks' ) );
		add_action( 'tpress_project_home_on_post_data_submitted', array( &$this, 'onProjectHomePostDataSubmitted' ) );
		add_filter( 'tpress_project_statistics', array( &$this, 'appendProjectStatistics' ), 10, 2 );
	}
	
	// @Override
	public function getDisplayName() {
		return __( 'Tasks Core Plugin', 'tpress' );
	}
	
	/** Url builders *************************************************************************************************/
		
	/**
	 * Get a link to show the tasks the current user has access to
	 */
	public function getMyTasksLink() {
		return TPressAdminInterfacePlugin::instance()->getViewUrl( 'tpress-my-tasks' );
	}
		
	/**
	 * Get a link to show a task 
	 */
	public function getTaskHomeLink( $task_id ) {
		return TPressAdminInterfacePlugin::instance()->getViewUrl( 'tpress-task_home&task_id=' . $task_id );
	}
		
	/**
	 * Get a link to show the tasks related to a project 
	 */
	public function getProjectTasksLink( $project_id ) {
		return TPressAdminInterfacePlugin::instance()->getViewUrl( 'tpress-project-tasks&project-id=' . $project_id );
	}

	/** General Methods **********************************************************************************************/
	
	/**
	 * Register the project views in the admin interface
	 *
	 * @param $dispatcher TPressAdminInterfacePlugin The admin interface dispatcher
	 *
	 * @see
	 * @todo Output a link to show projects properly
	 */
	public function registerAdminInterfaceViews( $dispatcher ) {
		$dispatcher->registerView( new TPressProjectTasksView() );
	}
		
	/**
	 * Output some statistics in the "Right Now in TeamPress" widget
	 *
	 * @param $statistics array The statistics we got to fill
	 *
	 * @see
	 * @todo Output a link to show projects properly
	 */
	public function appendRightNowStatistics( $statistics ) {
		if ( !isset( $statistics[ 'tpress_projects' ] ) ) {
			$statistics[ 'tpress_projects' ] = array();
			$statistics[ 'tpress_projects' ][ 'display_name' ] = __( 'Projects', 'tpress' );
			$statistics[ 'tpress_projects' ][ 'data' ] = array();
		}
		
		$count = TPressTask::countTasks();
		
		$statistics[ 'tpress_projects' ][ 'data' ][] = array(
				'number' => $count, 
				'text' => _n( 'Task', 'Tasks', $count, 'tpress' ),
				'link' => $this->getMyTasksLink() 
			);
		
		return $statistics;
	}
	
	public function addProjectLeadersCapabilities( $capabilities ) {
		return array_merge( $capabilities, array(	
				'tpress_manage_tasks', 		
				'tpress_update_task',			
				'tpress_create_task',			
				'tpress_mark_task_completed',
			));		
	}
	
	public function addProjectCoWorkersCapabilities( $capabilities ) {
		return array_merge( $capabilities, array(
				'tpress_update_task',			
				'tpress_create_task',			
				'tpress_mark_task_completed',
			));		
	}
	
	public function addProjectClientsCapabilities( $capabilities ) {
		return array_merge( $capabilities, array(	
			));		
	}

	/** Model update callbacks ***************************************************************************************/
	
	public function onProjectDeleted( $project_id ) {
		$tasks = TPressTask::findByProject( $project_id );
		$tasks = $tasks->get_posts();
		foreach ( $tasks as $t ) {
			TPressTask::delete( $t->ID );
		}
	}

	/** Pluging UI on the Dashboard **********************************************************************************/
	
	/** Pluging UI on a project home *********************************************************************************/
	
	/**
	 * Handle form submittion on the project home page (handles task creation)
	 * 
	 * @param array $post_data the $_POST data
	 */
	public function onProjectHomePostDataSubmitted( $post_data ) {
		global $post, $user_ID;
		
		// Handle task creation
		if ( isset( $post_data[ 'create_task' ] ) ) {
			if ( !isset( $post_data[ 'project' ] ) ) {
				TeamPress::addError( __( 'A task can only be created within a project', 'tpress' ) ); 
				return;
			}
			else if ( !TPressProject::userCan( $post_data[ 'project' ], $user_ID, 'tpress_create_task' ) ) {
				TeamPress::addError( __( 'You are not allowed to create tasks in this project', 'tpress' ) ); 
				return;
			}
			
			// Convert date to the proper format
			$post_data[ 'due_date' ] = WordPressHelper::convertDateToMysqlFormat( $post_data[ 'due_date' ], _x( 'd/m/Y', 'Date Picker format (PHP)', 'tpress' ) );
				
			$task_id = TPressTask::create( $post_data );
			if ( $task_id > 0 ) {
				$_POST[ 'project-id' ] = $post_data[ 'project' ];
				
				TeamPress::addMessage( __( 'The task has been created', 'tpress' ) ); 
			} else {
				TeamPress::addError( __( 'The task could not be created', 'tpress' ) ); 
			}
		}
		// Handle task update
		else if ( isset( $post_data[ 'update_task' ] ) ) {
			if ( !isset( $post_data[ 'project' ] ) ) {
				TeamPress::addError( __( 'A task can only be updated within a project', 'tpress' ) ); 
				return;
			}
			else if ( !TPressProject::userCan( $post_data[ 'project' ], $user_ID, 'tpress_udpate_task' ) ) {
				TeamPress::addError( __( 'You are not allowed to udpate tasks in this project', 'tpress' ) ); 
				return;
			}

			// Convert date to the proper format
			$post_data[ 'due_date' ] = WordPressHelper::convertDateToMysqlFormat( $post_data[ 'due_date' ], _x( 'd/m/Y', 'Date Picker format (PHP)', 'tpress' ) );
				
			$task_id = TPressTask::update( $post_data[ 'task-id' ], $post_data );
			if ( $task_id > 0 ) {
				$_POST[ 'project-id' ] = $post_data[ 'project' ];
				
				TeamPress::addMessage( __( 'The task has been updated', 'tpress' ) ); 
			} else {
				TeamPress::addError( __( 'The task could not be updated', 'tpress' ) ); 
			}
		}
	}
	
	/**
	 * Add a block on the project home page to show the tasks needing attention
	 * 
	 * @param TPressProject $project
	 */
	public function addProjectHomeBlocks( $project ) {
		the_project_tasks_block( $project );
	
		include( 'view/TPressTaskDetailsDialog.template.php' );
	}
	
	/**
	 * Output some statistics for a particular project
	 *
	 * @param $statistics array The statistics we got to fill
	 * @param $project WP_Post The project for which we need statistics
	 *
	 */
	public function appendProjectStatistics( $statistics, $project ) {
		// Compute statistics about the users
		if ( !isset( $statistics[ 'tpress_tasks' ] ) ) {
			$statistics[ 'tpress_tasks' ] = array();
			$statistics[ 'tpress_tasks' ][ 'display_name' ] = __( 'Tasks', 'tpress' );
			$statistics[ 'tpress_tasks' ][ 'data' ] = array();
		}
		
		$count = TPressTask::countProjectTasks( $project->ID );
		$statistics[ 'tpress_tasks' ][ 'data' ][] = array(
				'number' => $count, 
				'text' => _n( 'Task', 'Tasks', $count, 'tpress' ),
				'link' => ''
			);
		
		$count = TPressTask::countProjectOpenedTasks( $project->ID );
		$statistics[ 'tpress_tasks' ][ 'data' ][] = array(
				'number' => $count, 
				'text' => _n( 'Still Open', 'Still Opened', $count, 'tpress' ),
				'link' => ''
			);
		
		return $statistics;
	}
	
	/** Singleton ****************************************************************************************************/

	/** Returns the one and only instance of the plugin */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new TPressTasksPlugin();
		}
		return self::$instance;
	}

	/** A dummy constructor to prevent TeamPress from being loaded more than once. */
	protected function __construct() { /* Do nothing here */ }

	/** A dummy magic method to prevent TeamPress from being cloned */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'tpress' ), '3.2' ); }

	/** A dummy magic method to prevent TeamPress from being unserialized */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'tpress' ), '3.2' ); }

	private static $instance;
	
	/** Instance variables *******************************************************************************************/
	
}

endif; // class_exists