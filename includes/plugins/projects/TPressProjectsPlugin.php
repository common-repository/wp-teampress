<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressProjectsPlugin' ) ) :

require_once( 'model/TPressProject.php' );
require_once( 'view/TPressProjectCardBoardPanel.php' );
require_once( 'view/TPressProjectHomeView.php' );
require_once( 'view/template_functions.php' );
require_once( 'TPressProjectsAjaxController.php' );

/** TeamPress plugin registration ************************************************************************************/
TeamPress::registerPlugin( 'TPressProjectsPlugin' );

/**
 * Defines the core object of TeamPress: projects
 */
class TPressProjectsPlugin extends TPressUserGroupBasedPlugin {

	public static $POST_TYPE = 'tpress_project';
	
	public static $ROLE_PROJECT_LEADER 			= 'tpress_group_project_leader';
	public static $ROLE_PROJECT_COWORKER		= 'tpress_group_project_coworker';
	public static $ROLE_PROJECT_CLIENT 			= 'tpress_group_project_user';
	
	/** TPressUserGroupBasedPlugin overrides *************************************************************************/
	
	// @Override
	public function onPluginRegistered($tpress) {
		parent::onPluginRegistered($tpress);
			
		add_action( 'init', array( 'TPressProject', 'registerPostType' ) );
		add_action( 'tpress_admin_interface_register_views', array( &$this, 'registerAdminInterfaceViews' ) );
		add_filter( 'tpress_dashboard_right_now_statistics', array( &$this, 'appendRightNowStatistics' ), 10 );
		add_filter( 'tpress_project_statistics', array( &$this, 'appendProjectStatistics' ), 10, 2 );
		add_filter( 'tpress_dashboard_main_links', array( &$this, 'appendMainDashboardLinks' ) );
		add_action( 'tpress_dashboard_register_panels', array( &$this, 'registerDashboardPanels' ) );
 		add_action( 'tpress_enqueue_scripts_tpress-dashboard', array( &$this, 'enqueueDashboardScripts' ) );
 		add_action( 'tpress_enqueue_styles_tpress-dashboard', array( &$this, 'enqueueDashboardStyles' ) );
		add_action( 'tpress_dashboard_after_panels', array( &$this, 'includeNewProjectDialogHtml' ) );
		
		$this->ajaxController = new TPressProjectAjaxController();
	}
	
	// @Override
	public function getDisplayName() {
		return __( 'Projects Core Plugin', 'tpress' );
	}
	
	// @Override
	public function getAllCapabilities() {
		return apply_filters( 'tpress_projects_all_capabilities', array(
				'tpress_manage_projects',		// create / delete projects
				'tpress_view_all_projects',
			) );
	}
	
	// @Override
	public function getUserGroupsCapabilities() {
		$this->getDefaultGroupCapabilities();
		
		return array_merge(
				$this->default_group_capabilities[ self::$ROLE_PROJECT_LEADER ],
				$this->default_group_capabilities[ self::$ROLE_PROJECT_COWORKER ],
				$this->default_group_capabilities[ self::$ROLE_PROJECT_CLIENT ]
			);
	}
	
	// @Override
	protected function defineUserGroups() {
		$this->getDefaultGroupCapabilities();
		
		$this->addUserGroup(
				self::$ROLE_PROJECT_LEADER,
				__( 'Project Leader', 'tpress' ),
				$this->default_group_capabilities[ self::$ROLE_PROJECT_LEADER ]
			);
			
		$this->addUserGroup(
				self::$ROLE_PROJECT_COWORKER,
				__( 'Project Co-worker', 'tpress' ),
				$this->default_group_capabilities[ self::$ROLE_PROJECT_COWORKER ]
			);
			
		$this->addUserGroup(
				self::$ROLE_PROJECT_CLIENT,
				__( 'Project Client', 'tpress' ),
				$this->default_group_capabilities[ self::$ROLE_PROJECT_CLIENT ]
			);
	}
	
	/**
	 * Get the group capabilities and make sure we have cached this array
	 *
	 * @return array An array of capability arrays, indexed by user group id
	 */
	private function getDefaultGroupCapabilities() {
		if ( !isset( $this->default_group_capabilities ) ) {
			$this->default_group_capabilities = array();
			$this->default_group_capabilities[ self::$ROLE_PROJECT_LEADER ] = apply_filters( 
					'tpress_projects_leaders_capabilities', 
					array( 	'tpress_manage_project_users',
							'tpress_edit_project_details'
						) );
			$this->default_group_capabilities[ self::$ROLE_PROJECT_COWORKER ] = apply_filters( 
					'tpress_projects_coworkers_capabilities', 
					array( 	
						) );
			$this->default_group_capabilities[ self::$ROLE_PROJECT_CLIENT ] = apply_filters( 
					'tpress_projects_clients_capabilities', 
					array( 
						) );
		}
		
		return $this->default_group_capabilities;
	}
	
	/** Url builders *************************************************************************************************/
		
	/**
	 * Get a link to show the projects the current user has access to
	 */
	public function getMyProjectsLink() {
		return TPressAdminInterfacePlugin::instance()->getViewUrl( 'tpress-my-projects' );
	}
	
	/**
	 * Get the link to show a project's home page
	 */
	public function getCreateProjectLink() {
		return TPressAdminInterfacePlugin::instance()->getViewUrl( 'tpress-project-home' );
	}
	
