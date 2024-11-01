<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressUpdaterPlugin' ) ) :

/** TeamPress plugin registration ************************************************************************************/
TeamPress::registerPlugin( 'TPressUpdaterPlugin' );

/**
 * The class handling plugin updates. When there is a need to run code on a version change, you need to add some code
 * in the run() method, after comparing the database version to current version. The update code can be put in a 
 * function at the end of this class under the "Branch update functions" section header.
 */
class TPressUpdaterPlugin extends TPressPluginBase {
	
	/** TPressPluginBase overrides ***********************************************************************************/
		
	// @Override
	public function getDisplayName() {
		return __( 'Updater Core Plugin', 'tpress' );
	}
	
	// @Override
	public function onPluginRegistered($tpress) {
		parent::onPluginRegistered($tpress);
			
		add_action( 'tpress_ready', array( &$this, 'onTeamPressReady' ) );
	}
	
	/** Public Methods ***********************************************************************************************/
	
	/**
	 * Run the plugin
	 */
	public function onTeamPressReady($tpress) {
		$this->run();
	}
	
	/**
	 * TeamPress' version updater looks at what the current database version is, and
	 * runs whatever other code is needed.
	 */
	public function run() {
		$tpress = $this->getTeamPress();

		// Allow plugins to run some code on new install
		if ( !$this->isInstalled() ) {
			do_action_ref_array( 'tpress_new_installation', array( &$tpress ) );
		}
	
		// Bail if no update needed
		if ( !$this->isUpdateRequired() ) return;

		// Get the raw database version
		$raw_db_version = (int) $this->tpress->getDbVersionRaw();

		// Allow plugins to run some code before the update
		do_action_ref_array( 'tpress_before_update', array( &$tpress ) );
		
		/** 1.0 Branch ***********************************************************************************************/
		if ( $raw_db_version < 100 ) {
			// Nothing to do yet
		}

		// Bump the version
		$this->doVersionBump();

		// Allow plugins to run some code after the update
		do_action_ref_array( 'tpress_after_update', array( &$tpress ) );
	}
	
	/** Singleton ****************************************************************************************************/

	/** Returns the one and only instance of the plugin */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new TPressUpdaterPlugin();
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
	
	/** Private Methods **********************************************************************************************/
	
	/**
	 * Check if the plugin is installed
	 *
	 * @return bool True if the plugin is already installed
	 */
	private function isInstalled() {
		return !$this->tpress->getDbVersionRaw();
	}
	
	/**
	 * Compare the TeamPress version to the DB version to determine if updating is required
	 *
	 * @return bool True if update is required, False if not
	 */
	private function isUpdateRequired() {
		$raw    = (int) $this->tpress->getDbVersionRaw();
		$cur    = (int) $this->tpress->getDbVersion();
		$retval = (bool) ( $raw < $cur );
		return $retval;
	}

	/**
	 * Update the DB to the latest version
	 */
	private function doVersionBump() {
		$db_version = $this->tpress->getDbVersion();
		update_option( '_tpress_db_version', $db_version );
	}
	
	/** Branch update functions **************************************************************************************/

	
}

endif; // class_exists