<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: 2014 Ronny Harbich
 * License: GPLv2 or later
 */


/**
 * Manges VG WORT marker functionality. It manages a separat database table for markers.
 */
class WPVGW_MarkersManager {

	/**
	 * @var string The database name of the marker table.
	 */
	private $markersTableName;

	/**
	 * @var string[] The allowed WordPress user roles.
	 */
	private $allowedUserRoles;

	/**
	 * @var string[] The allowed WordPress post statuses.
	 */
	private $allowedPostStatuses = array( 'publish', 'pending', 'draft', 'future', 'private', 'trash' );

	/**
	 * @var string[] All WordPress post types.
	 */
	private $possiblePostTypes = null;
	/**
	 * @var string[] The allowed WordPress post types. A subset of {@link possiblePostTypes}.
	 */
	private $allowedPostTypes = null;


	/**
	 * Gets the database name of the marker table.
	 *
	 * @return string The markers table name.
	 */
	public function get_markers_table_name() {
		return $this->markersTableName;
	}


	/**
	 * Gets the allowed WordPress post statuses.
	 * Only post with these statuses can have markers.
	 *
	 * @return string[] The allowed WordPress post statuses.
	 */
	public function get_allowed_post_statuses() {
		return $this->allowedPostStatuses;
	}

	/**
	 * Gets the allowed WordPress post types.
	 * Only post of these types can have markers.
	 *
	 * @return string[] The allowed WordPress post types. A subset of {@link get_possible_post_types()}.
	 */
	public function get_allowed_post_types() {
		return $this->allowedPostTypes;
	}

	/**
	 * Sets the allowed WordPress post types. Only post of these types can have markers.
	 * Unknown post types will be removed.
	 *
	 * @param string[] $value An array of post types. Unknown post types will be removed.
	 */
	public function set_allowed_post_types( $value ) {
		$this->allowedPostTypes = $value;

		// remove unknown post types
		foreach ( $this->allowedPostTypes as $key => $allowedPostType ) {
			if ( !in_array( $allowedPostType, $this->possiblePostTypes, true ) )
				unset( $this->allowedPostTypes[$key] );
		}
	}

	/**
	 * Gets all WordPress post types.
	 *
	 * @return string[] All WordPress post types.
	 */
	public function get_possible_post_types() {
		return $this->possiblePostTypes;
	}


	/**
	 * Creates a new instance of {@link WPVGW_MarkersManager}.
	 *
	 * @param string $markers_table_name The database name of the marker table.
	 * @param string[] $allowed_user_roles The allowed WordPress user roles.
	 * @param string[] $allowed_post_types An array of post types. Only post of these types can have markers. Unknown post types will be removed.
	 */
	public function __construct( $markers_table_name, $allowed_user_roles, $allowed_post_types ) {
		$this->markersTableName = $markers_table_name;
		$this->allowedUserRoles = $allowed_user_roles;

		// get all possible post types from WordPress
		$this->possiblePostTypes = array_merge(
			array( 'post', 'page' ), // add post and page as post types
			array_values( get_post_types( array( 'public' => true, 'show_ui' => true, '_builtin' => false ) ) )
		);


		$this->set_allowed_post_types( $allowed_post_types );
	}


	/**
	 * Checks whether a specified post type is one of the WordPress’ post types.
	 *
	 * @param string $post_type The post type to check.
	 *
	 * @return bool True if $post_type is one of WordPress’ post types, otherwise false.
	 */
	public function is_post_type_possible( $post_type ) {
		return in_array( $post_type, $this->possiblePostTypes );
	}

	/**
	 * Checks whether a specified post type is one of the allowed post types.
	 *
	 * @param string $post_type The post type to check
	 *
	 * @return bool True if $post_type is one of the allowed post types, otherwise false.
	 */
	public function is_post_type_allowed( $post_type ) {
		return in_array( $post_type, $this->allowedPostTypes );
	}


	/**
	 * Checks whether a specified user ID is one of the allowed users.
	 *
	 * @param int|null $user_id An user id.
	 *
	 * @return bool True if $user_id is one of the allowed user IDs or null, otherwise false.
	 */
	public function is_user_allowed( $user_id ) {
		if( $user_id === null )
			return true;

		$user = get_userdata( $user_id );

		//  user not allowed if not found by WordPress
		if ( $user === false ) {
			return false;
		}
		else {
			// user allowed if user role is one of the allowed user roles
			return $this->is_user_role_allowed( $user->roles );
		}
	}

	/**
	 * Checks whether one or more specified user roles are one of the allowed user roles.
	 *
	 * @param string|string[] $roles An user role or an array of user roles.
	 *
	 * @return bool True if one of $roles is one of the allowed user roles, otherwise false.
	 */
	private function is_user_role_allowed( $roles ) {
		if ( is_array( $roles ) )
			return count( array_intersect( $this->allowedUserRoles, $roles ) ) > 0;

		return in_array( $roles, $this->allowedUserRoles );
	}


