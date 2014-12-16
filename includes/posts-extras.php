<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: 2014 Ronny Harbich
 * License: GPLv2 or later
 */


/**
 * Manages extra data for WordPress posts in a separate database table.
 */
class WPVGW_PostsExtras {

	/**
	 * @var string The database name of the posts extras table.
	 */
	private $postExtrasTableName;
	/**
	 * @var WPVGW_MarkersManager A markers manager.
	 */
	private $markersManager;


	/**
	 * Gets the database name of the posts extras table.
	 *
	 * @return string The markers table name.
	 */
	public function get_post_extras_table_name() {
		return $this->postExtrasTableName;
	}


	/**
	 * Creates a new instance of {@link WPVGW_PostsExtras}.
	 *
	 * @param string $post_extras_table_name The database name of the posts extras table.
	 * @param WPVGW_MarkersManager $markers_manager A markers manager instance.
	 */
	public function __construct( $post_extras_table_name, WPVGW_MarkersManager $markers_manager ) {
		$this->postExtrasTableName = $post_extras_table_name;
		$this->markersManager = $markers_manager;
	}


	/**
	 * Makes post extras typesafe, i. e., converts each data to the correct data type.
	 *
	 * @param array $post_extras A post extras reference. The post extras are modified by this function.
	 */
	private function make_post_extras_typesafe( array &$post_extras ) {
		// cast post extras data to the correct types
		$post_extras['post_id'] = (int)$post_extras['post_id'];
		$post_extras['character_count'] = (int)$post_extras['character_count'];
	}

	/**
	 * Gets the post extras for a specified post from the database.
	 *
	 * @param int $post_id The ID of the post to get the extras for.
	 *
	 * @return bool|array The post extras or false if the post was not found.
	 * @throws Exception Thrown if a database error occurred.
	 */
	public function  get_post_extras_from_db( $post_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		// $post_id has to be an integer
		if ( !is_int( $post_id ) )
			return false;

		$formatLiteral = WPVGW_Helper::get_format_literal( $post_id );

		// get post extras by post ID from the database
		$postExtras = $wpdb->get_row( WPVGW_Helper::prepare_with_null(
				"SELECT * FROM $this->postExtrasTableName WHERE post_id = $formatLiteral LIMIT 1",
				$post_id
			),
			ARRAY_A
		);

		if ( $wpdb->last_error !== '' )
			WPVGW_Helper::throw_database_exception();

		// return false if no row was retrieved
		if ( $postExtras === null )
			return false;

		// cast post extras data to the correct types
		$this->make_post_extras_typesafe( $postExtras );

		return $postExtras;
	}

	/**
	 * Inserts new post extras into the database or updates the post extras if the post extras already exists.
	 *
	 * @param array $new_post_extras New post extras. The 'post_id' must be specified and be a non-negative integer.
	 *
	 * @throws Exception Thrown if one of the arguments are invalid. Thrown if a database error occurred.
	 */
	public function insert_update_post_extras_in_db( array $new_post_extras ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		// get special column values
		$post_id = array_key_exists( 'post_id', $new_post_extras ) ? $new_post_extras['post_id'] : null;

		if ( !( is_int( $post_id ) && $post_id >= 0 ) )
			throw new Exception( 'Post ID must be specified and not be null.' );

		$columnNames = WPVGW_Helper::implode_keys( ', ', $new_post_extras );
		$columnNamesOnDuplicate = WPVGW_Helper::sql_columns_on_duplicate( $new_post_extras );
		$columnValues = WPVGW_Helper::sql_values( $new_post_extras );

		// insert new marker into markers table
		$success = $wpdb->query( WPVGW_Helper::prepare_with_null(
				"INSERT INTO $this->postExtrasTableName ($columnNames) VALUES ($columnValues) ON DUPLICATE KEY UPDATE $columnNamesOnDuplicate",
				array_values( $new_post_extras )
			)
		);

		if ( $success === false )
			WPVGW_Helper::throw_database_exception();
	}

	/**
	 * Deletes post extras for a specified post from database.
	 *
	 * @param int $post_id A non-negative post ID.
	 *
	 * @return bool True if the the post extras were deleted, otherwise false.
	 * @throws Exception Thrown if a database error occurred.
	 */
	public function delete_post_extra( $post_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		// post ID must be a non-negative integer
		if ( !( is_int( $post_id ) && $post_id >= 0 ) )
			return false;

		// delete post extras
		$successOrDeletedRows = $wpdb->query( WPVGW_Helper::prepare_with_null(
				"DELETE FROM $this->postExtrasTableName WHERE post_id = %d LIMIT 1",
				$post_id
			)
		);

		if ( $successOrDeletedRows === false )
			WPVGW_Helper::throw_database_exception();

		// true if at least one marker was deleted
		return ( $successOrDeletedRows >= 1 );
	}


	/**
	 * For each post the post’s character count will be recalculated and stored as post extra into the database.
	 * Only allowed post statuses and allowed post types will be considered.
	 *
	 * @return WPVGW_RecalculatePostCharacterCountStats Stats.
	 * @throws Exception Thrown if a database error occurred.
	 */
	public function recalculate_all_post_character_count_in_db() {
		$postsExtrasFillStats = new WPVGW_RecalculatePostCharacterCountStats();

		$allowedPostTypes = $this->markersManager->get_allowed_post_types();

		// allowed post types must not be empty
		if ( !empty ( $allowedPostTypes ) ) {
			// get all posts
			$postQuery = new WPVGW_Uncached_WP_Query(
				array(
					'post_status' => $this->markersManager->get_allowed_post_statuses(),
					'post_type'   => $allowedPostTypes,
				)
			);

			// iterate posts
			while ( $postQuery->has_post() ) {
				$post = $postQuery->get_post();

				$this->recalculate_post_character_count_in_db( $post );

				$postsExtrasFillStats->numberOfPostExtrasUpdates++;
			}
		}

		return $postsExtrasFillStats;
	}

	/**
	 * The character count of a specified post will be recalculated and stored as post extra into the database.
	 *
	 * @param WP_Post $post A post.
	 *
	 * @throws Exception Thrown if a database error occurred.
	 */
	public function recalculate_post_character_count_in_db( $post ) {
		// count post characters
		$characterCount = $this->markersManager->calculate_character_count( $post->post_title, $post->post_content );

		// insert or update post extras
		$this->insert_update_post_extras_in_db(
			array(
				'post_id'         => $post->ID,
				'character_count' => $characterCount,
			)
		);
	}
}


/**
 * Holds constants that will be returned if post character counts were recalculated.
 */
class WPVGW_RecalculatePostCharacterCountStats {
	/**
	 * @var int The number of post for which the post extras were updated.
	 */
	public $numberOfPostExtrasUpdates = 0;
}