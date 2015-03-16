<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */



class WPVGW_Cache {

	
	private $markersManager;
	
	private $postsExtras;

	
	private $markersCache = array();
	
	private $postExtrasCache = array();


	
	public function __construct( WPVGW_MarkersManager $markers_manager, WPVGW_PostsExtras $post_extras ) {
		$this->markersManager = $markers_manager;
		$this->postsExtras = $post_extras;
	}


	
	public function get_marker( $post_id ) {
		
		if ( array_key_exists( $post_id, $this->markersCache ) )
			return $this->markersCache[$post_id];

		
		$marker = $this->markersManager->get_marker_from_db( $post_id, 'post_id' );

		
		$this->markersCache[$post_id] = $marker;

		return $marker;
	}

	
	public function get_post_extras( $post_id ) {
		
		if ( array_key_exists( $post_id, $this->postExtrasCache ) )
			return $this->postExtrasCache[$post_id];

		
		$postExtras = $this->postsExtras->get_post_extras_from_db( $post_id );

		
		$this->postExtrasCache[$post_id] = $postExtras;

		return $postExtras;
	}

}