	/**
	 * Calculate the number of characters of a post content and title.
	 *
	 * @param string $post_content Textual content of a post.
	 * @param string $post_title The title of a post.
	 *
	 * @return int The number of characters of the post content.
	 */
	public function calculate_character_count( $post_title, $post_content ) {
		// replace <br> tags by new lines (\n)
		$post_content = preg_replace(
			'%<br\s*/?>%si',
			"\n",
			$post_content
		);

		// remove all HTML tags, but not the content between the tags;
		$post_content = strip_tags( $post_content );

		// remove whitespaces from the beginning and end
		$post_content = trim( $post_content );

		// remove shortcodes and whitespaces sequences
		$post_content = preg_replace( array(
				WPVGW_Helper::$captionShortcodeRegex, // remove caption shortcodes and its content
				WPVGW_Helper::$shortcodeRegex, // remove shortcodes, but not content between shortcodes; it is escaping aware
				'/\s{2,}/i' // remove sequences of 2 or more whitespaces
			),
			array(
				'',
				'',
				' '
			),
			$post_content
		);

		// convert html entities (e. g. &amp; to &)
		$post_content = html_entity_decode( $post_content );

		// return the number of characters of the cleaned post content
		return ( mb_strlen( $post_title ) + mb_strlen( $post_content ) );
	}

	/**
	 * Checks whether a specified character count of a post is sufficient for VG WORT markers, i. e., if the related post can have a marker.
	 *
	 * @param int $character_count The character count of a post.
	 * @param int $minimum_character_count The minimum character count that is necessary.
	 *
	 * @return bool True if character count is sufficient, otherwise false.
	 */
	public function is_character_count_sufficient( $character_count, $minimum_character_count ) {
		// WARNING: If you modify the calculation, you have to modify it in is_character_count_sufficient_sql() too!
		return $character_count >= $minimum_character_count;
	}

	/**
	 * Same as {@link is_character_count_sufficient()}, but return a SQL WHERE expression.
	 * This is need to do the check in a SQL query.
	 *
	 * @param int $character_count_column See {@link is_character_count_sufficient()}.
	 * @param int $minimum_character_count See {@link is_character_count_sufficient()}.
	 *
	 * @return string A SQL WHERE expression that does the same check like {@link is_character_count_sufficient()}.
	 */
	public function is_character_count_sufficient_sql( $character_count_column, $minimum_character_count ) {
		// WARNING: If you modify the calculation, you have to modify it in is_character_count_sufficient() too!
		// return SQL WHERE expression condition
		return $character_count_column . ' >= ' . $minimum_character_count;
	}

	/**
	 * Calculates the number of characters that are missing to for a specified character count of a post to be sufficient for VG WORT markers.
	 *
	 * @param int $character_count The character count of a post.
	 * @param int $minimum_character_count The minimum character count that is necessary.
	 *
	 * @return int The number of missing characters.
	 */
	public function calculate_missing_character_count( $character_count, $minimum_character_count ) {
		if ( $character_count < $minimum_character_count )
			return $minimum_character_count - $character_count;
		else
			return 0;
	}

	/**
	 * Checks if a specified key exists in a specified column in the database.
	 *
	 * @param mixed $key A key to check.
	 * @param string $column Name of the column.
	 *
	 * @return bool True if the key exists in the column, otherwise false.
	 * @throws Exception Thrown if a database error occurred.
	 */
	public function key_exists_in_db( $key, $column ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		if ( $key === null )
			return false;

		$formatLiteral = WPVGW_Helper::get_format_literal( $key );

		// test if key exists in column
		$exists = $wpdb->get_var( WPVGW_Helper::prepare_with_null(
				"SELECT EXISTS(SELECT 1 FROM $this->markersTableName WHERE $column = $formatLiteral LIMIT 1)",
				$key
			)
		);

		if ( $exists === null )
			WPVGW_Helper::throw_database_exception();

		return (bool)$exists;
	}

	/**
	 * Makes a marker typesafe, i. e., converts each data to the correct data type.
	 *
	 * @param array $marker A marker reference. The marker is modified by this function.
	 */
	private function make_marker_typesafe( array &$marker ) {
		// cast marker data to the correct data types
		$marker['id'] = (int)$marker['id'];
		$marker['post_id'] = $marker['post_id'] === null ? null : (int)$marker['post_id'];
		$marker['user_id'] = $marker['user_id'] === null ? null : (int)$marker['user_id'];
		$marker['is_marker_disabled'] = (bool)$marker['is_marker_disabled'];
		$marker['is_post_deleted'] = (bool)$marker['is_post_deleted'];
	}

	/**
	 * Retrieves a marker from the database.
	 *
	 * @param mixed $key A key to identify the marker.
	 * @param string $column The name of a database key column.
	 *
	 * @return bool|array The marker or false if the marker was not found.
	 * @throws Exception Thrown if a database error occurred.
	 */
	public function get_marker_from_db( $key, $column ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		// key should not be null
		if ( $key === null )
			return false;

		$formatLiteral = WPVGW_Helper::get_format_literal( $key );

		// get first row from specified column by value
		$marker = $wpdb->get_row( WPVGW_Helper::prepare_with_null(
				"SELECT * FROM $this->markersTableName WHERE $column = $formatLiteral LIMIT 1",
				$key
			),
			ARRAY_A
		);

		if ( $wpdb->last_error !== '' )
			WPVGW_Helper::throw_database_exception();

		// return false if no row/marker was returned
		if ( $marker === null )
			return false;

		// cast marker data to the correct types
		$this->make_marker_typesafe( $marker );

		return $marker;
	}

