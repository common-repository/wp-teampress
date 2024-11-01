<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressMainSettingsTab' ) ) :

require_once( 'TPressSettingsTab.php' );

/**
 * The main option view
 */
class TPressMainSettingsTab extends TPressSettingsTab {

	// @Override
	public function getTabTitle() {
		return __( 'Global Settings', 'tpress' );
	}

	// @Override
	public function getTitle() {
		return __( 'Global TeamPress Settings', 'tpress' );
	}

	// @Override
	public function getSlug() {
		return 'tpress-main-settings';
	}

	// @Override
	public function saveSettings( $post_data ) {		
	}	
	
	// @Override
	public function onPostDataSubmitted( $post_data ) {		
	}	
}

endif; // class_exists