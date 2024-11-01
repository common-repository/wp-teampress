<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressUserGroupsSettingsTab' ) ) :

require_once( 'TPressSettingsTab.php' );

/**
 * The main option view
 */
class TPressUserGroupsSettingsTab extends TPressSettingsTab {

	// @Override
	public function getTabTitle() {
		return __( 'User Groups', 'tpress' );
	}

	// @Override
	public function getTitle() {
		return __( 'Capabilities assigned to TeamPress User Groups', 'tpress' );
	}

	// @Override
	public function getSlug() {
		return 'tpress-user-groups-settings';
	}

	// @Override
	public function saveSettings( $post_data ) {		
		$all_plugins = TeamPress::instance()->getRegisteredPlugins();
		
		foreach ( $all_plugins as $plugin ) {			
			if ( !( $plugin instanceof TPressUserGroupBasedPlugin ) ) continue;	
			
			$plugin_caps 	= $plugin->getUserGroupsCapabilities();
			$groups			= $plugin->getUserGroups();
			
			if ( empty( $plugin_caps ) || empty( $groups ) ) continue;	
			
			foreach ( $groups as $group ) {
				foreach ( $plugin_caps as $cap ) {
					$name = str_replace( ' ', '-', $group->name . '_' . $cap );
					if ( isset( $post_data[ $name ] ) ) {
						$group->add_cap( $cap );       
					} else {
						$group->remove_cap( $cap );                
					}
				}	
			}	
		}
	}
	
	// @Override
	public function onPostDataSubmitted( $post_data ) {		
		if ( isset( $post_data[ 'tpress_reset_groups' ] ) ) {
			$all_plugins = TeamPress::instance()->getRegisteredPlugins();
			
			foreach ( $all_plugins as $plugin ) {			
				if ( !( $plugin instanceof TPressUserGroupBasedPlugin ) ) continue;	
				
				$plugin->resetUserGroups();
			}
		}
	}	
}

endif; // class_exists