	/**
	 * Retrieves a marker that has not been added to a post for a specified user from the database.
	 *
	 * @param int|null $user_id A non-negative user id or null for arbitrary user.
	 *
	 * @return bool|mixed A free marker for the specified user id or false if no free marker was found.
	 * @throws Exception Thrown if a database error occurred.
	 */
	public function get_free_marker_from_db( $user_id = null ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		if ( $user_id !== null )
			// user id should be a non-negative integer
			if ( !is_int( $user_id ) || $user_id < 0 )
				return false;

		// compare operator differs for null user
		$compareOperator = ( $user_id === null ? 'IS' : '=' );

		// get first row from specified column by value
		$marker = $wpdb->get_row( WPVGW_Helper::prepare_with_null(
				"SELECT * FROM $this->markersTableName WHERE user_id $compareOperator %d AND post_id IS NULL AND is_post_deleted = 0 LIMIT 1",
				$user_id
			),
			ARRAY_A
		);

		if ( $wpdb->last_error !== '' )
			WPVGW_Helper::throw_database_exception();

		// return false if no row was returned
		if ( $marker === null )
			return false;

		// cast marker data to the correct types
		$this->make_marker_typesafe( $marker );

		return $marker;
	}

	/**
	 * Removes a post from a marker and resets the marker in the database, i. e., sets marker to enabled.

	 *
*@param int $id A non-negative id.
	 * @param string $id_type The type of the given ID. Has to be 'post' or 'marker'.
	 *
*@return bool True if post was removed from marker, otherwise false.
	 * @throws Exception Thrown if $id_type is invalid. Thrown if a database error occurred.
	 */
	public function remove_post_from_marker_in_db( $id, $id_type = 'post' ) {
		if ( $id === null || !is_int( $id ) || $id < 0 )
			throw new Exception( "ID must be a non-negative integer." );

		// get the correct key column
		switch ( $id_type ) {
			case 'post':
				$keyColumn = 'post_id';
				break;
			case 'marker':
				$keyColumn = 'id';
				break;
			default:
				throw new Exception( 'Invalid ID type.' );
				break;
		}

		// remove marker from post
		return $this->update_marker_in_db(
			$id,
			$keyColumn,
			array( // reset post specific properties
				'post_id'            => null,
				'is_marker_disabled' => false,
				'is_post_deleted'    => false,
				'deleted_post_title' => null,
			),
			null,
			array(),
			array( 'post_id' => null ) // negative conditions; post ID must not be null
		) == WPVGW_UpdateMarkerResults::Updated;
	}


	/**
	 * Checks if a marker behaves with integrity.
	 *
	 * @param string $key_column The name of the database column that identifies the marker.
	 * @param string $column A marker database column.
	 * @param mixed $value A value for the marker database column.
	 * @param array $oldMarker The marker to check.
	 *
	 * @return bool True if marker integrity is broken, otherwise false.
	 */
	private function is_marker_integrity_broken( $key_column, $column, $value, array $oldMarker ) {
		return $column != $key_column && $value !== null && $oldMarker[$column] !== $value && $this->key_exists_in_db( $value, $column );
	}

