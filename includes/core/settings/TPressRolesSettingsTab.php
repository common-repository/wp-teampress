<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressRolesSettingsTab' ) ) :

require_once( 'TPressSettingsTab.php' );

/**
 * The main option view
 */
class TPressRolesSettingsTab extends TPressSettingsTab {

	// @Override
	public function getTabTitle() {
		return __( 'Roles & Capabilities', 'tpress' );
	}

	// @Override
	public function getTitle() {
		return __( 'Capabilities assigned to WordPress roles', 'tpress' );
	}
	
	// @Override
	public function getSlug() {
		return 'tpress-roles-settings';
	}

	// @Override
	public function saveSettings( $post_data ) {
		global $wp_roles;
		
		$all_plugins = TeamPress::instance()->getRegisteredPlugins();
		$roles 	= $wp_roles->role_objects;
		
		foreach ( $all_plugins as $plugin ) {
			$plugin_caps 	= $plugin->getAllCapabilities();
			
			if ( empty( $plugin_caps ) ) continue;	
			foreach ( $roles as $role ) {
				if ( $this->isRoleHidden( $role ) ) continue;
				
				foreach ( $plugin_caps as $cap ) {
					$name = str_replace( ' ', '-', $role->name . '_' . $cap );
					if ( isset( $post_data[ $name ] ) ) {
						$role->add_cap( $cap );       
					} else {
						$role->remove_cap( $cap );                
					}
				}	
			}	
		}
	}
	
	/**
	 * Decide if we should hide this role from the view
	 */
	protected function isRoleHidden( $role ) {
		if ( !strncmp( $role->name, 'tpress_group_', strlen( 'tpress_group_' ) ) ) return true;
		return false;
	}
}

endif; // class_exists