	/**
	 * Get the link to show a project's home page
	 */
	public function getProjectHomeLink( $project_id ) {
		return TPressAdminInterfacePlugin::instance()->getViewUrl( 'tpress-project-home&project-id=' . $project_id );
	}
	
	/**
	 * Get the link to manage the project's users
	 */
	public function getProjectUsersLink( $project_id ) {
		return TPressAdminInterfacePlugin::instance()->getViewUrl( 'tpress-project-users&project-id=' . $project_id );
	}
	
	/**
	 * Get the link to view the user's page within the context of this project
	 */
	public function getUserDetailsLink( $project_id, $user_id ) {
		return TPressAdminInterfacePlugin::instance()->getViewUrl( 'tpress-project-user&project-id=' . $project_id . '&user-id=' . $user_id );
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
		$dispatcher->registerView( new TPressProjectHomeView() );
	}
	
	/** Pluging UI on the Dashboard **********************************************************************************/
		
	public function includeNewProjectDialogHtml() {
		unset( $GLOBALS['post'] );
		include( 'view/TPressProjectDetailsDialog.template.php' );
	}
		
	public function appendMainDashboardLinks( $links ) {
		$links[] = array(
				'title' 	=> __( 'My Projects', 'tpress' ),
				'url'		=> $this->getMyProjectsLink(),
				'class'		=> 'my-projects'
			);
			
		if ( current_user_can( 'tpress_manage_projects' ) ) {
			$links[] = array(
					'title' 	=> __( 'New Project', 'tpress' ),
					'url'		=> '',
					'class'		=> 'new-project'
				);
		}
			
		return $links;
	}
	
	/**
	 * 
	 * @param TPressDashboardView $dashboard_view
	 */
	public function registerDashboardPanels( $dashboard_view ) {
		$dashboard_view->addDashboardPanel( new TPressProjectCardBoardPanel( 
				'pinned-projects', __( 'Pinned Projects', 'tpress' ), 
				TPressProject::findPinnedProjects( 8 ) 
			) );
		$dashboard_view->addDashboardPanel( new TPressProjectCardBoardPanel( 
				'all-projects',  __( 'All Projects', 'tpress' ), TPressProject::findAll() 
			) );
		$dashboard_view->addDashboardPanel( new TPressProjectCardBoardPanel( 
				'my-projects',  __( 'My Projects', 'tpress' ), TPressProject::findUserProjects() 
			) );
	}

	/**
	 * Enqueue the javascript needed when dealing with projects
	 */
	public function enqueueDashboardScripts() {
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-select2', TeamPress::instance()->getIncludesUrl() . 'js/jquery.select2.min.js',
				array( 'jquery' ) );		
		wp_enqueue_script( 'tpress-projects', TeamPress::instance()->getIncludesUrl() . 'plugins/projects/view/tpress_projects.js',
				array( 'jquery' ) );	
	}


	/**
	 * Enqueue the javascript needed when dealing with projects
	 */
	public function enqueueDashboardStyles() {
		wp_dequeue_style( 'jquery-ui-css' );
		wp_enqueue_style( 'jquery-select2-css', TeamPress::instance()->getAdminThemeUrl() .'jquery-select2/select2.css',
			false, '2.5.0', 'screen' );
		wp_enqueue_style( 'jquery-ui-css', TeamPress::instance()->getAdminThemeUrl() .'jquery-ui-aristo.css',
			false, '2.5.0', 'screen' );
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
		
		$count = TPressProject::countProjects();
		
		$statistics[ 'tpress_projects' ][ 'data' ][] = array(
				'number' => $count, 
				'text' => _n( 'Project', 'Projects', $count, 'tpress' ),
				'link' => $this->getMyProjectsLink() 
			);
		
		return $statistics;
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
		if ( !isset( $statistics[ 'tpress_users' ] ) ) {
			$statistics[ 'tpress_users' ] = array();
			$statistics[ 'tpress_users' ][ 'display_name' ] = __( 'Users', 'tpress' );
			$statistics[ 'tpress_users' ][ 'data' ] = array();
		}
		
		$groups = $this->getUserGroups();
		$users = $project->users;
		$user_counts = array();
		
		foreach ( $users as $group => $group_users ) {
			$user_counts[ $group ] = count( $group_users );
		}
		
		foreach ( $groups as $group ) {
			$singular_name = WordPressHelper::getRoleDisplayName( $group->name );
			$count = $user_counts[ $group->name ];
			
			$statistics[ 'tpress_users' ][ 'data' ][] = array(
					'number' => $count, 
					'text' => $count==1 ? $singular_name : $singular_name . 's',
					'link' => ''
				);
		}
		
		// Compute other statistics ...
		
		return $statistics;
	}
	
	/** Singleton ****************************************************************************************************/

	/** 
	 * Returns the one and only instance of the plugin
	 * 
	 * @return TPressProjectsPlugin 
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new TPressProjectsPlugin();
		}
		return self::$instance;
	}

	/** A dummy constructor to prevent TeamPress from being loaded more than once. */
	protected function __construct() { /* Do nothing here */ }

	/** A dummy magic method to prevent TeamPress from being cloned */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'tpress' ), '3.2' ); }

	/** A dummy magic method to prevent TeamPress from being unserialized */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'tpress' ), '3.2' ); }
	
	/**
	 * @var TPressProjectsPlugin
	 */
	private static $instance;
	
	/** Instance variables *******************************************************************************************/

	/**
	 * @var array
	 */
	private $default_group_capabilities;
	
	/**
	 * @var TPressProjectAjaxController
	 */
	private $ajaxController;
}

endif; // class_exists