	/**
	 * Updates an existing marker in the database.
	 * The update is rejected if a specified user id is not the marker’s user id.
	 * Warning: The value in the column $key_column (which is $key) can be overwritten by $update_marker. In most cases this is unwanted.
	 *
	 * @param mixed $key A valid key that identifies the marker.
	 * @param string $key_column The name of the database column where the key is searched.
	 * @param array $update_marker An array of new marker data: column_name => value. Must not be empty.
	 * @param int|null $check_user_id A user ID or null if marker user should not be checked.
	 * @param array $conditions An array of conditions for the old marker that have to hold, otherwise the update is rejected. The array is: column_name => value.
	 * @param array $negativConditions An array of conditions for the old marker that must not hold, otherwise the update is rejected. The array is: column_name => value.
	 *
	 * @throws Exception Thrown if one of the arguments are invalid. Thrown if a database error occurred.
	 * @return int One of the constants defined in {@link WPVGW_UpdateMarkerResults}.
	 */
	public function update_marker_in_db( $key, $key_column, array $update_marker, $check_user_id = null, array $conditions = array(), array $negativConditions = array() ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		// throw exception if update marker is empty
		if ( count( $update_marker ) < 1 )
			throw new Exception( 'Too few elements in update marker.' );

		// throw exception if $key_column has an invalid column name
		if ( $key_column != 'id' && $key_column != 'post_id' && $key_column != 'public_marker' && $key_column != 'private_marker' )
			throw new Exception( 'Key column has an invalid column name.' );

		// get marker by key in a key column
		$oldMarker = $this->get_marker_from_db( $key, $key_column );

		// marker not found
		if ( $oldMarker === false )
			return WPVGW_UpdateMarkerResults::MarkerNotFound;


		// check conditions
		foreach ( $conditions as $aKey => $value ) {
			if ( !array_key_exists( $aKey, $oldMarker ) )
				throw new Exception( 'Key in conditions does not exist in marker.' );

			if ( is_array( $value ) ) {
				// test if one of the values is $marker[$aKey]
				if ( !in_array( $oldMarker[$aKey], $value, true ) )
					return WPVGW_UpdateMarkerResults::MarkerNotFound;
			}
			elseif ( $oldMarker[$aKey] !== $value )
				return WPVGW_UpdateMarkerResults::MarkerNotFound;
		}

		// check negative conditions
		foreach ( $negativConditions as $aKey => $value ) {
			if ( !array_key_exists( $aKey, $oldMarker ) )
				throw new Exception( 'Key in negative conditions does not exist in marker.' );

			if ( is_array( $value ) ) {
				// test if one of the values is $marker[$aKey]
				if ( in_array( $oldMarker[$aKey], $value, true ) )
					return WPVGW_UpdateMarkerResults::MarkerNotFound;
			}
			elseif ( $oldMarker[$aKey] === $value )
				return WPVGW_UpdateMarkerResults::MarkerNotFound;
		}


		// check if old marker has the correct user id
		if ( $oldMarker['user_id'] !== null && $check_user_id !== null && $oldMarker['user_id'] !== $check_user_id )
			return WPVGW_UpdateMarkerResults::UserNotAllowed;


		// only update if a not null old post id does not change or will be set to null
		if ( $oldMarker['post_id'] !== null &&
			array_key_exists( 'post_id', $update_marker ) &&
			$update_marker['post_id'] !== null &&
			$update_marker['post_id'] !== $oldMarker['post_id']
		)
			return WPVGW_UpdateMarkerResults::PostIdNotNull;

		// check to broken integrity
		if ( array_key_exists( 'post_id', $update_marker ) &&
			$this->is_marker_integrity_broken( $key_column, 'post_id', $update_marker['post_id'], $oldMarker )
		)
			return WPVGW_UpdateMarkerResults::PostIdExists;

		// check to broken integrity
		if ( array_key_exists( 'public_marker', $update_marker ) ) {
			if ( $update_marker['public_marker'] === null )
				throw new Exception( 'Public marker must not be null.' );

			if ( $this->is_marker_integrity_broken( $key_column, 'public_marker', $update_marker['public_marker'], $oldMarker ) )
				return WPVGW_UpdateMarkerResults::PublicMarkerExists;
		}

		// check to broken integrity
		if ( array_key_exists( 'private_marker', $update_marker ) &&
			$this->is_marker_integrity_broken( $key_column, 'private_marker', $update_marker['private_marker'], $oldMarker )
		)
			return WPVGW_UpdateMarkerResults::PrivateMarkerExists;


		if ( WPVGW_Helper::array_contains( $update_marker, $oldMarker ) )
			return WPVGW_UpdateMarkerResults::UpdateNotNecessary;


		// do not update the id column
		unset( $update_marker['id'] );

		$setters = WPVGW_Helper::sql_setters( $update_marker );

		array_push( $update_marker, $key );

		// get format literal for the key
		$keyFormatLiteral = WPVGW_Helper::get_format_literal( $key );

		$success = $wpdb->query( WPVGW_Helper::prepare_with_null(
				"UPDATE $this->markersTableName SET $setters WHERE $key_column = $keyFormatLiteral LIMIT 1",
				array_values( $update_marker )
			)
		);

		if ( $success === false )
			WPVGW_Helper::throw_database_exception();


		return WPVGW_UpdateMarkerResults::Updated;
	}

	/**
	 * Deletes a marker from the database.
	 *
	 * @param int $marker_id The ID of the marker to delete.
	 *
	 * @return bool True if the marker was deleted, otherwise false.
	 * @throws Exception Thrown if a database error occurred.
	 */
	public function delete_marker_in_db( $marker_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		// id must be a non-negative integer
		if ( !( is_int( $marker_id ) && $marker_id >= 0 ) )
			return false;


		// get marker that will be deleted
		$markerToDelete = $this->get_marker_from_db( $marker_id, 'id' );

		if ( $markerToDelete === false )
			return false;


		// delete marker
		$successOrDeletedRows = $wpdb->query( WPVGW_Helper::prepare_with_null(
				"DELETE FROM $this->markersTableName WHERE id = %d LIMIT 1",
				$marker_id
			)
		);

		if ( $successOrDeletedRows === false )
			WPVGW_Helper::throw_database_exception();


		// true if at least one marker was deleted
		return ( $successOrDeletedRows >= 1 );
	}

	/**
	 * Insert a new marker into the database.
	 *
	 * @param array $insert_marker The marker to insert. The 'public_marker' must be specified and not be null.
	 *
	 * @return int One of the constants defined in {@link WPVGW_InsertMarkerResults}.
	 * @throws Exception Thrown if one of the arguments are invalid. Thrown if a database error occurred.
	 */
	public function insert_marker_in_db( array $insert_marker ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		// get special column values
		$post_id = array_key_exists( 'post_id', $insert_marker ) ? $insert_marker['post_id'] : null;
		$public_marker = array_key_exists( 'public_marker', $insert_marker ) ? $insert_marker['public_marker'] : null;
		$private_marker = array_key_exists( 'private_marker', $insert_marker ) ? $insert_marker['private_marker'] : null;

		if ( $public_marker === null )
			throw new Exception( 'Public marker must be specified and not be null.' );

		// test if unique data does not exist in database
		if ( $this->key_exists_in_db( $post_id, 'post_id' ) || $this->key_exists_in_db( $public_marker, 'public_marker' ) || $this->key_exists_in_db( $private_marker, 'private_marker' ) ) {
			return WPVGW_InsertMarkerResults::IntegrityError;
		}

		// do not insert the id column
		unset( $insert_marker['id'] );

		$columnNames = WPVGW_Helper::implode_keys( ', ', $insert_marker );
		$columnValues = WPVGW_Helper::sql_values( $insert_marker );

		// insert new marker into markers table
		$success = $wpdb->query( WPVGW_Helper::prepare_with_null(
				"INSERT INTO $this->markersTableName ($columnNames) VALUES ($columnValues)",
				array_values( $insert_marker )
			)
		);

		if ( $success === false )
			WPVGW_Helper::throw_database_exception();


		return WPVGW_InsertMarkerResults::Inserted;
	}

