<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressSettingsView' ) ) :

require_once( 'TPressMainSettingsTab.php' );
require_once( 'TPressRolesSettingsTab.php' );
require_once( 'TPressUserGroupsSettingsTab.php' );

/**
 * The settings view 
 */
class TPressSettingsView extends TPressView {

	public function __construct() {
		$this->tabs = array();
		
		// Some default core settings tabs
		$this->registerTab( new TPressMainSettingsTab() );
		$this->registerTab( new TPressRolesSettingsTab() );
		$this->registerTab( new TPressUserGroupsSettingsTab() );
		
		do_action( 'tpress_register_settings_tabs', $this );	
	}

	/** TPressPluginBase overrides ***********************************************************************************/
	
	// @Override
	public function getSlug() {
		return 'tpress-settings-main';
	}
	
	// @Override
	public function checkAdminReferer( $post_data ) {
		if ( !parent::checkAdminReferer( $post_data ) ) return false;
		
		return check_admin_referer('tpress_save_settings');
	}
	
	// @Override
	public function onPostDataSubmitted( $post_data ) {
		parent::onPostDataSubmitted( $post_data );
		
		foreach ( $this->tabs as $tab ) {
			$tab->saveSettings( $_POST );
			$tab->onPostDataSubmitted( $_POST );
		} 
	}
	
	// @Override
	public function addAdminMenu( $dispatcher ) {
		parent::addAdminMenu( $dispatcher );
		
		$dispatcher->addViewSubMenu(
				$this,
				__('TeamPress &raquo; Settings', 'tpress'), 
				__('Settings', 'tpress'), 
				'tpress_manage_settings'
			);
	}
	
	public function render() {
		if ( !current_user_can( 'tpress_manage_settings' ) ) {
			TeamPress::addError( __( 'You are not allowed to manage TeamPress! Please contact your administrator', 'tpress' ) ); 
		}
		
		parent::render();
	}
	
	// @Override
	public function enqueueScripts() {
		parent::enqueueScripts();
		
		wp_enqueue_script( 'jquery-ui-tabs' );
	}
	
	// @Override
	public function enqueueStyles() {
		parent::enqueueStyles();

		wp_dequeue_style( 'jquery-ui-css' );
		wp_enqueue_style( 'jquery-ui-css', TeamPress::instance()->getAdminThemeUrl() .'jquery-ui-aristo.css',
				false, '2.5.0', 'screen' );
	}
	
	/** Simple accessor */
	public function getRegisteredTabs() {
       return $this->tabs;
	}
    
	/**
	 * Call this function to register a tab
	 */
	public function registerTab( $tab ) {
		if ( !( $tab instanceof TPressSettingsTab ) ) {
			_doing_it_wrong( 
					__FUNCTION__, 
					__( 'TPressSettingsView only accepts TPressSettingsTab implementations as tabs', 'tpress' ),
					'1.0.0' );
			return;
		}  else if ( isset( $this->tabs[ $tab->getSlug() ] ) ) {
			_doing_it_wrong( 
					__FUNCTION__, 
					sprintf( 
							__( 'A tab with the slug %s is already registered', 'tpress' ),
							$tab->getSlug()),
					'1.0.0' );
			return;
		}
		
		$this->tabs[ $tab->getSlug() ] = $tab;
	}
	
	/** General Methods **********************************************************************************************/
	
	/** 
	 * List the capabilities required by this view 
	 */
	public static function appendRequiredCapabilities( $capabilities ) {
		$capabilities[] = 'tpress_manage_settings';
		return $capabilities;
	}
	
	/** Instance variables *******************************************************************************************/
	
	private $tabs;
}

// We got to declare this outside of the class so that it gets called early enough (the view is still not instanciated
// at the time the filter gets executed
add_filter( 'tpress_admin_interface_required_capabilities', 
		array( 'TPressSettingsView', 'appendRequiredCapabilities' ) );

endif; // interface_exists