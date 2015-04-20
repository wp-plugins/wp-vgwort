<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */



class WPVGW_MarkersManager {

	
	private $markersTableName;

	
	private $allowedUserRoles;

	
	private $allowedPostStatuses = array( 'publish', 'pending', 'draft', 'future', 'private', 'trash' );

	
	private $possiblePostTypes = null;
	
	private $allowedPostTypes = null;
	
	private $removedPostTypes = null;

	
	private $doShortcodesForCharacterCountCalculation = false;
	
	private $considerExcerptForCharacterCountCalculation = false;


	
	public function get_markers_table_name() {
		return $this->markersTableName;
	}


	
	public function get_allowed_post_statuses() {
		return $this->allowedPostStatuses;
	}

	
	public function get_allowed_post_types() {
		return $this->allowedPostTypes;
	}

	
	public function set_allowed_post_types( $value ) {
		$this->allowedPostTypes = $value;
		$this->build_valid_post_type_arrays();
	}

	
	public function get_removed_post_types() {
		return $this->removedPostTypes;
	}

	
	public function set_removed_post_types( $value ) {
		$this->removedPostTypes = $value;
		$this->build_valid_post_type_arrays();
	}

	
	public function get_possible_post_types() {
		return $this->possiblePostTypes;
	}


	
	public function __construct( $markers_table_name, $allowed_user_roles, $allowed_post_types, $removed_post_types, $do_shortcodes_for_character_count_calculation, $considerExcerptForCharacterCountCalculation ) {
		$this->markersTableName = $markers_table_name;
		$this->allowedPostTypes = $allowed_post_types;
		$this->removedPostTypes = $removed_post_types;
		$this->allowedUserRoles = $allowed_user_roles;

		
		$this->possiblePostTypes = array_merge(
			array( 'post', 'page' ), 
			array_values( get_post_types( array( 'public' => true, 'show_ui' => true, '_builtin' => false ) ) )
		);

		
		$this->build_valid_post_type_arrays();

		$this->doShortcodesForCharacterCountCalculation = $do_shortcodes_for_character_count_calculation;
		$this->considerExcerptForCharacterCountCalculation = $considerExcerptForCharacterCountCalculation;
	}


	
	private function build_valid_post_type_arrays() {
		
		foreach ( $this->allowedPostTypes as $key => $allowedPostType ) {
			
			if ( !in_array( $allowedPostType, $this->possiblePostTypes, true ) ) {
				
				unset( $this->allowedPostTypes[$key] );
				
				$this->removedPostTypes[] = $allowedPostType;
			}
		}

		
		foreach ( $this->removedPostTypes as $key => $removedPostType ) {
			
			if ( in_array( $removedPostType, $this->possiblePostTypes, true ) ) {
				
				unset( $this->removedPostTypes[$key] );
				
				$this->allowedPostTypes[] = $removedPostType;
			}
		}
	}

	
	public function is_post_type_possible( $post_type ) {
		return in_array( $post_type, $this->possiblePostTypes );
	}

	
	public function is_post_type_allowed( $post_type ) {
		return in_array( $post_type, $this->allowedPostTypes );
	}


	
	public function is_user_allowed( $user_id ) {
		if ( $user_id === null )
			return true;

		$user = get_userdata( $user_id );

		
		if ( $user === false ) {
			return false;
		}
		else {
			
			return $this->is_user_role_allowed( $user->roles );
		}
	}

	
	private function is_user_role_allowed( $roles ) {
		if ( is_array( $roles ) )
			return count( array_intersect( $this->allowedUserRoles, $roles ) ) > 0;

		return in_array( $roles, $this->allowedUserRoles );
	}


	
	public function calculate_character_count( $post_title, $post_content, $post_excerpt ) {
		
		$post_content = $this->cleanWordPressText( $post_content );

		
		if ( $this->considerExcerptForCharacterCountCalculation )
			
			$post_excerpt = $this->cleanWordPressText( $post_excerpt );
		else
			$post_excerpt = '';

		
		return ( mb_strlen( $post_title ) + mb_strlen( $post_content ) + mb_strlen( $post_excerpt ) );
	}

	
	private function cleanWordPressText( $text ) {
		
		$text = preg_replace( WPVGW_Helper::$captionShortcodeRegex, '', $text );

		
		
		if ( $this->doShortcodesForCharacterCountCalculation )
			$text = do_shortcode( $text );

		
		$text = preg_replace(
			'%<br\s*/?>%si',
			' ',
			$text
		);

		
		$text = strip_tags( $text );

		
		$text = preg_replace( array(
			WPVGW_Helper::$shortcodeRegex, 
			'/\s{2,}/i' 
		),
			array(
				'',
				' '
			),
			$text
		);

		
		$text = html_entity_decode( $text );

		
		return trim( $text );
	}

	
	public function is_character_count_sufficient( $character_count, $minimum_character_count ) {
		
		return $character_count >= $minimum_character_count;
	}

	
	public function is_character_count_sufficient_sql( $character_count_column, $minimum_character_count ) {
		
		
		return $character_count_column . ' >= ' . $minimum_character_count;
	}

	
	public function calculate_missing_character_count( $character_count, $minimum_character_count ) {
		if ( $character_count < $minimum_character_count )
			return $minimum_character_count - $character_count;
		else
			return 0;
	}

	
	public function key_exists_in_db( $key, $column ) {
		
		global $wpdb;

		if ( $key === null )
			return false;

		$formatLiteral = WPVGW_Helper::get_format_literal( $key );

		
		$exists = $wpdb->get_var( WPVGW_Helper::prepare_with_null(
				"SELECT EXISTS(SELECT 1 FROM $this->markersTableName WHERE $column = $formatLiteral LIMIT 1)",
				$key
			)
		);

		if ( $exists === null )
			WPVGW_Helper::throw_database_exception();

		return (bool)$exists;
	}

	
	private function make_marker_typesafe( array &$marker ) {
		
		$marker['id'] = (int)$marker['id'];
		$marker['post_id'] = $marker['post_id'] === null ? null : (int)$marker['post_id'];
		$marker['user_id'] = $marker['user_id'] === null ? null : (int)$marker['user_id'];
		$marker['is_marker_disabled'] = (bool)$marker['is_marker_disabled'];
		$marker['is_post_deleted'] = (bool)$marker['is_post_deleted'];
	}

	
	public function get_marker_from_db( $key, $column ) {
		
		global $wpdb;

		
		if ( $key === null )
			return false;

		$formatLiteral = WPVGW_Helper::get_format_literal( $key );

		
		$marker = $wpdb->get_row( WPVGW_Helper::prepare_with_null(
				"SELECT * FROM $this->markersTableName WHERE $column = $formatLiteral LIMIT 1",
				$key
			),
			ARRAY_A
		);

		if ( $wpdb->last_error !== '' )
			WPVGW_Helper::throw_database_exception();

		
		if ( $marker === null )
			return false;

		
		$this->make_marker_typesafe( $marker );

		return $marker;
	}

	
	public function get_free_marker_from_db( $user_id = null ) {
		
		global $wpdb;

		if ( $user_id !== null )
			
			if ( !is_int( $user_id ) || $user_id < 0 )
				return false;

		
		$compareOperator = ( $user_id === null ? 'IS' : '=' );

		
		$marker = $wpdb->get_row( WPVGW_Helper::prepare_with_null(
				"SELECT * FROM $this->markersTableName WHERE user_id $compareOperator %d AND post_id IS NULL AND is_post_deleted = 0 LIMIT 1",
				$user_id
			),
			ARRAY_A
		);

		if ( $wpdb->last_error !== '' )
			WPVGW_Helper::throw_database_exception();

		
		if ( $marker === null )
			return false;

		
		$this->make_marker_typesafe( $marker );

		return $marker;
	}

	
	public function remove_post_from_marker_in_db( $id, $id_type = 'post' ) {
		if ( $id === null || !is_int( $id ) || $id < 0 )
			throw new Exception( "ID must be a non-negative integer." );

		
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

		
		return $this->update_marker_in_db(
			$id,
			$keyColumn,
			array( 
				'post_id'            => null,
				'is_marker_disabled' => false,
				'is_post_deleted'    => false,
				'deleted_post_title' => null,
			),
			null,
			array(),
			array( 'post_id' => null ) 
		) == WPVGW_UpdateMarkerResults::Updated;
	}


	
	private function is_marker_integrity_broken( $key_column, $column, $value, array $oldMarker ) {
		return $column != $key_column && $value !== null && $oldMarker[$column] !== $value && $this->key_exists_in_db( $value, $column );
	}

	
	public function update_marker_in_db( $key, $key_column, array $update_marker, $check_user_id = null, array $conditions = array(), array $negativConditions = array() ) {
		
		global $wpdb;

		
		if ( count( $update_marker ) < 1 )
			throw new Exception( 'Too few elements in update marker.' );

		
		if ( $key_column != 'id' && $key_column != 'post_id' && $key_column != 'public_marker' && $key_column != 'private_marker' )
			throw new Exception( 'Key column has an invalid column name.' );

		
		$oldMarker = $this->get_marker_from_db( $key, $key_column );

		
		if ( $oldMarker === false )
			return WPVGW_UpdateMarkerResults::MarkerNotFound;


		
		foreach ( $conditions as $aKey => $value ) {
			if ( !array_key_exists( $aKey, $oldMarker ) )
				throw new Exception( 'Key in conditions does not exist in marker.' );

			if ( is_array( $value ) ) {
				
				if ( !in_array( $oldMarker[$aKey], $value, true ) )
					return WPVGW_UpdateMarkerResults::MarkerNotFound;
			}
			elseif ( $oldMarker[$aKey] !== $value )
				return WPVGW_UpdateMarkerResults::MarkerNotFound;
		}

		
		foreach ( $negativConditions as $aKey => $value ) {
			if ( !array_key_exists( $aKey, $oldMarker ) )
				throw new Exception( 'Key in negative conditions does not exist in marker.' );

			if ( is_array( $value ) ) {
				
				if ( in_array( $oldMarker[$aKey], $value, true ) )
					return WPVGW_UpdateMarkerResults::MarkerNotFound;
			}
			elseif ( $oldMarker[$aKey] === $value )
				return WPVGW_UpdateMarkerResults::MarkerNotFound;
		}


		
		if ( $oldMarker['user_id'] !== null && $check_user_id !== null && $oldMarker['user_id'] !== $check_user_id )
			return WPVGW_UpdateMarkerResults::UserNotAllowed;


		
		if ( $oldMarker['post_id'] !== null &&
			array_key_exists( 'post_id', $update_marker ) &&
			$update_marker['post_id'] !== null &&
			$update_marker['post_id'] !== $oldMarker['post_id']
		)
			return WPVGW_UpdateMarkerResults::PostIdNotNull;

		
		if ( array_key_exists( 'post_id', $update_marker ) &&
			$this->is_marker_integrity_broken( $key_column, 'post_id', $update_marker['post_id'], $oldMarker )
		)
			return WPVGW_UpdateMarkerResults::PostIdExists;

		
		if ( array_key_exists( 'public_marker', $update_marker ) ) {
			if ( $update_marker['public_marker'] === null )
				throw new Exception( 'Public marker must not be null.' );

			if ( $this->is_marker_integrity_broken( $key_column, 'public_marker', $update_marker['public_marker'], $oldMarker ) )
				return WPVGW_UpdateMarkerResults::PublicMarkerExists;
		}

		
		if ( array_key_exists( 'private_marker', $update_marker ) &&
			$this->is_marker_integrity_broken( $key_column, 'private_marker', $update_marker['private_marker'], $oldMarker )
		)
			return WPVGW_UpdateMarkerResults::PrivateMarkerExists;


		if ( WPVGW_Helper::array_contains( $update_marker, $oldMarker ) )
			return WPVGW_UpdateMarkerResults::UpdateNotNecessary;


		
		unset( $update_marker['id'] );

		$setters = WPVGW_Helper::sql_setters( $update_marker );

		array_push( $update_marker, $key );

		
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

	
	public function delete_marker_in_db( $marker_id ) {
		
		global $wpdb;

		
		if ( !( is_int( $marker_id ) && $marker_id >= 0 ) )
			return false;


		
		$markerToDelete = $this->get_marker_from_db( $marker_id, 'id' );

		if ( $markerToDelete === false )
			return false;


		
		$successOrDeletedRows = $wpdb->query( WPVGW_Helper::prepare_with_null(
				"DELETE FROM $this->markersTableName WHERE id = %d LIMIT 1",
				$marker_id
			)
		);

		if ( $successOrDeletedRows === false )
			WPVGW_Helper::throw_database_exception();


		
		return ( $successOrDeletedRows >= 1 );
	}

	
	public function insert_marker_in_db( array $insert_marker ) {
		
		global $wpdb;

		
		$post_id = array_key_exists( 'post_id', $insert_marker ) ? $insert_marker['post_id'] : null;
		$public_marker = array_key_exists( 'public_marker', $insert_marker ) ? $insert_marker['public_marker'] : null;
		$private_marker = array_key_exists( 'private_marker', $insert_marker ) ? $insert_marker['private_marker'] : null;

		if ( $public_marker === null )
			throw new Exception( 'Public marker must be specified and not be null.' );

		
		if ( $this->key_exists_in_db( $post_id, 'post_id' ) || $this->key_exists_in_db( $public_marker, 'public_marker' ) || $this->key_exists_in_db( $private_marker, 'private_marker' ) ) {
			return WPVGW_InsertMarkerResults::IntegrityError;
		}

		
		unset( $insert_marker['id'] );

		$columnNames = WPVGW_Helper::implode_keys( ', ', $insert_marker );
		$columnValues = WPVGW_Helper::sql_values( $insert_marker );

		
		$success = $wpdb->query( WPVGW_Helper::prepare_with_null(
				"INSERT INTO $this->markersTableName ($columnNames) VALUES ($columnValues)",
				array_values( $insert_marker )
			)
		);

		if ( $success === false )
			WPVGW_Helper::throw_database_exception();


		return WPVGW_InsertMarkerResults::Inserted;
	}

	
	
	public function get_marker_from_string( $marker_string ) {
		
		
		
		$numberOfMatches = WPVGW_Helper::validate_regex_result( preg_match( '%(?:.*http://(?P<server>[a-z0-9./-]+)/(?P<public_marker>[a-z0-9]+).*)|(?:.*?(?P<public_marker_alt>[a-z0-9]+).*)%i', $marker_string, $match ) );

		
		if ( $numberOfMatches > 0 ) {
			return array(
				'public_marker'  => $match['public_marker'] != '' ? $match['public_marker'] : $match['public_marker_alt'],
				'private_marker' => null,
				'server'         => $match['server'] != '' ? $match['server'] : null
			);
		}

		
		return false;
	}

	
	public function import_marker( $default_server, $public_marker, $private_marker = null, $server = null, $user_id = null ) {
		
		$importMarkersStats = new WPVGW_ImportMarkersStats();
		$importMarkersStats->numberOfMarkers = 1;

		
		if ( $server === null )
			$server = $default_server;

		
		if ( !$this->public_marker_validator( $public_marker ) ||
			( $private_marker !== null && !$this->private_marker_validator( $private_marker ) ) ||
			( !$this->server_validator( $server ) )
		) {
			$importMarkersStats->numberOfFormatErrors++;

			return $importMarkersStats;
		}


		
		$marker = array(
			'user_id'        => $user_id,
			'public_marker'  => $public_marker,
			'private_marker' => $private_marker,
			'server'         => $server,
		);

		
		switch ( $this->insert_marker_in_db( $marker ) ) {
			case WPVGW_InsertMarkerResults::Inserted :
				$importMarkersStats->numberOfInsertedMarkers++;
				break;
			case WPVGW_InsertMarkerResults::IntegrityError :
				
				$updateMarker = array(
					'private_marker' => $private_marker,
				);

				
				switch ( $this->update_marker_in_db(
					$public_marker, 
					'public_marker', 
					$updateMarker, 
					null, 
					array( 
						'user_id'        => $user_id,
						'private_marker' => array( null, $private_marker )
					)
				) ) {
					
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

		
		return $importMarkersStats;
	}

	
	public function import_markers_from_csv_file( $is_author_csv, $markers_csv_file_path, $default_server, $user_id = null ) {
		
		if ( !file_exists( $markers_csv_file_path ) )
			throw new Exception( __( sprintf( 'Die Datei %s existiert nicht.', WPVGW_TEXT_DOMAIN ) ) );

		
		$fileContents = file_get_contents( $markers_csv_file_path );

		return $this->import_markers_from_csv( $is_author_csv, $fileContents, $default_server, $user_id );
	}

	
	public function import_markers_from_csv( $is_author_csv, $markers_csv, $default_server, $user_id = null ) {
		$importMarkersStats = new WPVGW_ImportMarkersStats();

		if ( $is_author_csv )
			
			WPVGW_Helper::validate_regex_result( preg_match_all( '%.*?;<img.*?"http://(?P<server>[a-z0-9./-]+?)/(?P<public_marker>[a-z0-9]+?)".*?(?:\r\n|\r|\n);.*?;(?P<private_marker>[a-z0-9]+?)(?:;|\Z)%i',
					$markers_csv, $matches, PREG_SET_ORDER
				)
			);
		else
			
			WPVGW_Helper::validate_regex_result( preg_match_all( '/^(?P<public_marker>[a-z0-9]+?);(?P<private_marker>[a-z0-9]+?)(?:\s|\Z)/im',
					$markers_csv, $matches, PREG_SET_ORDER
				)
			);


		
		foreach ( $matches as $match ) {
			$importMarkersStats->add(
				$this->import_marker(
					$default_server,
					$match['public_marker'],
					$match['private_marker'],
					array_key_exists( 'server', $match ) ? $match['server'] : null, 
					$user_id
				)
			);
		}

		
		return $importMarkersStats;
	}

	
	private function import_old_markers_and_posts( $get_marker_function, $after_import_function, $default_server, array $query_override = array() ) {
		$importMarkersStats = new WPVGW_ImportMarkersStats();
		$importOldMarkersAndPostsStats = new WPVGW_ImportOldMarkersAndPostsStats();
		$importOldMarkersAndPostsStats->importMarkersStats = $importMarkersStats;

		
		if ( !empty ( $this->allowedPostTypes ) ) {
			
			$postQuery = new WPVGW_Uncached_WP_Query(
			
				array_merge(
					array(
						'post_status' => $this->allowedPostStatuses,
						'post_type'   => $this->allowedPostTypes,
					),
					$query_override
				)
			);

			
			while ( $postQuery->has_post() ) {
				$post = $postQuery->get_post();

				$importOldMarkersAndPostsStats->numberOfPosts++;

				
				$postUserId = (int)$post->post_author;

				
				$marker = $get_marker_function( $post );

				
				if ( $marker !== false ) {
					
					$importMarkersStats->add(
						$this->import_marker( $default_server, $marker['public_marker'], $marker['private_marker'], $marker['server'], null )
					);

					
					$updateMarkerResult = $this->update_marker_in_db(
						$marker['public_marker'], 
						'public_marker', 
						array( 
							'post_id' => $post->ID
						),
						$postUserId, 
						array( 
							'post_id' => array( null, $post->ID )
						)
					);

					
					switch ( $updateMarkerResult ) {
						case WPVGW_UpdateMarkerResults::Updated:
							$importOldMarkersAndPostsStats->numberOfUpdates++;
							
							if ( $after_import_function !== null )
								$after_import_function( $post );
							break;
						case WPVGW_UpdateMarkerResults::UpdateNotNecessary:
							$importOldMarkersAndPostsStats->numberOfDuplicates++;
							
							if ( $after_import_function !== null )
								$after_import_function( $post );
							break;
						default:
							$importOldMarkersAndPostsStats->numberOfIntegrityErrors++;
							break;
					}
				}
			}
		}

		return $importOldMarkersAndPostsStats;
	}

	
	public function import_markers_and_posts_from_old_version( $meta_name, $default_server ) {
		$thisObject = $this;

		
		return $this->import_old_markers_and_posts(
			function ( WP_Post $post ) use ( $thisObject, $meta_name ) {
				
				$markerString = get_post_custom_values( $meta_name, $post->ID );
				$markerString = $markerString[0];

				
				return $thisObject->get_marker_from_string( $markerString );
			},
			null, 
			$default_server,
			array( 'meta_key' => $meta_name )
		);
	}

	
	public function import_markers_and_posts_from_tl_vgwort_plugin( $default_server ) {
		
		return $this->import_old_markers_and_posts(
			function ( WP_Post $post ) {
				
				$metaValue = get_post_custom_values( 'vgwort-public', $post->ID );
				$marker['public_marker'] = $metaValue[0];

				
				$metaValue = get_post_custom_values( 'vgwort-private', $post->ID );
				$marker['private_marker'] = ( $metaValue === null ? null : $metaValue[0] );

				
				$metaValue = get_post_custom_values( 'vgwort-domain', $post->ID );
				$marker['server'] = ( $metaValue === null ? null : $metaValue[0] );

				
				return $marker;
			},
			null, 
			$default_server,
			array( 'meta_key' => 'vgwort-public' ) 
		);
	}

	
	public function import_markers_and_posts_from_vgw_plugin( $default_server ) {
		$thisObject = $this;

		
		return $this->import_old_markers_and_posts(
			function ( WP_Post $post ) use ( $thisObject ) {
				
				$metaValue = get_post_custom_values( 'vgwpixel', $post->ID );

				
				return $thisObject->get_marker_from_string( $metaValue[0] );
			},
			null, 
			$default_server,
			array( 'meta_key' => 'vgwpixel' ) 
		);
	}

	
	public function import_markers_and_posts_from_posts( $match_marker_regex, $default_server, $delete_manual_marker = false ) {
		$thisObject = $this;

		
		return $this->import_old_markers_and_posts(
			function ( WP_Post $post ) use ( $thisObject, $match_marker_regex ) {
				
				if ( preg_match( $match_marker_regex, $post->post_content, $matches ) !== 1 )
					return false;

				
				return $thisObject->get_marker_from_string( $matches[0] );
			},
			function ( WP_Post $post ) use ( $match_marker_regex, $delete_manual_marker ) {
				
				if ( !$delete_manual_marker )
					return;

				
				$newPostContent = preg_replace( $match_marker_regex, '', $post->post_content, 1 );

				
				wp_update_post( array(
						'ID'           => $post->ID,
						'post_content' => $newPostContent,
					)
				);
			},
			$default_server
		);
	}


	
	public function public_marker_validator( $public_marker ) {
		return WPVGW_Helper::validate_regex_result( preg_match( '/\A[a-z0-9]+\z/im', $public_marker ) ) === 1;
	}

	
	public function private_marker_validator( $private_marker ) {
		return WPVGW_Helper::validate_regex_result( preg_match( '/\A[a-z0-9]+\z/im', $private_marker ) ) === 1;
	}

	
	public function server_validator( $server ) {
		return WPVGW_Helper::validate_regex_result( preg_match( '%\A(?:http://)?[a-z0-9./-]+\z%im', $server ) ) === 1;
	}

	
	public function is_marker_disabled_validator( $is_marker_disabled ) {
		return is_bool( $is_marker_disabled );
	}

	
	public function server_cleaner( $server ) {
		return WPVGW_Helper::remove_prefix(
			WPVGW_Helper::remove_suffix( $server, '/' ),
			'http://'
		);
	}
}



class WPVGW_ImportMarkersStats {
	
	public $numberOfMarkers = 0;
	
	public $numberOfInsertedMarkers = 0;
	
	public $numberOfUpdatedMarkers = 0;
	
	public $numberOfDuplicateMarkers = 0;
	
	public $numberOfFormatErrors = 0;
	
	public $numberOfIntegrityErrors = 0;


	
	public function add( WPVGW_ImportMarkersStats $import_markers_stats ) {
		$this->numberOfMarkers += $import_markers_stats->numberOfMarkers;
		$this->numberOfInsertedMarkers += $import_markers_stats->numberOfInsertedMarkers;
		$this->numberOfUpdatedMarkers += $import_markers_stats->numberOfUpdatedMarkers;
		$this->numberOfDuplicateMarkers += $import_markers_stats->numberOfDuplicateMarkers;
		$this->numberOfFormatErrors += $import_markers_stats->numberOfFormatErrors;
		$this->numberOfIntegrityErrors += $import_markers_stats->numberOfIntegrityErrors;
	}
}


class WPVGW_ImportOldMarkersAndPostsStats {
	
	public $numberOfPosts = 0;
	
	public $numberOfUpdates = 0;
	
	public $numberOfDuplicates = 0;
	
	public $numberOfIntegrityErrors = 0;
	
	public $importMarkersStats = null;
}



class  WPVGW_UpdateMarkerResults {
	
	const Updated = 0;
	
	const UpdateNotNecessary = 1;
	
	const MarkerNotFound = 2;
	
	const UserNotAllowed = 3;
	
	const PostIdNotNull = 4;
	
	const PostIdExists = 5;
	
	const PublicMarkerExists = 6;
	
	const PrivateMarkerExists = 7;
}



class WPVGW_InsertMarkerResults {
	
	const Inserted = 0;
	
	const IntegrityError = 1;
}