	/**
	 * Tries to parse a marker from VG WORT marker strings like "<img src="http://vg02.met.vgwort.de/na/c662364dcf614454aea6160a00000000" width="1" height="1" alt="">".
	 * It parses plain markers like "c662364dcf614454aea6160a00000000" too.
	 *
	 * @param string $marker_string The string to parse.
	 *
	 * @throws Exception Thrown if a Regex error occurred.
	 * @return array|bool The marker got from the string, otherwise false.
	 */
	private function get_marker_from_string( $marker_string ) {
		// try to match a marker from a specific string
		// string should be something like "<img src="http://vg02.met.vgwort.de/na/c662364dcf614454aea6160a00000000" width="1" height="1" alt="">"
		// string can be a plain marker like "c662364dcf614454aea6160a00000000" too
		$numberOfMatches = WPVGW_Helper::validate_regex_result( preg_match( '%(?:.*http://(?P<server>[a-z0-9./-]+)/(?P<public_marker>[a-z0-9]+).*)|(?:.*?(?P<public_marker_alt>[a-z0-9]+).*)%i', $marker_string, $match ) );

		// return marker if matched
		if ( $numberOfMatches > 0 ) {
			return array(
				'public_marker'  => $match['public_marker'] != '' ? $match['public_marker'] : $match['public_marker_alt'],
				'private_marker' => null,
				'server'         => $match['server'] != '' ? $match['server'] : null
			);
		}

		// no marker parsed
		return false;
	}

	/**
	 * Imports a marker into the database.
	 * If the marker already exists the marker (i. e. the private marker and the server) will be updated if safe.
	 * This function validates the markers data.
	 *
	 * @param string $default_server A default server. Used if $server is null.
	 * @param string $public_marker A public marker.
	 * @param string|null $private_marker A private marker.
	 * @param string|null $server A VG WORT server for the marker.
	 * @param int|null $user_id An user (ID) for the marker.
	 *
	 * @return WPVGW_ImportMarkersStats Import stats.
	 * @throws Exception Thrown if a database error occurred.
	 */
	public function import_marker( $default_server, $public_marker, $private_marker = null, $server = null, $user_id = null ) {
		// import stats
		$importMarkersStats = new WPVGW_ImportMarkersStats();
		$importMarkersStats->numberOfMarkers = 1;

		// use default server?
		if ( $server === null )
			$server = $default_server;

		// validate marker data
		if ( !$this->public_marker_validator( $public_marker ) ||
			( $private_marker !== null && !$this->private_marker_validator( $private_marker ) ) ||
			( !$this->server_validator( $server ) )
		) {
			$importMarkersStats->numberOfFormatErrors++;

			return $importMarkersStats;
		}


		// collect marker data
		$marker = array(
			'user_id'        => $user_id,
			'public_marker'  => $public_marker,
			'private_marker' => $private_marker,
			'server'         => $server,
		);

		// insert new marker in database
		switch ( $this->insert_marker_in_db( $marker ) ) {
			case WPVGW_InsertMarkerResults::Inserted :
				$importMarkersStats->numberOfInsertedMarkers++;
				break;
			case WPVGW_InsertMarkerResults::IntegrityError :
				// update private marker only
				$updateMarker = array(
					'private_marker' => $private_marker,
				);

				// try to update marker by public marker key and update private marker only if private marker in database is null or equal and user id is equal
				switch ( $this->update_marker_in_db(
					$public_marker, // key
					'public_marker', // column
					$updateMarker, // update marker
					null, // do not check user
					array( // conditions
						'user_id'        => $user_id,
						'private_marker' => array( null, $private_marker )
					)
				) ) {
					// iterate import stats
					case WPVGW_UpdateMarkerResults::Updated:
						$importMarkersStats->numberOfUpdatedMarkers++;
						break;
					case WPVGW_UpdateMarkerResults::UpdateNotNecessary:
						$importMarkersStats->numberOfDuplicateMarkers++;
						break;
					default:
						$importMarkersStats->numberOfIntegrityErrors++;
						break;
				}
				break;
			default:
				WPVGW_Helper::throw_unknown_result_exception();
				break;
		}

		// return import stats
		return $importMarkersStats;
	}

	/**
	 * Imports a CSV string into the database.
	 *
	 * @param string $markers_csv A VG WORT CSV string that contains markers.
	 * @param string $default_server A default server. Used if no servers are found in the CSV string.
	 * @param int|null $user_id An user (ID) for the marker.
	 *
	 * @return WPVGW_ImportMarkersStats Import stats.
	 * @throws Exception Thrown if a database error occurred.
	 */
	public function import_markers_from_csv( $markers_csv, $default_server, $user_id = null ) {
		$importMarkersStats = new WPVGW_ImportMarkersStats();

		// extract server, public marker and private marker from the file contents
		WPVGW_Helper::validate_regex_result( preg_match_all( '%.*?;<img.*?"http://(?P<server>[a-z0-9./-]+?)/(?P<public_marker>[a-z0-9]+?)".*?(?:\r\n|\r|\n);.*?;(?P<private_marker>[a-z0-9]+?)(?:;|\Z)%i',
				$markers_csv, $matches, PREG_SET_ORDER
			)
		);

		// iterate found markers
		foreach ( $matches as $match ) {
			$importMarkersStats->add(
				$this->import_marker(
					$default_server,
					$match['public_marker'],
					$match['private_marker'],
					$match['server'],
					$user_id
				)
			);
		}

		// return import stats
		return $importMarkersStats;
	}

