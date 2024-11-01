<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressUserGroupBasedPlugin' ) ) :

require_once( 'TPressPluginBase.php' );

/**
 * Defines the base class for TeamPress plugins which are involving user groups. 
 * 
 * By convention, plugins should define the group id so that it starts by "tpress_group_". For example: 
 * tpress_group_project_leader (@see TPressProjectsPlugin)
 */
abstract class TPressUserGroupBasedPlugin extends TPressPluginBase {

	/** User Group methods *******************************************************************************************/
	
	/**
	 * List the groups exposed by the plugin. A group is in fact similar to a role in WordPress. However, a group will
	 * be used in the context of an object (for instance, the project plugin defines several groups of users (leaders,
	 * users, clients, ...) and users may belong to a group in one project, and to another group in another project.
	 *
	 * Groups will then be used to define what users are able to do within the related object.
	 *
	 * @return array An array of WP_Role objects 
	 */
	public function getUserGroups() {
		if ( !isset( $this->groups ) ) {
			$this->groups = array();
			$this->defineUserGroups();
		}
		
		return $this->groups;
	}
	
	/**
	 * Get a particular user group.
	 *
	 * @return WP_Role The group or null if not found
	 */
	public function getUserGroup( $id ) {
		$this->getUserGroups();
		if ( !isset( $this->groups ) || !isset( $this->groups[ $id ] ) ) return null;		
		return $this->groups[ $id ];
	}
	
	/**
	 * Reset the groups (delete the role and reload them)
	 */
	public function resetUserGroups() {	
		foreach ( $this->getUserGroups() as $id => $group ) {
			remove_role( $id );
		}
		unset( $this->groups );
		$this->getUserGroups();
	}
	
	/**
	 * Add a group to the array of groups used by the plugin
	 *
	 * @param $id string The name of the group
	 * @param $display_name string The group name as shown to humans
	 * @param $default_capabilities array The default capabilities to assign to the group if we have to create it
	 */
	protected function addUserGroup( $id, $display_name, $default_capabilities ) {
		$caps = array();		
		foreach ( $default_capabilities as $cap ) {
			$caps[ $cap ] = true;
		}
		
		$this->groups[ $id ] = WordPressHelper::getOrCreateRole( $id, $display_name, $caps );
	}
	
	/**
	 * List the capabilities that are used by the user groups
	 *
	 * @return array An array of strings representing the capabilities used by our groups
	 */
	abstract public function getUserGroupsCapabilities();
	
	/**
	 * This function shall be implemented by subclasses in order to add the groups they intend to use. The subclasses
	 * should in fact be using the function TPressUserGroupBasedPlugin::addUserGroup to add those groups.
	 */
	abstract protected function defineUserGroups();
	
	/** Instance variables *******************************************************************************************/

	// Groups is an array of WP_Role objects
	private $groups;
}

endif; // class_exists