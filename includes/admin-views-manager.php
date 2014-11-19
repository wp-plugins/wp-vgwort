<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: 2014 Ronny Harbich
 * License: GPLv2 or later
 */


/**
 * Represents the base for the administration views.
 */
class WPVGW_AdminViewsManger {

	/**
	 * @var array The array of all admin views.
	 */
	private $views;
	/**
	 * @var string The slug for the current view.
	 */
	private $currentViewSlug;
	/**
	 * @var WPVGW_AdminViewBase The current admin view.
	 */
	private $currentView = null;


	/**
	 * Gets the slug of the default view.
	 *
	 * @return string The slug of the default view.
	 */
	public static function get_default_view_slug() {
		return WPVGW_MarkersAdminView::get_slug_static();
	}


	/**
	 * Gets the views.
	 *
	 * @return array The views. Array ('slug' => WPVGW_AdminViewBase)
	 */
	public function get_views() {
		return $this->views;
	}

	/**
	 * Gets the current admin view.
	 *
	 * @return WPVGW_AdminViewBase The current admin view.
	 */
	public function get_current_view() {
		return $this->currentView;
	}

	/**
	 * Gets whether the admin views manager is initialized.
	 *
	 * @return bool True if the admin views manager is initialized.
	 */
	public function is_init() {
		return $this->currentView !== null;
	}


	/**
	 * Creates a new instance of {@link WPVGW_AdminView}.
	 *
	 * @param WPVGW_MarkersManager $markers_manager A markers manager.
	 * @param WPVGW_PostsExtras $posts_extras The posts extras.
	 * @param WPVGW_Options $options The options.
	 */
	public function __construct( WPVGW_MarkersManager $markers_manager, WPVGW_PostsExtras $posts_extras, WPVGW_Options $options ) {
		$this->markersManager = $markers_manager;
		$this->postsExtras = $posts_extras;
		$this->options = $options;


		// array of all admin pages
		$this->views = array(
			// markers view
			WPVGW_MarkersAdminView::get_slug_static()       => new WPVGW_MarkersAdminView( $markers_manager, $posts_extras, $options ),
			// import markers view
			WPVGW_ImportAdminView::get_slug_static()        => new WPVGW_ImportAdminView( $markers_manager, $posts_extras, $options ),
			// configuration view
			WPVGW_ConfigurationAdminView::get_slug_static() => new WPVGW_ConfigurationAdminView( $markers_manager, $posts_extras, $options ),
			// operations view
			WPVGW_OperationsAdminView::get_slug_static()    => new WPVGW_OperationsAdminView( $markers_manager, $posts_extras, $options ),
			// data privacy view
			WPVGW_DataPrivacyAdminView::get_slug_static()   => new WPVGW_DataPrivacyAdminView( $markers_manager, $posts_extras, $options ),
			// support view
			WPVGW_SupportAdminView::get_slug_static()       => new WPVGW_SupportAdminView( $markers_manager, $posts_extras, $options ),
			// about view
			WPVGW_AboutAdminView::get_slug_static()         => new WPVGW_AboutAdminView( $markers_manager, $posts_extras, $options ),
		);
	}

	/**
	 * Initialises the views manager. Needs to be called before calling methods of this class.
	 *
	 * @param string $current_view_slug A name of a registered view ({@link views}). This view will be initialized.
	 */
	public function init( $current_view_slug ) {
		// set current view slug
		$this->currentViewSlug = $current_view_slug;

		// validate view slug
		if ( $this->currentViewSlug === null || !array_key_exists( $this->currentViewSlug, $this->views ) )
			// get first key (view slug) of the views array
			$this->currentViewSlug = key( $this->views );

		// get view by current view slug
		$this->currentView = $this->views[$this->currentViewSlug];
		// initialize current view
		$this->currentView->init();
	}

	/**
	 * Creates an admin view URL.
	 *
	 * @param null|string $view_slug If null, the default view is meant, otherwise the actual view slug as string. The view slug will not be validated!
	 *
	 * @return string The unescaped URL to the specific admin view.
	 */
	public static function create_admin_view_url( $view_slug = null ) {
		return admin_url( 'admin.php?page=' . WPVGW . '-' . ( $view_slug === null ? self::get_default_view_slug() : $view_slug ) );
	}

}