	/**
	 * Imports a CSV file into the database.
	 *
	 * @param string $markers_csv_file_path A VG WORT CSV file that contains markers.
	 * @param string $default_server A default server. Used if no servers are found in the CSV file.
	 * @param int|null $user_id An user (ID) for the marker.
	 *
	 * @return WPVGW_ImportMarkersStats Import stats.
	 * @throws Exception Thrown if the csv file was not found. Thrown if a database error occurred.
	 */
	public function import_markers_from_csv_file( $markers_csv_file_path, $default_server, $user_id = null ) {
		// throw exception if file does not exist
		if ( !file_exists( $markers_csv_file_path ) )
			throw new Exception( __( sprintf( 'Die Datei %s existiert nicht.', WPVGW_TEXT_DOMAIN ) ) );

		// read whole file
		$fileContents = file_get_contents( $markers_csv_file_path );

		return $this->import_markers_from_csv( $fileContents, $default_server, $user_id );
	}

	/**
	 * Import old markers from WordPress posts into the database.
	 * All post of the allowed post statuses and all allowed post types are iterated. Only allows users will be considered.
	 * Posts will be add to their corresponding markers, even if the marker is already contained in the database (update).
	 *
	 * @param Callback $get_marker_function A function that returns a marker (an array) that will be add to the database. Syntax: array get_marker_function(WP_Post $post)
	 * @param Callback $after_import_function A function that will be called after the marker was imported and the marker was add to the post successfully. Syntax: after_import_function(WP_Post $post)
	 * @param string $default_server A default server. Used if no servers are found.
	 * @param array $query_override An array that adds and overrides parameters for <code>new WP_Query($query)</code>that it used for post iteration.
	 *
	 * @throws Exception Thrown if a database error occurred.
	 * @return WPVGW_ImportOldMarkersAndPostsStats Import stats.
	 * @see WP_Query
	 */
	private function import_old_markers_and_posts( $get_marker_function, $after_import_function, $default_server, array $query_override = array() ) {
		$importMarkersStats = new WPVGW_ImportMarkersStats();
		$importOldMarkersAndPostsStats = new WPVGW_ImportOldMarkersAndPostsStats();
		$importOldMarkersAndPostsStats->importMarkersStats = $importMarkersStats;

		// TODO: WP_Query will store all database results in array which can cause too much memory consumption (WTF! Why no iterated fetch?)
		// get posts
		$postQuery = new WP_Query(
		// merge defaults and values to override
			array_merge(
				array(
					'numberposts' => -1,
					'nopaging'    => true,
					'post_status' => $this->allowedPostStatuses,
					'post_type'   => $this->possiblePostTypes,
				),
				$query_override
			)
		);

		// iterate found posts
		while ( $postQuery->have_posts() ) {
			$post = $postQuery->next_post();

			$importOldMarkersAndPostsStats->numberOfPosts++;

			// get post author
			$postUserId = (int)$post->post_author;

			// get marker from specified function
			$marker = $get_marker_function( $post );

			/*if ( $marker === false ) {
				// iterate import stats
				$importMarkersStats->numberOfMarkers++;
			}*/
			if ( $marker !== false ) {
				// try to import the marker add import stats; set to arbitrary user (null)
				$importMarkersStats->add(
					$this->import_marker( $default_server, $marker['public_marker'], $marker['private_marker'], $marker['server'], null )
				);

				// add post to marker
				$updateMarkerResult = $this->update_marker_in_db(
					$marker['public_marker'], // key
					'public_marker', // column
					array( // marker
						'post_id' => $post->ID
					),
					$postUserId, // current user
					array( // conditions (just to be safe that the old post id is null or has the post ID already)
						'post_id' => array( null, $post->ID )
					)
				);

				// iterate import stats
				switch ( $updateMarkerResult ) {
					case WPVGW_UpdateMarkerResults::Updated:
						$importOldMarkersAndPostsStats->numberOfUpdates++;
						// call after import function
						if ( $after_import_function !== null )
							$after_import_function( $post );
						break;
					case WPVGW_UpdateMarkerResults::UpdateNotNecessary:
						$importOldMarkersAndPostsStats->numberOfDuplicates++;
						// call after import function
						if ( $after_import_function !== null )
							$after_import_function( $post );
						break;
					default:
						$importOldMarkersAndPostsStats->numberOfIntegrityErrors++;
						break;
				}
			}

		}

		// restore global post data stomped by the_post()
		wp_reset_query();

		return $importOldMarkersAndPostsStats;
	}

