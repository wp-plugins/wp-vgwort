<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */



class WPVGW_PostsExtras {

	
	private $postExtrasTableName;
	
	private $markersManager;


	
	public function get_post_extras_table_name() {
		return $this->postExtrasTableName;
	}


	
	public function __construct( $post_extras_table_name, WPVGW_MarkersManager $markers_manager ) {
		$this->postExtrasTableName = $post_extras_table_name;
		$this->markersManager = $markers_manager;
	}


	
	private function make_post_extras_typesafe( array &$post_extras ) {
		
		$post_extras['post_id'] = (int)$post_extras['post_id'];
		$post_extras['character_count'] = (int)$post_extras['character_count'];
	}

	
	public function  get_post_extras_from_db( $post_id ) {
		
		global $wpdb;

		
		if ( !is_int( $post_id ) )
			return false;

		$formatLiteral = WPVGW_Helper::get_format_literal( $post_id );

		
		$postExtras = $wpdb->get_row( WPVGW_Helper::prepare_with_null(
				"SELECT * FROM $this->postExtrasTableName WHERE post_id = $formatLiteral LIMIT 1",
				$post_id
			),
			ARRAY_A
		);

		if ( $wpdb->last_error !== '' )
			WPVGW_Helper::throw_database_exception();

		
		if ( $postExtras === null )
			return false;

		
		$this->make_post_extras_typesafe( $postExtras );

		return $postExtras;
	}

	
	public function insert_update_post_extras_in_db( array $new_post_extras ) {
		
		global $wpdb;

		
		$post_id = array_key_exists( 'post_id', $new_post_extras ) ? $new_post_extras['post_id'] : null;

		if ( !( is_int( $post_id ) && $post_id >= 0 ) )
			throw new Exception( 'Post ID must be specified and not be null.' );

		$columnNames = WPVGW_Helper::implode_keys( ', ', $new_post_extras );
		$columnNamesOnDuplicate = WPVGW_Helper::sql_columns_on_duplicate( $new_post_extras );
		$columnValues = WPVGW_Helper::sql_values( $new_post_extras );

		
		$success = $wpdb->query( WPVGW_Helper::prepare_with_null(
				"INSERT INTO $this->postExtrasTableName ($columnNames) VALUES ($columnValues) ON DUPLICATE KEY UPDATE $columnNamesOnDuplicate",
				array_values( $new_post_extras )
			)
		);

		if ( $success === false )
			WPVGW_Helper::throw_database_exception();
	}

	
	public function delete_post_extra( $post_id ) {
		
		global $wpdb;

		
		if ( !( is_int( $post_id ) && $post_id >= 0 ) )
			return false;

		
		$successOrDeletedRows = $wpdb->query( WPVGW_Helper::prepare_with_null(
				"DELETE FROM $this->postExtrasTableName WHERE post_id = %d LIMIT 1",
				$post_id
			)
		);

		if ( $successOrDeletedRows === false )
			WPVGW_Helper::throw_database_exception();

		
		return ( $successOrDeletedRows >= 1 );
	}


	
	public function recalculate_all_post_character_count_in_db() {
		$postsExtrasFillStats = new WPVGW_RecalculatePostCharacterCountStats();

		$allowedPostTypes = $this->markersManager->get_allowed_post_types();

		
		if ( !empty ( $allowedPostTypes ) ) {
			
			$postQuery = new WPVGW_Uncached_WP_Query(
				array(
					'post_status' => $this->markersManager->get_allowed_post_statuses(),
					'post_type'   => $allowedPostTypes,
				)
			);

			
			while ( $postQuery->has_post() ) {
				$post = $postQuery->get_post();

				$this->recalculate_post_character_count_in_db( $post );

				$postsExtrasFillStats->numberOfPostExtrasUpdates++;
			}
		}

		return $postsExtrasFillStats;
	}

	
	public function recalculate_post_character_count_in_db( $post ) {
		
		$characterCount = $this->markersManager->calculate_character_count( $post->post_title, $post->post_content, $post->post_excerpt );

		
		$this->insert_update_post_extras_in_db(
			array(
				'post_id'         => $post->ID,
				'character_count' => $characterCount,
			)
		);
	}
}



class WPVGW_RecalculatePostCharacterCountStats {
	
	public $numberOfPostExtrasUpdates = 0;
}