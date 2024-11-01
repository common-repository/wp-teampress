<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressAdminInterfacePlugin' ) ) :

require_once( TeamPress::instance()->getIncludesDir() . 'core/settings/TPressSettingsView.php' );
require_once( TeamPress::instance()->getIncludesDir() . 'plugins/admininterface/view/TPressDashboardView.php' );
require_once( TeamPress::instance()->getIncludesDir() . 'plugins/admininterface/view/template_functions.php' );

/** TeamPress plugin registration ************************************************************************************/
TeamPress::registerPlugin( 'TPressAdminInterfacePlugin' );

/**
 * The class showing the main TeamPress user interface within the WordPress administration panel. This acts as a 
 * dispatcher to determine which view will be shown.
 */
class TPressAdminInterfacePlugin extends TPressViewDispatcherPlugin {
	
	/** TPressPluginBase overrides ***********************************************************************************/
	
	// @Override
	public function onPluginRegistered($tpress) {
		parent::onPluginRegistered($tpress);
		
		add_action( 'tpress_admin_menu', array(&$this, 'addAdminMenu') ); 
		add_action( 'tpress_ready', array(&$this, 'onTeamPressReady') ); 
		
		// Show widgets in WordPress dashboard
		add_action( 'tpress_admin_dashboard_setup', array(&$this, 'registerWordPressDashboardWidget') ); 
		
		// Add "View Dashboard" link on plugins page
		add_filter( 'plugin_action_links_teampress', array(&$this, 'addPluginPageLinks' ) );
	}
	
	// @Override
	public function getDisplayName() {
		return __( 'Admin Interface Core Plugin', 'tpress' );
	}
	
	// @Override
	public function getAllCapabilities() {
		return apply_filters( 'tpress_admin_interface_required_capabilities', array(
				'tpress_view_admin_interface'
			) );
	}
	
	/** TPressPluginBase overrides ***********************************************************************************/
	
	// @Override
	public function addAdminMenu() {		
		add_menu_page(
				__('TeamPress Dashboard', 'tpress'), 
				__('TeamPress', 'tpress'), 
				'tpress_view_admin_interface',
				$this->getDispatcherSlug(), 
				array (&$this, 'onShowView'),
				'', 
				500
			);
			
		parent::addAdminMenu();
	}
		
	// @Override
	public function getDispatcherSlug() {
		return 'tpress-home'; 
	}
	
	/** General Methods **********************************************************************************************/
	
	/**
	 * To be done when TeamPress is ready
	 */
	public function onTeamPressReady() {
		// Register dashboard views
		$this->registerView( new TPressDashboardView() );
		$this->registerView( new TPressSettingsView() );
		do_action_ref_array( 'tpress_admin_interface_register_views', array( &$this ) );
	}
	
	/**
	 * Add some links under the plugin name in the WordPress plugins admin page
	 */
	public function addPluginPageLinks( $links ) {
		$settings_link = '<a href="' . $this->getDashboardUrl() . '">'.__('Go to TeamPress Dashboard', 'tpress').'</a>';
		array_unshift ( $links, $settings_link );
		return $links;
	}
	
	/**
	 * Register the 'Right Now in TeamPress' widget in the WordPress dashboard
	 */
	public function registerWordPressDashboardWidget() {
		wp_add_dashboard_widget( 
				'tpress_dashboard_rightnow', 
				__( 'Right Now in TeamPress', 'tpress' ), 
				array(&$this, 'renderRightNowWidget' ) 
			);
	}
	
	/**
	 * Render the dashboard widget to show off some TeamPress statistics. The statistics are provided by the plugins 
	 * themselves. They should return whatever they way in an array with the following structure:
	 * 	'stat_category_slug' => array(
	 * 	  'display_name' => 'Projects',
	 *	  'data' => array( 
	 *	    array(
	 *        'number' => 10, 
	 *        'text' => 'Projects', 
	 *        'link' => 'http://example.com/projects/' ),
	 *	    array(
	 *        'number' => 200, 
	 *        'text' => 'Tasks', 
	 *        'link' => 'http://example.com/tasks/' )
	 *    )
	 *  )
	 */
	public function renderRightNowWidget() {
		$statistics = apply_filters( 'tpress_dashboard_right_now_statistics', array() );
		
		include( 'view/TPressDashboardRightNowWidget.inc.php' );
	}
	
	/** Url builders *************************************************************************************************/
    
	public function getDashboardUrl() {
		return $this->getViewUrl(); 
	}
		
	/** Singleton ****************************************************************************************************/

	/** Returns the one and only instance of the plugin */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new TPressAdminInterfacePlugin();
			self::$instance->views = array();
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