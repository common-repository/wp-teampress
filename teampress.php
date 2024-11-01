<?php
/**
 * Plugin Name: TeamPress
 * Plugin URI:  http://teampress.marvinlabs.com
 * Description: TeamPress is project management software, made the WordPress way.
 * Author:      MarvinLabs
 * Author URI:  http://www.marvinlabs.com
 * Version:     1.0.0-alpha
 * Text Domain: teampress
 * Domain Path: /languages/
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TeamPress' ) ) :
/**
 * Main TeamPress plugin Class
 */
final class TeamPress {

	/** Version numbers **********************************************************************************************/
	
	/**
	 * Return the TeamPress database version
	 *
	 * @retrun string The TeamPress version
	 */
	public function getVersion() {
		return '1.0.0-alpha';
	}
	
	/**
	 * Return the TeamPress database version
	 *
	 * @retrun string The TeamPress version
	 */
	public function getDbVersion() {
		return '100';
	}
	
	/**
	 * Return the TeamPress database version directly from the database
	 *
	 * @retrun string The current TeamPress version
	 */
	public function getDbVersionRaw() {
		return get_option( '_tpress_db_version', '' );
	}
	
	/** Dirs and Urls ************************************************************************************************/
	
	/** Simple accessor to the admin theme URL */
	public function getAdminThemeUrl() {
		return $this->admin_theme_url;
	}
	
	/** Simple accessor to the frontend theme URL */
	public function getThemeUrl() {
		return $this->theme_url;
	}
	
	/** Simple accessor */
	public function getAdminThemeDir() {
		return $this->admin_theme_dir;
	}
	
	/** Simple accessor */
	public function getThemeDir() {
		return $this->theme_dir;
	}
	
	/** Simple accessor */
	public function getIncludesDir() {
		return $this->includes_dir;
	}
	
	/** Simple accessor */
	public function getIncludesUrl() {
		return $this->includes_url;
	}

	/** Plugin Methods ***********************************************************************************************/

	/**
	 * Register a plugin instance in TeamPress
	 * 
	 * @param $plugin a TPressPluginBase subclass that implements the singleton pattern via the instance() method
	 */
	public static function registerPlugin( $plugin_class ) {		
		add_action( 'tpress_register_plugin', create_function( 
			'$tpress', 
			'$tpress->addPlugin( ' . $plugin_class . '::instance() );') );
	}
	
	/**
	 * Add a plugin to our registry. The plugin will be notified that it has been registered and we will by default
	 * assign all the plugin capabilities to the WordPress site administrators.
	 */
	public function addPlugin( $plugin ) {		
		if ( !( $plugin instanceof TPressPluginBase ) ) {
			_doing_it_wrong( 
					__FUNCTION__, 
					__( 'You can only register plugin classes inheriting from TPressPluginBase', 'tpress' ), '3.2' );
			return;
		}
		
		$this->plugins[] = $plugin;
		
		// Notify about registration
		$plugin->onPluginRegistered( $this );
			
		// By default, assign all the plugin capabilities to the WordPress site administrators
		$plugin_caps = $plugin->getAllCapabilities();
		$role = get_role('administrator');
		if (!empty($role)) {
			foreach ( $plugin_caps as $cap ) {
				$role->add_cap($cap);
			}
		}
	}
	
	/**
	 * Get all plugins registered in TeamPress
	 */
	public function getRegisteredPlugins() {
		return $this->plugins;
	}
	
	/** Singleton ****************************************************************************************************/

	/**
	 * @var TeamPress The one true TeamPress
	 */
	private static $instance;

	/**
	 * Main TeamPress Instance
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new TeamPress();
			self::$instance->setupGlobals();
			self::$instance->includeDependencies();
			self::$instance->checkExternalPluginDependencies();
			self::$instance->setupActions();
			
			// Give a chance to plugins to register themselves			
			do_action_ref_array( 'tpress_register_plugin', array( &self::$instance ) );
		}
		return self::$instance;
	}

	/**
	 * A dummy constructor to prevent TeamPress from being loaded more than once.
	 */
	private function __construct() { /* Do nothing here */ }