	/**
	 * Import old markers from WordPress’ post meta (from prior plugin versions) into the database.
	 * All post of the allowed post statuses and all post types are iterated and checked for a marker in the meta data specified by a meta key name. Only allows users will be considered.
	 * Posts will be add to their corresponding markers, even if the marker is already contained in the database (update).
	 *
	 * @param string $meta_name The post meta key name. This meta data contains the old marker.
	 * @param string $default_server A default server. Used if no servers are found.
	 *
	 * @return WPVGW_ImportOldMarkersAndPostsStats Import stats.
	 * @throws Exception Thrown if a database error occurred.
	 */
	public function import_markers_and_posts_from_old_version( $meta_name, $default_server ) {
		$thisObject = $this;

		// import markers with their corresponding posts
		return $this->import_old_markers_and_posts(
			function ( WP_Post $post ) use ( $thisObject, $meta_name ) {
				// get marker string from post’s meta
				$markerString = get_post_custom_values( $meta_name, $post->ID );
				$markerString = $markerString[0];

				// get marker from marker string
				return $thisObject->get_marker_from_string( $markerString );
			},
			null, // TODO: Maybe delete old post meta?
			$default_server,
			array( 'meta_key' => $meta_name )
		);
	}

	/**
	 * Import old markers from T. Leuschner’s VG WORT plugin into the database.
	 * All post of the allowed post statuses and all post types are iterated and checked for a marker in the meta data specified by a meta key name. Only allows users will be considered.
	 * Posts will be add to their corresponding markers, even if the marker is already contained in the database (update).
	 *
	 * @param string $default_server A default server. Used if no servers are found.
	 *
	 * @return WPVGW_ImportOldMarkersAndPostsStats Import stats.
	 * @throws Exception Thrown if a database error occurred.
	 */
	public function import_markers_and_posts_from_tl_vgwort_plugin( $default_server ) {
		$thisObject = $this;

		// import markers with their corresponding posts
		return $this->import_old_markers_and_posts(
			function ( WP_Post $post ) use ( $thisObject ) {
				// get public marker
				$metaValue = get_post_custom_values( 'vgwort-public', $post->ID );
				$marker['public_marker'] = $metaValue[0];

				// get private marker
				$metaValue = get_post_custom_values( 'vgwort-private', $post->ID );
				$marker['private_marker'] = ( $metaValue === null ? null : $metaValue[0] );

				// get server
				$metaValue = get_post_custom_values( 'vgwort-domain', $post->ID );
				$marker['server'] = ( $metaValue === null ? null : $metaValue[0] );

				// get marker from marker string
				return $marker;
			},
			null, // TODO: Maybe delete old post meta?
			$default_server,
			array( 'meta_key' => 'vgwort-public' ) // specific for T. Leuschner’s VG WORT plugin
		);
	}

	/**
	 * Import old manual markers from WordPress posts into the database. It works if a post content has a VG WORT marker like <code><img src="http://vg02.met.vgwort.de/na/foo123bar" width="1" height="1" alt=""/></code>.
	 * All post of the allowed post statuses and all allowed post types are iterated and checked for a marker. Only allows users will be considered.
	 * Posts will be add to their corresponding markers, even if the marker is already contained in the database (update).
	 * Warning: The first manual marker found in a post will be removed if specified.
	 *
	 * @param string $match_marker_regex A Regular Expression that matches VG WORT markers like <code><img src="http://vg02.met.vgwort.de/na/foo123bar" width="1" height="1" alt=""/></code>. The Regular Expression will not be validated.
	 * @param string $default_server A default server. Used if no servers are found.
	 * @param bool $delete_manual_marker If true, the manual marker will be deleted from post, otherwise it remains.
	 *
	 * @return WPVGW_ImportOldMarkersAndPostsStats Import stats.
	 */
	public function import_markers_and_posts_from_posts( $match_marker_regex, $default_server, $delete_manual_marker = false ) {
		$thisObject = $this;

		// import markers with their corresponding posts
		return $this->import_old_markers_and_posts(
			function ( WP_Post $post ) use ( $thisObject, $match_marker_regex ) {
				// try to find a marker in the post content
				if ( preg_match( $match_marker_regex, $post->post_content, $matches ) !== 1 )
					return false;

				// get marker from marker string
				return $thisObject->get_marker_from_string( $matches[0] );
			},
			function ( WP_Post $post ) use ( $thisObject, $match_marker_regex, $delete_manual_marker ) {
				// delete manual marker?
				if ( !$delete_manual_marker )
					return;

				// delete marker from post content
				$newPostContent = preg_replace( $match_marker_regex, '', $post->post_content, 1 );

				// update post content
				wp_update_post( array(
						'ID'           => $post->ID,
						'post_content' => $newPostContent,
					)
				);
			},
			$default_server
		);
	}


	/**
	 * Validates if a string is a valid public marker up to the format.
	 *
	 * @param string $public_marker A string that is a public marker probably.
	 *
	 * @throws Exception Thrown if a Regex error occurred.
	 * @return bool True if $public_marker is a public marker, otherwise false.
	 */
	public function public_marker_validator( $public_marker ) {
		return WPVGW_Helper::validate_regex_result( preg_match( '/\A[a-z0-9]+\z/im', $public_marker ) ) === 1;
	}

	/**
	 * Validates if a string is a valid private marker up to the format.
	 *
	 * @param string $private_marker A string that is a private marker probably.
	 *
	 * @throws Exception Thrown if a Regex error occurred.
	 * @return bool True if $private_marker is a private marker, otherwise false.
	 */
	public function private_marker_validator( $private_marker ) {
		return WPVGW_Helper::validate_regex_result( preg_match( '/\A[a-z0-9]+\z/im', $private_marker ) ) === 1;
	}

