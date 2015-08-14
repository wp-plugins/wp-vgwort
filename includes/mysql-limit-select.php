<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */



class WPVGW_MySqlLimitSelect {

	
	private $query = null;

	
	private $countLimit = 1000;

	
	private $currentOffset = 0;


	
	public function __construct( $query, $countLimit = 1000) {
		$this->query = $query;
		$this->countLimit = $countLimit;
	}


	
	public function next_results( $output = OBJECT ) {
		
		global $wpdb;

		$limitQuery = sprintf("%s LIMIT %s, %s", $this->query, $this->currentOffset, $this->countLimit);

		if ( $wpdb->last_error !== '' )
			WPVGW_Helper::throw_database_exception();

		$this->currentOffset += $this->countLimit;

		return $wpdb->get_results($limitQuery, $output);
	}

}