	/**
	 * A dummy magic method to prevent TeamPress from being cloned
	 */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'tpress' ), '3.2' ); }

	/**
	 * A dummy magic method to prevent TeamPress from being unserialized
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'tpress' ), '3.2' ); }

	/** Private Methods **********************************************************************************************/
	
	/**
	 * Set some smart defaults to class variables. Allow some of them to be
	 * filtered to allow for early overriding.
	 */
	private function setupGlobals() {

		/** Paths ****************************************************************************************************/

		// Setup some base path and URL information
		$this->file       = __FILE__;
		$this->basename   = plugin_basename( $this->file );
		$this->plugin_dir = plugin_dir_path( $this->file );
		$this->plugin_url = plugin_dir_url ( $this->file );

		// Includes
		$this->includes_dir = trailingslashit( $this->plugin_dir . 'includes'  );
		$this->includes_url = trailingslashit( $this->plugin_url . 'includes'  );

		// Themes
		$this->theme_dir 		= apply_filters( 
				'tpress_theme_dir', 	
				trailingslashit( $this->plugin_dir . 'themes/frontend/default' ),
				$this );
		$this->admin_theme_dir 	= apply_filters( 
				'tpress_admin_theme_dir', 	
				trailingslashit( $this->plugin_dir . 'themes/admin/default' ),
				$this );
		$this->theme_url 		= apply_filters( 
				'tpress_theme_url', 	
				trailingslashit( $this->plugin_url . 'themes/frontend/default' ),
				$this );
		$this->admin_theme_url 	= apply_filters( 
				'tpress_admin_theme_url', 	
				trailingslashit( $this->plugin_url . 'themes/admin/default' ),
				$this );
		
		// Languages
		$this->lang_dir     = trailingslashit( $this->plugin_dir . 'languages' );

		/** Users ****************************************************************************************************/

		$this->current_user   = new stdClass(); // Currently logged in user
		$this->displayed_user = new stdClass(); // Currently displayed user

		/** Misc *****************************************************************************************************/

		$this->domain         = 'tpress'; 		// Unique identifier for retrieving translated strings
		$this->messages       = array( 'error' => array(), 'updated' => array() ); 		// Feedback

		/** Cache *************************************************************/

		// Add TeamPress to global cache groups
		wp_cache_add_global_groups( 'tpress' );
		
		// All TeamPress globals are setup
		do_action_ref_array( 'tpress_after_setup_globals', array( &$this ) );
	}

	/**
	 * Include required files
	 */
	private function includeDependencies() {
		
		/** We use TGM Plugin Activation to handle dependency on other plugins ***************************************/
		
		require_once( $this->includes_dir . 'tgm-plugin-activation/class-tgm-plugin-activation.php');

		/** Helpers **************************************************************************************************/
		
		require_once( $this->includes_dir . 'helpers/WordPressHelper.php');
		
		/** Core *****************************************************************************************************/

		require_once( $this->includes_dir . 'core/TPressAjaxController.php');
		require_once( $this->includes_dir . 'core/TPressView.php');
		require_once( $this->includes_dir . 'core/TPressPanel.php');
		require_once( $this->includes_dir . 'core/TPressPluginBase.php');
		require_once( $this->includes_dir . 'core/TPressUserGroupBasedPlugin.php');
		require_once( $this->includes_dir . 'core/TPressViewDispatcherPlugin.php');
		require_once( $this->includes_dir . 'core/settings/TPressSettingsView.php');
		
		/** Default Plugins ******************************************************************************************/
		require_once( $this->includes_dir . 'plugins/admininterface/TPressAdminInterfacePlugin.php');
		require_once( $this->includes_dir . 'plugins/updater/TPressUpdaterPlugin.php');
		require_once( $this->includes_dir . 'plugins/projects/TPressProjectsPlugin.php');
		require_once( $this->includes_dir . 'plugins/tasks/TPressTasksPlugin.php');
	}

	/**
	 * Setup the default hooks and actions
	 */
	private function setupActions() {

		// Add actions to plugin activation and deactivation hooks
		add_action( 'activate_'   . $this->basename, array( &$this, 'onActivation' ) );
		add_action( 'deactivate_' . $this->basename, array( &$this, 'onDeactivation' ) );

		// If TeamPress is being deactivated, do not add any actions
		if ( $this->isBeingDeactivated( $this->basename ) ) return;
		
		// If we are dependent on other plugins, we need to make sure they are installed and activated.
		add_action( 'tgmpa_register', array( &$this, 'checkExternalPluginDependencies' ) );
		
		// Add action when plugins are loaded
		add_action( 'plugins_loaded', array( &$this, 'onPluginsLoaded' ) );
		
		// Add the main admin menu
		if ( is_admin() ) {
			add_action('admin_menu', array(&$this, 'addAdminMenu'), 9);  
			add_action('admin_enqueue_scripts', array(&$this, 'addAdminScripts') );
			add_action('admin_enqueue_scripts', array(&$this, 'addAdminStyles') );
			add_action('wp_dashboard_setup', array(&$this, 'onDashboardSetup'));
		}
		
		// All TeamPress actions are setup
		do_action_ref_array( 'tpress_after_setup_actions', array( &$this ) );
	}

	/**
	 * If we are dependent on other plugins, we need to make sure they are installed and activated.
	 */
	public function checkExternalPluginDependencies() {
		$external_plugins = array(
			'advanced-custom-fields' => array(
				'name' 				=> 'Advanced Custom Fields',
				'slug' 				=> 'advanced-custom-fields',
				'source'    		=> $this->plugin_dir . '/lib/advanced-custom-fields.zip', 
				'required' 			=> true,
				'force_activation' 	=> true
			)
		);		
		
		$config = array(
				'domain'       		=> 'tpress', 	
				'has_notices'      	=> true,                       	
				'is_automatic'    	=> true,						
				'strings'      		=> array(
					'page_title'                       	=> __( 'Install Required Plugins', 'tpress' ),
					'menu_title'                       	=> __( 'Install Plugins', 'tpress' ),
					'installing'                       	=> __( 'Installing Plugin: %s', 'tpress' ), // %1$s = plugin name
					'oops'                             	=> __( 'Something went wrong with the plugin API.', 'tpress' ),
					'notice_can_install_required'     	=> _n_noop( 'TeamPress requires the following plugin: %1$s.', 'TeamPress requires the following plugins: %1$s.' ), // %1$s = plugin name(s)
					'notice_can_install_recommended'	=> _n_noop( 'TeamPress recommends the following plugin: %1$s.', 'TeamPress recommends the following plugins: %1$s.' ), // %1$s = plugin name(s)
					'notice_cannot_install'  			=> _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ), // %1$s = plugin name(s)
					'notice_can_activate_required'    	=> _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s)
					'notice_can_activate_recommended'	=> _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s)
					'notice_cannot_activate' 			=> _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ), // %1$s = plugin name(s)
					'notice_ask_to_update' 				=> _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with TeamPress: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with TeamPress: %1$s.' ), // %1$s = plugin name(s)
					'notice_cannot_update' 				=> _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ), // %1$s = plugin name(s)
					'install_link' 					  	=> _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
					'activate_link' 				  	=> _n_noop( 'Activate installed plugin', 'Activate installed plugins' ),
					'return'                           	=> __( 'Return to Required Plugins Installer', 'tpress' ),
					'plugin_activated'                 	=> __( 'Plugin activated successfully.', 'tpress' ),
					'complete' 							=> __( 'All plugins installed and activated successfully. %s', 'tpress' ), // %1$s = dashboard link
					'nag_type'							=> 'updated'
				)
			);
			
		tgmpa( apply_filters( 'tpress_external_plugin_dependencies', $external_plugins), $config );
	}
	
	/** WordPress action hook override */
	public function onActivation() {
		do_action_ref_array( 'tpress_activation', array( &$this ) );
	}
	
	/** WordPress action hook override */
	public function onDeactivation() {
		do_action_ref_array( 'tpress_deactivation', array( &$this ) );
	}
	
	/** WordPress action hook override */
	public function addAdminMenu() {	
		do_action_ref_array( 'tpress_admin_menu', array( &$this ) );
	}
	
	/** WordPress action hook override */
	public function addAdminScripts() {
		do_action_ref_array( 'tpress_admin_scripts', array( &$this ) );
	}
	
	/** WordPress action hook override */
	public function addAdminStyles() {
		echo "<link rel='stylesheet' type='text/css' href='" . $this->getAdminThemeUrl() . "tpress-style.css' />";
		do_action_ref_array( 'tpress_admin_styles', array( &$this ) );
	}
	
	/** WordPress action hook override */
	public function onDashboardSetup() {
		do_action_ref_array( 'tpress_admin_dashboard_setup', array( &$this ) );
	}
	
	/** WordPress action hook override */
	public function onPluginsLoaded() {	
		$this->loadTextDomain();
		$this->setupCurrentUser();
	
		do_action_ref_array( 'tpress_ready', array( &self::$instance ) );
	}
	
	/**
	 * Display the options page
	 */
	public function onShowSettings() {
		include_once( $this->includes_dir . '/core/view/settings.php' );
	}
	
	/**
	 * Determine if TeamPress is being deactivated
	 *
	 * @return bool True if deactivating TeamPress, false if not
	 */
	private function isBeingDeactivated( $basename = '' ) {
		
		$action = false;
		if ( ! empty( $_REQUEST['action'] ) && ( '-1' != $_REQUEST['action'] ) )
			$action = $_REQUEST['action'];
		elseif ( ! empty( $_REQUEST['action2'] ) && ( '-1' != $_REQUEST['action2'] ) )
			$action = $_REQUEST['action2'];

		// Bail if not deactivating
		if ( empty( $action ) || !in_array( $action, array( 'deactivate', 'deactivate-selected' ) ) )
			return false;

		// The plugin(s) being deactivated
		if ( $action == 'deactivate' ) {
			$plugins = isset( $_GET['plugin'] ) ? array( $_GET['plugin'] ) : array();
		} else {
			$plugins = isset( $_POST['checked'] ) ? (array) $_POST['checked'] : array();
		}

		// Set basename if empty
		if ( empty( $basename ) && !empty( $this->basename ) )
			$basename = $this->basename;

		// Bail if no basename
		if ( empty( $basename ) )
			return false;

		// Is TeamPress being deactivated?
		return in_array( $basename, $plugins );
	}
	
	/** Messages *****************************************************************************************************/

	/**
	 * 
	 * @param string $message
	 */
	public static function addError( $message ) {
		self::instance()->messages['error'][] = $message;
	} 

	/**
	 *
	 * @param string $message
	 */
	public static function addMessage( $message ) {
		self::instance()->messages['updated'][] = $message;
	} 
	
	/**
	 * 
	 */
	public static function printMessages() {		
		foreach ( self::instance()->messages as $key => $list ) {
			if ( empty( $list ) ) continue;
			
			echo '<div id="message" class="' . $key . '"><ul>';
			foreach ( $list as $msg ) {
				echo '<li>' . $msg . '</li>';
			}
			echo '</ul></div>';
		}
	}

	/** Other members & functions ************************************************************************************/


	/**
	 * Load the translation file for current language. Checks the languages
	 * folder inside the TeamPress plugin first, and then the default WordPress
	 * languages folder.
	 *
	 * Note that custom translation files inside the TeamPress plugin folder
	 * will be removed on TeamPress updates. If you're creating custom
	 * translation files, please use the global language folder.
	 *
	 * @return bool True on success, false on failure
	 */
	private function loadTextDomain() {
	
		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale',  get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );
	
		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/teampress/' . $mofile;
	
		// Look in global /wp-content/languages/teampress folder
		if ( file_exists( $mofile_global ) ) {
			return load_textdomain( $this->domain, $mofile_global );
	
			// Look in local /wp-content/plugins/teampress/languages/ folder
		} elseif ( file_exists( $mofile_local ) ) {
			return load_textdomain( $this->domain, $mofile_local );
		}
	
		// Nothing found
		return false;
	}
	
	/**
	 * Setup the currently logged-in user
	 *
	 * Do not to call this prematurely, I.E. before the 'init' action has
	 * started. This function is naturally hooked into 'init' to ensure proper
	 * execution. get_currentuserinfo() is used to check for XMLRPC_REQUEST to
	 * avoid xmlrpc errors.
	 */
	private function setupCurrentUser() {
		$this->current_user = &wp_get_current_user();
	}
	
	private $plugins;
	
	private $file;
	private $basename;
	private $plugin_dir;
	private $plugin_url;
	private $includes_dir;
	private $includes_url;
	private $theme_url;
	private $admin_theme_url;
	private $lang_dir;

	private $current_user; 		// Currently logged in user
	private $displayed_user; 	// Currently displayed user

	private $domain; 			// Unique identifier for retrieving translated strings
	private $messages; 			// Feedback
	
	private $teampress_manager_role;
	private $project_leader_role;
	private $project_coworker_role;
	private $project_client_role;
	
	private $default_role_capababilities;

}

// That's where the magic happens
TeamPress::instance();

endif; // class_exists check
