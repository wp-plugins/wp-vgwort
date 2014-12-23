<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */



class WPVGW_AdminViewsManger {

	
	private $views;
	
	private $currentViewSlug;
	
	private $currentView = null;


	
	public static function get_default_view_slug() {
		return WPVGW_MarkersAdminView::get_slug_static();
	}


	
	public function get_views() {
		return $this->views;
	}

	
	public function get_current_view() {
		return $this->currentView;
	}

	
	public function is_init() {
		return $this->currentView !== null;
	}


	
	public function __construct( WPVGW_MarkersManager $markers_manager, WPVGW_PostsExtras $posts_extras, WPVGW_Options $options ) {
		$this->markersManager = $markers_manager;
		$this->postsExtras = $posts_extras;
		$this->options = $options;


		
		$this->views = array(
			
			WPVGW_MarkersAdminView::get_slug_static()       => new WPVGW_MarkersAdminView( $markers_manager, $posts_extras, $options ),
			
			WPVGW_ImportAdminView::get_slug_static()        => new WPVGW_ImportAdminView( $markers_manager, $posts_extras, $options ),
			
			WPVGW_ConfigurationAdminView::get_slug_static() => new WPVGW_ConfigurationAdminView( $markers_manager, $posts_extras, $options ),
			
			WPVGW_OperationsAdminView::get_slug_static()    => new WPVGW_OperationsAdminView( $markers_manager, $posts_extras, $options ),
			
			WPVGW_DataPrivacyAdminView::get_slug_static()   => new WPVGW_DataPrivacyAdminView( $markers_manager, $posts_extras, $options ),
			
			WPVGW_SupportAdminView::get_slug_static()       => new WPVGW_SupportAdminView( $markers_manager, $posts_extras, $options ),
			
			WPVGW_AboutAdminView::get_slug_static()         => new WPVGW_AboutAdminView( $markers_manager, $posts_extras, $options ),
		);
	}

	
	public function init( $current_view_slug ) {
		
		$this->currentViewSlug = $current_view_slug;

		
		if ( $this->currentViewSlug === null || !array_key_exists( $this->currentViewSlug, $this->views ) )
			
			$this->currentViewSlug = key( $this->views );

		
		$this->currentView = $this->views[$this->currentViewSlug];
		
		$this->currentView->init();
	}

	
	public static function create_admin_view_url( $view_slug = null ) {
		return admin_url( 'admin.php?page=' . WPVGW . '-' . ( $view_slug === null ? self::get_default_view_slug() : $view_slug ) );
	}

}



