<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressProjectCardBoardPanel' ) ) :

/**
 * View of the admin interface that shows the projects corresponding to a query as cards
 */
class TPressProjectCardBoardPanel extends TPressPanel {

	public function __construct( $slug, $title, $query ) {
		$this->slug = $slug;
		$this->query = $query;
		$this->title = $title;
	}

	/** TPressPanel overrides ****************************************************************************************/
	
	// @Override
	public function getSlug() {
		return $this->slug;
	}
	
	// @Override	
	public function render() {
		global $panel_query, $panel_title, $user_ID;
		$panel_query = $this->query;
		$panel_title = $this->title;
	
		parent::render();
	}
	
	/** General Methods **********************************************************************************************/
	
	
	/** Instance variables *******************************************************************************************/
	private $slug;
	private $query;
	private $title;
}

endif; // interface_exists