	/**
	 * Validates if a string is a valid VG WORT server up to the format.
	 *
	 * @param string $server A string that is a server probably.
	 *
	 * @throws Exception Thrown if a Regex error occurred.
	 * @return bool True if $server is a server, otherwise false.
	 */
	public function server_validator( $server ) {
		return WPVGW_Helper::validate_regex_result( preg_match( '%\A(?:http://)?[a-z0-9./-]+\z%im', $server ) ) === 1;
	}

	/**
	 * Validates if a bool is a valid marker disabled flag up to the format.
	 *
	 * @param bool $is_marker_disabled A bool that is a marker disabled flag probably.
	 *
	 * @return bool True if $is_marker_disabled is a marker disabled flag, otherwise false.
	 */
	public function is_marker_disabled_validator( $is_marker_disabled ) {
		return is_bool( $is_marker_disabled );
	}

	/**
	 * Removes a possible "http://" and a possible trailing "/" from a VG WORT server.
	 *
	 * @param string $server A server.
	 *
	 * @return string The server without "http://" and trailing "/".
	 */
	public function server_cleaner( $server ) {
		return WPVGW_Helper::remove_prefix(
			WPVGW_Helper::remove_suffix( $server, '/' ),
			'http://'
		);
	}
}


/**
 * Holds import stats for marker imports.
 */
class WPVGW_ImportMarkersStats {
	/**
	 * @var int The number of found markers.
	 */
	public $numberOfMarkers = 0;
	/**
	 * @var int The number of inserted (actually imported) markers.
	 */
	public $numberOfInsertedMarkers = 0;
	/**
	 * @var int The number of markers that already were in the database, but were updated.
	 */
	public $numberOfUpdatedMarkers = 0;
	/**
	 * @var int The number of markers that already were in the database. These markers were ignored.
	 */
	public $numberOfDuplicateMarkers = 0;
	/**
	 * @var int The number of marker format errors. These markers were ignored.
	 */
	public $numberOfFormatErrors = 0;
	/**
	 * @var int The number of marker that would break the integrity if imported. These markers were ignored.
	 */
	public $numberOfIntegrityErrors = 0;


	/**
	 * Adds specified import stats to this import stats.
	 *
	 * @param WPVGW_ImportMarkersStats $import_markers_stats Import stats that will be added to this stats.
	 */
	public function add( WPVGW_ImportMarkersStats $import_markers_stats ) {
		$this->numberOfMarkers += $import_markers_stats->numberOfMarkers;
		$this->numberOfInsertedMarkers += $import_markers_stats->numberOfInsertedMarkers;
		$this->numberOfUpdatedMarkers += $import_markers_stats->numberOfUpdatedMarkers;
		$this->numberOfDuplicateMarkers += $import_markers_stats->numberOfDuplicateMarkers;
		$this->numberOfFormatErrors += $import_markers_stats->numberOfFormatErrors;
		$this->numberOfIntegrityErrors += $import_markers_stats->numberOfIntegrityErrors;
	}
}

/**
 * Holds import stats for old marker imports (from prior plugin version).
 */
class WPVGW_ImportOldMarkersAndPostsStats {
	/**
	 * @var int Number of found posts that have old markers.
	 */
	public $numberOfPosts = 0;
	/**
	 * @var int Number of markers that were updated be found markers.
	 */
	public $numberOfUpdates = 0;
	/**
	 * @var int Number of markers that already were in the database. These markers were ignored.
	 */
	public $numberOfDuplicates = 0;
	/**
	 * @var int Number of markers thar would break the integrity if imported. These markers were ignored.
	 */
	public $numberOfIntegrityErrors = 0;
	/**
	 * @var WPVGW_ImportMarkersStats The ordinary marker import stats.
	 */
	public $importMarkersStats = null;
}


/**
 * Holds constants that will be returned if a marker was updated.
 */
class  WPVGW_UpdateMarkerResults {
	/**
	 * The marker was updated successfully.
	 */
	const Updated = 0;
	/**
	 * The marker was not updated because it is already up to date.
	 */
	const UpdateNotNecessary = 1;
	/**
	 * The marker was not found.
	 */
	const MarkerNotFound = 2;
	/**
	 * The specified user is not allowed to update the marker.
	 */
	const UserNotAllowed = 3;
	/**
	 * The marker was not updated because integrity would be broken. If a marker already has a post id it cannot be changed, except to null.
	 */
	const PostIdNotNull = 4;
	/**
	 * The marker was not updated because integrity would be broken. The post id already exists, i. e., the post already has another marker.
	 */
	const PostIdExists = 5;
	/**
	 * The marker was not updated because integrity would be broken. The public marker already exists.
	 */
	const PublicMarkerExists = 6;
	/**
	 * The marker was not updated because integrity would be broken. The private marker already exists.
	 */
	const PrivateMarkerExists = 7;
}


/**
 * Holds constants that will be returned if a marker was inserted.
 */
class WPVGW_InsertMarkerResults {
	/**
	 * The marker was inserted successfully
	 */
	const Inserted = 0;
	/**
	 * The marker was not inserted because integrity would be broken.
	 */
	const IntegrityError = 1;
}

