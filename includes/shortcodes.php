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
				'post_id'            => null,
				'formatted'          => true,
				'decimals'           => 0,
				'round'              => false,
				'round_half'         => false,
				'page_count_divisor' => 1800,
				'text'               => $this->postStatsTemplate,
				'type'               => 'character-count',
			),
			$attributes
		);

		
		$formatted = filter_var( $attributes['formatted'], FILTER_VALIDATE_BOOLEAN );
		$decimals = abs( intval( $attributes['decimals'] ) );
		$round = filter_var( $attributes['round'], FILTER_VALIDATE_BOOLEAN );
		$roundHalf = filter_var( $attributes['round_half'], FILTER_VALIDATE_BOOLEAN );
		$pageCountDivisor = abs( intval( $attributes['page_count_divisor'] ) );
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
			
			$customPageCount = $characterCount / (float)$pageCountDivisor;
		}
		else
			return __( 'Keine Zeichenanzahl für den Beitrag gefunden.', WPVGW_TEXT_DOMAIN );


		
		switch ( $type ) {
			case 'standard-page-count':
				
				if ( $formatted )
					return esc_html( number_format_i18n( $this->round( $standardPageCount, $decimals, $roundHalf ), $decimals ) );

				return $round ?
					$this->round( $standardPageCount, $decimals, $roundHalf ) :
					$standardPageCount;
			case 'custom-page-count' :
				
				if ( $formatted )
					return esc_html( number_format_i18n( $this->round( $customPageCount, $decimals, $roundHalf ), $decimals ) );

				return $round ?
					$this->round( $customPageCount, $decimals, $roundHalf ) :
					$customPageCount;
			case 'text':
				
				if ( $formatted )
					return esc_html(
						sprintf(
							$text,
							number_format_i18n( $characterCount ),
							number_format_i18n( $this->round( $standardPageCount, $decimals, $roundHalf ), $decimals ),
							number_format_i18n( $this->round( $customPageCount, $decimals, $roundHalf ), $decimals )
						)
					);

				return $round ?
					esc_html( sprintf( $text, $this->round( $characterCount, $decimals, $roundHalf ), $this->round( $standardPageCount, $decimals, $roundHalf ), $this->round( $customPageCount, $decimals, $roundHalf ) ) ) :
					esc_html( sprintf( $text, $characterCount, $standardPageCount, $customPageCount ) );
			default: 
				
				if ( $formatted )
					return esc_html( number_format_i18n( $characterCount ) );

				return $characterCount;
		}
	}


	
	private function round( $number, $number_of_decimals, $round_half = false ) {
		
		if ( !$round_half || $number_of_decimals <= 0 )
			return round( $number, $number_of_decimals );

		
		$number = round( $number, $number_of_decimals + 1 );

		
		$decimalShift = pow( 10, $number_of_decimals + 1 );

		
		$shiftedNumber = (int)( $number * $decimalShift );

		
		$last2Decimals = $shiftedNumber % 100;

		
		$removedDecimals = $shiftedNumber - $last2Decimals;

		
		if ( $last2Decimals <= 24 )
			$roundedNumber = $removedDecimals;
		elseif ( $last2Decimals <= 74 )
			$roundedNumber = $removedDecimals + 50;
		else
			$roundedNumber = $removedDecimals + 100;

		
		return $roundedNumber / $decimalShift;
	}

}