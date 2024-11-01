<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressPluginBase' ) ) :

/**
 * Defines the base class for TeamPress plugins
 */
abstract class TPressPluginBase {
	
	/** General Methods **********************************************************************************************/

	/** Simple accessor */
	public function getTeamPress() {
		return $this->tpress;
	}
	
	/**
	 * Get a proper display name for the plugin
	 *
	 * @return string The display name
	 */
	abstract public function getDisplayName();

	/**
	 * Function called when the plugin has been registered in TeamPress
	 *
	 * @param $tpress TeamPress plugin instance
	 */
	public function onPluginRegistered( $tpress ) {
		$this->tpress = $tpress;
	}
	
	/**
	 * List the general capabilities exposed by the plugin
	 *
	 * @return array An array of strings
	 */
	public function getAllCapabilities() {
		return array();
	}

	/**
	 * Constructor registering basic actions. This constructor is protected because child classes should implement the
	 * singleton pattern.
	 */
	protected function __construct() {
	}
	
	protected $tpress;
}

endif; // class_exists