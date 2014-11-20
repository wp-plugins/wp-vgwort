<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: 2014 Ronny Harbich
 * License: GPLv2 or later
 */
 

/**
 * Base class of a view.
 */
abstract class WPVGW_ViewBase {
	/**
	 * @var bool Whether the view is initialized.
	 */
	private $isInit = false;
	/**
	 * @var WPVGW_MarkersManager The markers manager.
	 */
	protected $markersManager;
	/**
	 * @var WPVGW_PostsExtras The posts extras.
	 */
	protected $postsExtras;
	/**
	 * @var WPVGW_Options The options.
	 */
	protected $options;
	/**
	 * @var array The array of JavaScript data for this view:
	 * array('file' => 'file.js', 'slug' => 'slug', 'dependencies' => array( 'jquery' ), 'localize' => array('object_name' => 'name of the JavaScript object', 'data' => array())).
	 */
	protected $javaScripts = array();


	/**
	 * Gets the JavaScript data for the view.
	 *
	 * @return array An array of javascript data:
	 * array('file' => 'file.js', 'slug' => 'slug', 'dependencies' => array( 'jquery' ), 'localize' => array('object_name' => 'name of the JavaScript object', 'data' => array())).
	 */
	public function get_javascripts() {
		return $this->javaScripts;
	}


	/**
	 * Gets whether the view is initialized.
	 *
	 * @return bool True if the view is initialized.
	 */
	public function is_init() {
		return $this->isInit;
	}


	/**
	 * Creates a new instance of {@link WPVGW_ViewBase}.
	 *
	 * @param WPVGW_MarkersManager $markers_manager A markers manager.
	 * @param WPVGW_Options $options The options.
	 * @param WPVGW_PostsExtras|null $posts_extras The posts extras.
	 */
	protected function __construct( WPVGW_MarkersManager $markers_manager, WPVGW_Options $options, WPVGW_PostsExtras $posts_extras = null ) {
		$this->markersManager = $markers_manager;
		$this->options = $options;
		$this->postsExtras = $posts_extras;
	}

	/**
	 * Initializes the base view. This function must be called before using the view.
	 * Inheritors must call {@link init_base()} in this function.
	 */
	public abstract function init();


	/**
	 * Initializes the base view. This function must be called before using the view.
	 * Inheritors must call this function in {@link init()}.
	 *
	 * @param array $javascript An array of javascript data for this view: array('file' => 'file.js', 'slug' => 'slug', 'dependencies' => array( 'jquery' )).
	 */
	protected function init_base( array $javascript ) {
		// view is initialized
		$this->isInit = true;

		$this->javaScripts = $javascript;

		// add javascript to view base javascript
		/*$this->javaScripts = array_merge( array(
				array(
					'file'         => 'views/main.js',
					'slug'         => 'view-base',
					'dependencies' => array( 'jquery' )
				)
			),
			$javascript
		);*/
	}

}