<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */
 


abstract class WPVGW_ViewBase {
	
	private $isInit = false;
	
	protected $markersManager;
	
	protected $postsExtras;
	
	protected $options;
	
	protected $javaScripts = array();


	
	public function get_javascripts() {
		return $this->javaScripts;
	}


	
	public function is_init() {
		return $this->isInit;
	}


	
	protected function __construct( WPVGW_MarkersManager $markers_manager, WPVGW_Options $options, WPVGW_PostsExtras $posts_extras = null ) {
		$this->markersManager = $markers_manager;
		$this->options = $options;
		$this->postsExtras = $posts_extras;
	}

	
	public abstract function init();


	
	protected function init_base( array $javascript ) {
		
		$this->isInit = true;

		$this->javaScripts = $javascript;

		
		
	}

}