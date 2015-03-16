<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */



class WPVGW_Shortcodes {
	
	private static $instance = null;
	
	private $isInitialized = false;

	
	private $markersManager = null;
	
	private $cache = null;

	
	private $postStatsTemplate;


	
	public static function get_instance() {
		if ( self::$instance === null )
			self::$instance = new WPVGW_Shortcodes();

		return self::$instance;
	}


	
	public function init( WPVGW_MarkersManager $markers_manager, WPVGW_Cache $cache, $post_stats_template ) {

		
		if ( $this->isInitialized )
			throw new Exception( 'Class already initialized.' );

		$this->markersManager = $markers_manager;
		$this->cache = $cache;
		$this->postStatsTemplate = $post_stats_template;

		
		add_shortcode( 'pvgw_post_stats', array( $this, 'shortcode_post_stats' ) );

		
		$this->isInitialized = true;
	}

	
	public function shortcode_post_stats( $attributes, $content = null ) {
		
		$attributes = shortcode_atts(
			array(
				'post_id'   => null,
				'formatted' => true,
				'decimals'  => 0,
				'text'      => $this->postStatsTemplate,
				'type'      => 'character-count',
			),
			$attributes
		);

		
		$formatted = filter_var( $attributes['formatted'], FILTER_VALIDATE_BOOLEAN );
		$decimals = intval( $attributes['decimals'] );
		$text = $attributes['text'];
		$type = $attributes['type'];

		
		if ( $attributes['post_id'] === null )
			$post = get_post();
		else
			$post = get_post( ( intval( $attributes['post_id'] ) ) );


		
		if ( $post === null )
			return __( 'Die ID des Beitrags ist ungültig.', WPVGW_TEXT_DOMAIN );


		
		if ( !$this->markersManager->is_post_type_allowed( $post->post_type ) ||
			!$this->markersManager->is_user_allowed( intval( $post->post_author ) )
		)
			return __( 'Beitrags-Autor oder Beitrags-Typ nicht zugelassen.', WPVGW_TEXT_DOMAIN );


		
		$postExtras = $this->cache->get_post_extras( $post->ID );

		
		if ( $postExtras ) {
			
			$characterCount = $postExtras['character_count'];
			
			$standardPageCount = $characterCount / 1500.0;
		}
		else
			return __( 'Keine Zeichenanzahl für den Beitrag gefunden.', WPVGW_TEXT_DOMAIN );


		
		switch ( $type ) {
			case 'standard-page-count':
				
				if ( $formatted )
					return esc_html( number_format_i18n( round( $standardPageCount, $decimals ), $decimals ) );

				return $standardPageCount;
			case 'text':
				
				if ( $formatted )
					return esc_html(
						sprintf(
							$text,
							number_format_i18n( $characterCount ),
							number_format_i18n( round( $standardPageCount, $decimals ), $decimals )
						)
					);

				return esc_html( sprintf( $text, $characterCount, $standardPageCount ) );
			default: 
				
				if ( $formatted )
					return esc_html( number_format_i18n( $characterCount ) );

				return $characterCount;
		}
	}

}