<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressViewDispatcherPlugin' ) ) :

require_once( 'TPressPluginBase.php' );
require_once( 'TPressView.php' );

/**
 * This class provides plugins running in the admin interface some functionnality to dispatch view requests to the 
 * appropriate user interface.
 */
abstract class TPressViewDispatcherPlugin extends TPressPluginBase {
	
	/** TPressPluginBase overrides ***********************************************************************************/
	
	// @Override
	public function onPluginRegistered($tpress) {
		parent::onPluginRegistered($tpress);
			
		$this->default_view = null;		
		$this->views = array();
		
		add_action('tpress_admin_menu', 	array(&$this, 'addAdminMenu'));  
		add_action('tpress_admin_scripts', 	array(&$this, 'enqueueScripts'));  
		add_action('tpress_admin_styles', 	array(&$this, 'enqueueStyles'));
	}
	
	/** General Methods **********************************************************************************************/
		
	/**
	 * Add the administration menus
	 */
	public function addAdminMenu() {
		foreach ( $this->views as $view ) {
			$view->addAdminMenu( $this );
		}
	}
		
	/**
	 * Get the slug of the dispatcher itself
	 */
	abstract public function getDispatcherSlug();
	
	/** View Methods *************************************************************************************************/
    
	public function addViewSubMenu( $view, $page_title, $menu_title, $capability ) {
		add_submenu_page(
				$this->getDispatcherSlug(),
				$page_title, $menu_title, $capability,
				$view->getSlug(), 
				array (&$this, 'onShowView')
			);
	}
	
	/**
	 * Get the URL to show a given view.
	 *
	 * @param $view mixed The view we want the URL of. This can either be the TPressView directly or a string 
	 * representing its slug. If that parameter is not set, the function returns the URL of the default view for the 
	 * dispatcher.
	 */
	public function getViewUrl( $view = null ) {
		if ( isset( $view ) ) {
			if ( $view instanceof TPressView ) {
				return admin_url( 'admin.php?page=' . $this->getDispatcherSlug() . '&view=' . $view->getSlug() ); 
			} else {
				return admin_url( 'admin.php?page=' . $this->getDispatcherSlug() . '&view=' . $view ); 
			}
		} 
		
		return admin_url( 'admin.php?page=' . $this->getDispatcherSlug() );
	}
	
    /**
    * Function called on a menu click to display the appropriate settings view
    */
    public function onShowView() {   
    	$current_view 	= $this->getView();
		
		// Handle POST data if necessary
		if ( !empty($_POST) && $current_view->checkAdminReferer( $_POST ) ) {
			$current_view->onPostDataSubmitted( $_POST );
		}		
		
		// Display the view
		$current_view->render();
    }
    
	/**
	 * Subclasses should call this function to register their views
	 */
	public function registerView( $view ) {
		if ( !( $view instanceof TPressView ) ) {
			_doing_it_wrong( 
					__FUNCTION__, 
					__( 'TPressViewDispatcherPlugin only accepts TPressView implementations as views', 'tpress' ),
					'3.2' );
			return;
		}  else if ( isset( $this->views[ $view->getSlug() ] ) ) {
			_doing_it_wrong( 
					__FUNCTION__, 
					sprintf( 
							__( 'A view with the slug %s is already registered', 'tpress' ),
							$view->getSlug()),
					'3.2' );
			return;
		}
		
		if ( !isset( $this->default_view ) ) $this->default_view = $view;
		
		$this->views[ $view->getSlug() ] = $view;
	}
	
	/**
	 * Give a chance to view to register their scripts
	 */
	public function enqueueScripts() {	
		foreach ( $this->views as $view ) {
			$view->enqueueScripts();
		}		
	}	
	
	/**
	 * Give a chance to view to register their styles
	 */
	public function enqueueStyles() {	
		foreach ( $this->views as $view ) {
			$view->enqueueStyles();
		}			
	}	
	
    /**
    * Get the views registered in the dispatcher
    */
    public function getRegisteredViews() {
		return $this->views;
	}
	
    /**
    * Get the view object corresponding to the page we want to show
    */
    private function getView() {
    	$requested_slug = $this->getPageParameter();
            
		foreach ( $this->views as $slug => $view ) {
			if ( $slug === $requested_slug ) return $view;
		}
			
        return $this->default_view;
    }
        	
	/**
	 * Get the page from the GET or POST values
	 */
	private function getPageParameter() {
		if ( isset( $_GET['page'] ) && $_GET['page']==$this->getDispatcherSlug() && isset( $_GET['view'] ) ) {
			return $_GET['view'];
		} else if ( isset( $_GET['page'] ) ) {
			return $_GET['page'];
		} else if ( isset( $_POST['page'] ) && $_POST['page']==$this->getDispatcherSlug() && isset( $_POST['view'] ) ) {
			return $_POST['view'];
		} else if ( isset( $_POST['page'] ) ) {
			return $_POST['page'];
		} 
		
		return null;
	}
	
	/** Instance variables *******************************************************************************************/
	
	private $default_view;
	private $views;
}

endif; // class_exists