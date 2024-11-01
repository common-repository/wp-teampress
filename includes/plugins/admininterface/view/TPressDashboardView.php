<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressDashboardView' ) ) :

/**
 * The dashboard view 
 */
class TPressDashboardView extends TPressView {

	public function __construct() {
		$this->panels = array();		
		do_action( 'tpress_dashboard_register_panels', $this );	
	}

	/** TPressPluginBase overrides ***********************************************************************************/
	
	// @Override
	public function getSlug() {
		return 'tpress-dashboard';
	}
	
	// @Override
	public function addAdminMenu( $dispatcher ) {
		parent::addAdminMenu( $dispatcher );
		
		$dispatcher->addViewSubMenu(
				$this,
				__('TeamPress &raquo; Dashboard', 'tpress'), 
				__('Dashboard', 'tpress'), 
				'tpress_dashboard'
			);
	}
	
	/** Dashboard rendering ******************************************************************************************/
	
	/**
	 * Render the main links of the dashboard (My Projects, My Schedule, ...)
	 */
	public function renderMainLinks( $container = "ul", $before_item = "<li class='%s'>", 
		$after_item = "</li>", $separator = " | " ) {
		$links = apply_filters( 'tpress_dashboard_main_links', array() );
	
		do_action( 'tpress_dashboard_before_main_links', $this );
		
		echo "<$container class='main-links subsubsub'>";
		
		$is_last = count( $links ) - 1;
		foreach ( $links as $link ) {			
			echo sprintf( $before_item, $link['class'] );
			echo '<a href="' . $link[ 'url' ] . '">' . $link[ 'title' ] . '</a>';
			
			if ( $is_last>0 ) echo $separator;
			
			echo $after_item;
			
			--$is_last;
		}
		
		echo "</$container>";
		
		do_action( 'tpress_dashboard_after_main_links', $this );
	}
	
	/**
	 * Render the panels that got registered by other plugins
	 */
	public function renderPanels() {	
		do_action( 'tpress_dashboard_before_panels', $this );
		
		foreach ( $this->panels as $id => $panel ) {
			do_action( 'tpress_dashboard_before_panel', $this, $panel );
		
			echo "<div id='dahsboard-panel-$id'>" . $panel->render() . "</div>";
			
			do_action( 'tpress_dashboard_after_panel', $this, $panel );
		}
		
		do_action( 'tpress_dashboard_after_panels', $this );
	}
	
	/**
	 * Add a panel to the dashboard. This function is meant to be called during the execution of the action 
	 * 'tpress_dashboard_register_panels'. That action takes a single parameter which is the instance of this current
	 * dashboard view.
	 *
	 * @see http://wpseek.com/wp_add_dashboard_widget/
	 */
	public function addDashboardPanel( $panel ) {
		if ( !( $panel instanceof TPressPanel ) ) {
			_doing_it_wrong( 
					__FUNCTION__, 
					__( 'TPressSettingsView only accepts TPressPanel implementations as panels', 'tpress' ),
					'1.0.0' );
			return;
		}  else if ( isset( $this->panels[ $panel->getSlug() ] ) ) {
			_doing_it_wrong( 
					__FUNCTION__, 
					sprintf( 
							__( 'A panel with the slug %s is already registered', 'tpress' ),
							$panel->getSlug()),
					'1.0.0' );
			return;
		}
		
		$this->panels[] = $panel;
	}
	
	/** Instance variables *******************************************************************************************/
	
	private $panels;
}

endif; // interface_exists