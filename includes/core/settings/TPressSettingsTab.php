<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !interface_exists( 'TPressSettingsTab' ) ) :

/**
 * Plugins that have settings should implement this interface and register that tab using: 
 *
 * <code>TPressSettingsPlugin::instance()->registerSettingsTab( new MyTPressSettingsTab() );</code>
 */
abstract class TPressSettingsTab {
	
	/**
	 * Gets the title of the tab that allows to access that particular tab
	 * @return string the title
 	 */
	abstract public function getTabTitle();
	
	/**
	 * Gets the title of the tab shown in the panel. It should be more descriptive than the tab tab
	 * @return string the title
 	 */
	abstract public function getTitle();
	
	/**
	 * Gets the slug of that particular tab
	 * @return string the slug (shall be unique)
 	 */
	abstract public function getSlug();
	
	/**
	 * Display the form corresponding to the tab
	 */
	public function renderTabContent() {
		include( get_class( $this ) . '.template.php' );
	}
	
	/**
	 * Save the settings corresponding to the tab
	 *
	 * @param $post_data array The $_POST data submitted by the whole settings form
	 */
	public function saveSettings( $post_data ) {
	}
	
	/**
	 * Called when POST data has been submitted and after we have saved settings. Tabs should use this function to
	 * execute additional actions that may have been triggered by another button than the common save button.
	 *
	 * @param $post_data array The $_POST data submitted by the whole settings form
	 */
	public function onPostDataSubmitted( $post_data ) {
	}
}

endif; // interface_exists