<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */



class WPVGW_PostTableView extends WPVGW_ViewBase {
	
	private $characterCountColumnName;
	
	private $postIdColumnName;
	
	private $isMarkerDisabledColumnName;
	
	private $postType = null;
	
	private $filters;
	
	private $adminMessages = array();


	
	public function set_post_type( $value ) {
		$this->postType = $value;
	}


	
	public function __construct( WPVGW_MarkersManager $markers_manager, WPVGW_PostsExtras $posts_extras, WPVGW_Options $options ) {
		parent::__construct( $markers_manager, $options, $posts_extras );

		$this->characterCountColumnName = WPVGW . '_posts_extras_character_count';
		$this->postIdColumnName = WPVGW . '_markers_post_id';
		$this->isMarkerDisabledColumnName = WPVGW . '_markers_is_marker_disabled';

		
		add_action( 'admin_action_' . WPVGW . '_add_marker', array( $this, 'do_add_marker_action' ) );

		
		add_action( 'admin_action_' . WPVGW . '_add_markers', array( $this, 'do_add_markers_action' ) );
		add_action( 'admin_action_' . WPVGW . '_remove_markers', array( $this, 'do_remove_markers_action' ) );
		add_action( 'admin_action_' . WPVGW . '_recalculate_post_character_count', array( $this, 'do_recalculate_post_character_count' ) );
	}


	
	public function init() {
		if ( $this->postType === null )
			throw new Exception( 'Post type must be set before calling init().' );

		
		parent::init_base(
		
			array(
				array(
					'file'         => 'views/post-table-view.js',
					'slug'         => 'post-table-view',
					'dependencies' => array( 'jquery' ),
					'localize'     => array(
						'object_name' => 'translations',
						'data'        => array(
							'add_markers_title'                      => __( 'Zählmarke zuordnen', WPVGW_TEXT_DOMAIN ),
							'show_remove_markers'                    => current_user_can( 'manage_options' ), 
							'remove_markers_title'                   => __( 'Zählmarken-Zuordnung aufheben', WPVGW_TEXT_DOMAIN ),
							'recalculate_post_character_count_title' => __( 'Zeichenanzahl neuberechnen', WPVGW_TEXT_DOMAIN ),
						)
					)
				)
			)
		);


		
		$postsExtrasTableName = $this->postsExtras->get_post_extras_table_name();
		$markersTableName = $this->markersManager->get_markers_table_name();

		
		$characterCountSufficientSql = $this->markersManager->is_character_count_sufficient_sql( $postsExtrasTableName . '.character_count', $this->options->get_vg_wort_minimum_character_count() );

		$this->filters = array(
			
			WPVGW . '_sufficient' => array(
				
				array(
					'label' => __( 'Zeichenanzahl', WPVGW_TEXT_DOMAIN ),
				),
				array(
					'label' => __( 'Genügend', WPVGW_TEXT_DOMAIN ),
					'where' => $characterCountSufficientSql,
				),
				array(
					'label' => __( 'Zu wenig', WPVGW_TEXT_DOMAIN ),
					'where' => "NOT $characterCountSufficientSql",
				),
			),
			
			WPVGW . '_marker'     => array(
				
				array(
					'label' => __( 'Zählmarke', WPVGW_TEXT_DOMAIN ),
				),
				array(
					'label' => __( 'Zugeordnet', WPVGW_TEXT_DOMAIN ),
					'where' => "{$markersTableName}.post_id IS NOT NULL",
				),
				array(
					'label' => __( 'Nicht zugeordnet', WPVGW_TEXT_DOMAIN ),
					'where' => "{$markersTableName}.post_id IS NULL",
				),
				array(
					'label' => __( 'Inaktiv', WPVGW_TEXT_DOMAIN ),
					'where' => "{$markersTableName}.is_marker_disabled = 1",
				),
			),

		);

		
		add_filter( 'manage_' . $this->postType . '_posts_columns', array( $this, 'on_add_column' ) );
		add_action( 'manage_' . $this->postType . '_posts_custom_column', array( $this, 'on_render_column' ), 10, 2 );
		add_filter( 'manage_edit-' . $this->postType . '_sortable_columns', array( $this, 'on_register_sortable_column' ) );

		
		add_filter( 'posts_fields', array( $this, 'on_wp_query_posts_fields' ) );
		add_filter( 'posts_join', array( $this, 'on_wp_query_posts_join' ) );
		add_filter( 'posts_where', array( $this, 'on_wp_query_posts_where' ) );
		add_filter( 'posts_orderby', array( $this, 'on_wp_query_posts_order_by' ) );

		
		add_action( 'restrict_manage_posts', array( $this, 'on_render_filter_html' ) );

		
		add_filter( 'post_row_actions', array( $this, 'on_add_row_actions' ), 10, 2 );
		add_filter( 'page_row_actions', array( $this, 'on_add_row_actions' ), 10, 2 );

		
		add_action( 'admin_notices', array( $this, 'on_admin_notices' ) );
	}

	
	public function on_wp_query_posts_fields( $fields_statement ) {
		
		$postsExtrasTableName = $this->postsExtras->get_post_extras_table_name();
		$markersTableName = $this->markersManager->get_markers_table_name();

		
		$fields_statement .=
			", {$postsExtrasTableName}.character_count AS {$this->characterCountColumnName}, {$markersTableName}.post_id AS {$this->postIdColumnName}, {$markersTableName}.is_marker_disabled AS {$this->isMarkerDisabledColumnName}";

		return $fields_statement;
	}

	
	private function add_admin_message( $message, $type = WPVGW_ErrorType::Error, $escape = true ) {
		$this->adminMessages[] = array( 'message' => $message, 'type' => $type, 'escape' => $escape );
	}

	
	public function on_wp_query_posts_join( $join_statement ) {
		
		global $wpdb;

		
		$postsExtrasTableName = $this->postsExtras->get_post_extras_table_name();
		$markersTableName = $this->markersManager->get_markers_table_name();

		
		$join_statement .=
			" LEFT OUTER JOIN $postsExtrasTableName ON {$postsExtrasTableName}.post_id = {$wpdb->posts}.ID " .
			"LEFT OUTER JOIN $markersTableName ON {$markersTableName}.post_id = {$wpdb->posts}.ID";

		return $join_statement;
	}

	
	public function on_wp_query_posts_where( $where_statement ) {
		$sqlFilters = '';

		
		foreach ( $this->filters as $htmlSelect => $options ) {
			if ( isset( $_REQUEST[$htmlSelect] ) ) {
				
				$currentOption = intval( $_REQUEST[$htmlSelect] );

				if ( $currentOption != 0 && array_key_exists( $currentOption, $options ) )
					
					$sqlFilters .= ' AND ' . $options[$currentOption]['where'];
			}
		}

		
		return $where_statement . $sqlFilters;
	}

	
	public function on_wp_query_posts_order_by( $order_by_statement ) {
		
		$orderBy = get_query_var( 'orderby' );

		
		if ( $orderBy != $this->characterCountColumnName )
			return $order_by_statement;

		
		$order = strtolower( get_query_var( 'order' ) );

		if ( !( $order == 'asc' || $order == 'desc' ) ) {
			$order = 'asc';
		}

		
		$asColumnName = $this->characterCountColumnName;

		
		$order_by_statement = "$asColumnName $order";

		return $order_by_statement;
	}


	
	public function on_add_column( $columns ) {
		
		$columns[$this->characterCountColumnName] = __( 'Zeichen', WPVGW_TEXT_DOMAIN );

		return $columns;
	}

	
	public function on_register_sortable_column( $columns ) {
		
		$columns[$this->characterCountColumnName] = $this->characterCountColumnName;

		return $columns;
	}


	
	public function on_render_column( $column_name, $post_id ) {
		
		if ( $column_name != $this->characterCountColumnName )
			return;

		
		$post = get_post( $post_id );

		
		if ( !$this->markersManager->is_user_allowed( (int)$post->post_author ) ) {
			_e( 'Autor nicht zugelassen', WPVGW_TEXT_DOMAIN );
		}
		else {
			
			
			$characterCount = $post->{$this->characterCountColumnName} === null ? null : intval( $post->{$this->characterCountColumnName} );
			$hasMarker = $post->{$this->postIdColumnName} !== null;
			$isMakerDisabled = $post->{$this->isMarkerDisabledColumnName} == '1';

			
			if ( $characterCount === null )
				echo( __( 'nicht berechnet', WPVGW_TEXT_DOMAIN ) );
			elseif ( $this->markersManager->is_character_count_sufficient( $characterCount, $this->options->get_vg_wort_minimum_character_count() ) )
				echo( sprintf( __( 'genügend, %s', WPVGW_TEXT_DOMAIN ), number_format_i18n( $characterCount ) ) );
			else
				echo( sprintf( __( 'zu wenig, %s', WPVGW_TEXT_DOMAIN ), number_format_i18n( $characterCount ) ) );

			
			echo( '<br />' );

			
			if ( $hasMarker )
				echo( sprintf(
					$this->options->get_post_table_view_use_colors() ? '<span class="wpvgw-has-marker">%s</span>' : '%s',
					__( 'Zählmarke zugeordnet', WPVGW_TEXT_DOMAIN )
				) );
			elseif ( $characterCount !== null && $this->markersManager->is_character_count_sufficient( $characterCount, $this->options->get_vg_wort_minimum_character_count() ) )
				echo( sprintf(
					$this->options->get_post_table_view_use_colors() ? '<span class="wpvgw-marker-possible">%s</span>' : '<em>%s</em>',
					__( 'Zählmarke möglich', WPVGW_TEXT_DOMAIN )
				) );

			
			if ( $isMakerDisabled ) {
				
				echo( '<br />' );
				_e( 'Zählmarke inaktiv', WPVGW_TEXT_DOMAIN );
			}
		}
	}

	
	public function on_render_filter_html() {
		WPVGW_Helper::render_html_selects( $this->filters );
	}


	
	public function on_add_row_actions( $actions, $post ) {
		
		$postTypeObject = get_post_type_object( $post->post_type );
		if ( !current_user_can( $postTypeObject->cap->edit_post, $post->ID ) )
			return $actions;

		
		if ( !$this->markersManager->is_user_allowed( (int)$post->post_author ) )
			return $actions;


		
		$hasMarker = ( $post->{$this->postIdColumnName} !== null );

		if ( !$hasMarker ) {
			
			$action = sprintf(
				'<a href="%s" title="%s">%s</a>',
				wp_nonce_url( admin_url( 'admin.php?action=' . WPVGW . '_add_marker&amp;post=' . $post->ID ), WPVGW . '_add_marker' ),
				__( 'Diesem Beitrag automatisch eine Zählmarke zuordnen', WPVGW_TEXT_DOMAIN ),
				__( 'Zählmarke zuordnen', WPVGW_TEXT_DOMAIN )
			);

			
			$actions[WPVGW . '_add_marker'] = $action;
		}

		return $actions;
	}

	
	private function redirect_to_last_page() {
		
		$referer = wp_get_referer();

		if ( $referer === false )
			
			wp_safe_redirect( get_home_url() );
		else {
			
			wp_safe_redirect( $referer );
			
			set_transient( WPVGW . '_post_table_admin_messages', $this->adminMessages, 30 );
		}

		exit;
	}

	
	private function iterate_posts_for_actions( $post_ids, $check_user_allowed, $do_action ) {
		
		if ( $post_ids === null )
			return 0;


		$processedPostCount = 0;
		$userNotAllowedCount = 0;

		
		foreach ( $post_ids as $postId ) {
			$processedPostCount++;

			
			$postId = intval( $postId );

			
			$post = get_post( $postId );

			
			if ( $post === null )
				continue;

			
			$postTypeObject = get_post_type_object( $post->post_type );
			if ( !current_user_can( $postTypeObject->cap->edit_post, $post->ID ) )
				WPVGW_Helper::die_cheating();


			
			$postUserId = (int)$post->post_author;

			
			if ( $check_user_allowed && !$this->markersManager->is_user_allowed( $postUserId ) ) {
				$userNotAllowedCount++;
				continue;
			}


			if ( !$do_action( $this->markersManager, $this->postsExtras, $this->options, $post, $postUserId ) )
				break;
		}


		

		if ( $userNotAllowedCount > 0 ) {
			$this->add_admin_message(
				_n( 'Ein Beitrag / eine Zählmarke wurde nicht bearbeitet, da der Beitrags-Autor keine Zählmarken verwenden darf.',
					sprintf( '%s Beiträge/Zählmarken wurden nicht bearbeitet, da der jeweilige Beitrags-Autor keine Zählmarken verwenden darf.', number_format_i18n( $userNotAllowedCount ) ),
					$userNotAllowedCount,
					WPVGW_TEXT_DOMAIN
				)
			);
		}


		return $processedPostCount;
	}


	
	private function add_marker_to_post( $post_ids ) {
		
		$noFreeMarker = false;
		$markerAddedCount = 0;
		$markerAlreadyExistsCount = 0;
		$markerNotAddedCount = 0;
		$postCharacterCountUnknownCount = 0;
		$postCharacterCountNotSufficientCount = 0;


		$processedPostCount = $this->iterate_posts_for_actions( $post_ids, true,
			function ( $markersManager, $postsExtras, $options, $post, $postUserId ) use ( &$noFreeMarker, &$markerAddedCount, &$markerAlreadyExistsCount, &$markerNotAddedCount, &$postCharacterCountUnknownCount, &$postCharacterCountNotSufficientCount ) {
				
				
				
				
				

				$postId = $post->ID;

				
				$postExtras = $postsExtras->get_post_extras_from_db( $postId );
				
				$postCharacterCount = ( $postExtras === false ? null : $postExtras['character_count'] );

				if ( $postCharacterCount === null )
					$postCharacterCountUnknownCount++;
				else if ( !$markersManager->is_character_count_sufficient( $postCharacterCount, $options->get_vg_wort_minimum_character_count() ) )
					$postCharacterCountNotSufficientCount++;


				
				$marker = $markersManager->get_free_marker_from_db( $postUserId );

				if ( $marker === false )
					
					$marker = $markersManager->get_free_marker_from_db();

				if ( $marker === false ) {
					
					$noFreeMarker = true;

					return false;
				}
				else {
					
					$markerUpdateResult =
						$markersManager->update_marker_in_db(
							$marker['public_marker'], 
							'public_marker', 
							array( 
								'post_id' => $post->ID,
							),
							$postUserId, 
							array( 
								'post_id' => null
							)
						);

					switch ( $markerUpdateResult ) {
						case WPVGW_UpdateMarkerResults::Updated:
							$markerAddedCount++;
							break;
						case WPVGW_UpdateMarkerResults::UpdateNotNecessary:
						case WPVGW_UpdateMarkerResults::PostIdExists:
							$markerAlreadyExistsCount++;
							break;
						default:
							$markerNotAddedCount++;
							break;
					}
				}

				return true;
			}
		);


		

		if ( $noFreeMarker ) {
			$notProcessedPostCount = count( $post_ids ) - $processedPostCount;

			$this->add_admin_message(
				sprintf( __( 'Es sind nicht mehr genügend Zählmarken für einen oder mehrere Beitrags-Autor vorhanden. Fügen Sie bitte zunächst neue Zählmarken für die betreffenden Beitrags-Autoren hinzu und wiederholen Sie den Vorgang. %s', WPVGW_TEXT_DOMAIN ),
					sprintf( '<a href="%s">%s</a>',
						esc_attr( WPVGW_AdminViewsManger::create_admin_view_url( WPVGW_ImportAdminView::get_slug_static() ) ),
						__( 'Zählmarken hier importieren.', WPVGW_TEXT_DOMAIN )
					)
				) .
				' ' .
				_n( 'Einem Beitrag konnte daher keine Zählmarke zugeordnet werden.',
					sprintf( '%s Beiträgen konnten daher keine Zählmarken zugeordnet werden.', esc_html( number_format_i18n( $notProcessedPostCount ) ) ),
					$notProcessedPostCount,
					WPVGW_TEXT_DOMAIN
				),
				WPVGW_ErrorType::Error,
				false
			);
		}

		if ( $markerAddedCount > 0 ) {
			$this->add_admin_message(
				_n( 'Einem Beitrag wurde eine Zählmarke zugeordnet.',
					sprintf( '%s Beiträgen wurden Zählmarken zugeordnet.', number_format_i18n( $markerAddedCount ) ),
					$markerAddedCount,
					WPVGW_TEXT_DOMAIN
				),
				WPVGW_ErrorType::Update
			);
		}

		if ( $markerAlreadyExistsCount > 0 ) {
			$this->add_admin_message(
				_n( 'Einem Beitrag ist bereits eine Zählmarke zugeordnet worden.',
					sprintf( '%s Beiträgen sind bereits Zählmarken zugeordnet worden.', number_format_i18n( $markerAlreadyExistsCount ) ),
					$markerAlreadyExistsCount,
					WPVGW_TEXT_DOMAIN
				)
			);
		}

		if ( $markerNotAddedCount > 0 ) {
			$this->add_admin_message(
				_n( 'Einem Beitrag konnte keine Zählmarke zugeordnet werden.',
					sprintf( '%s Beiträgen konnten keine Zählmarken zugeordnet werden.', number_format_i18n( $markerNotAddedCount ) ),
					$markerNotAddedCount,
					WPVGW_TEXT_DOMAIN
				)
			);
		}

		if ( $postCharacterCountNotSufficientCount > 0 ) {
			$this->add_admin_message(
				_n( 'Ein Beitrag enthält zu wenig Zeichen. Es wurde möglicherweise dennoch eine Zählmarke zugeordnet.',
					sprintf( '%s Beiträge enthalten zu wenig Zeichen. Es wurden möglicherweise dennoch Zählmarken zugeordnet.', number_format_i18n( $postCharacterCountNotSufficientCount ) ),
					$postCharacterCountNotSufficientCount,
					WPVGW_TEXT_DOMAIN
				)
			);
		}

		if ( $postCharacterCountUnknownCount > 0 ) {
			$this->add_admin_message(
				_n( 'Für einen Beitrag konnte die Anzahl der Zeichen nicht ermittelt werden. Es wurde möglicherweise dennoch eine Zählmarke zugeordnet.',
					sprintf( 'Für %s Beiträge konnte die Anzahl der Zeichen nicht ermittelt werden. Es wurden möglicherweise dennoch Zählmarken zugeordnet.', number_format_i18n( $postCharacterCountUnknownCount ) ),
					$postCharacterCountUnknownCount,
					WPVGW_TEXT_DOMAIN
				)
			);
		}
	}

	
	public function do_add_marker_action() {
		
		check_admin_referer( WPVGW . '_add_marker' );

		
		$postId = ( isset( $_REQUEST['post'] ) ) ? intval( $_REQUEST['post'] ) : null;

		
		$this->add_marker_to_post( array( $postId ) );

		
		$this->redirect_to_last_page();
	}

	
	private function get_bulk_action_post_ids() {
		
		check_admin_referer( 'bulk-posts' );

		
		$postIds = ( isset( $_REQUEST['post'] ) ) ? $_REQUEST['post'] : null;

		if ( $postIds === null || !is_array( $postIds ) )
			return null;

		return $postIds;
	}

	
	public function do_add_markers_action() {
		
		$postIds = $this->get_bulk_action_post_ids();

		
		$this->add_marker_to_post( $postIds );

		
		$this->redirect_to_last_page();
	}

	
	public function do_remove_markers_action() {
		
		if ( !current_user_can( 'manage_options' ) )
			WPVGW_Helper::die_cheating();

		
		$postIds = $this->get_bulk_action_post_ids();

		$removedPostFromMarkerCount = 0;

		
		$processedPostCount = $this->iterate_posts_for_actions( $postIds, false,
			function ( $markersManager, $postsExtras, $options, $post, $postUserId ) use ( &$removedPostFromMarkerCount ) {
				
				
				
				
				

				
				if ( $markersManager->remove_post_from_marker_in_db( $post->ID ) )
					$removedPostFromMarkerCount++;

				return true;
			}
		);


		

		$failedRemovePostFromMarkerCount = count( $postIds ) - $removedPostFromMarkerCount;

		if ( $removedPostFromMarkerCount > 0 ) {
			$this->add_admin_message(
				_n( 'Eine Zählmarken-Zuordnung wurde aufgehoben.',
					sprintf( '%s Zählmarken-Zuordnungen wurden aufgehoben.', number_format_i18n( $removedPostFromMarkerCount ) ),
					$removedPostFromMarkerCount,
					WPVGW_TEXT_DOMAIN
				),
				WPVGW_ErrorType::Update
			);
		}

		if ( $failedRemovePostFromMarkerCount > 0 ) {
			$this->add_admin_message(
				_n( 'Einem Beitrag wurde keine Zählmarke zugeordnet. Daher konnte keine Zählmarke-Zuordnung aufgehoben werden.',
					sprintf( '%s Beiträgen wurden keine Zählmarken zugeordnet. Daher konnte keine Zählmarke-Zuordnungen aufgehoben werden.', number_format_i18n( $failedRemovePostFromMarkerCount ) ),
					$failedRemovePostFromMarkerCount,
					WPVGW_TEXT_DOMAIN
				)
			);
		}


		
		$this->redirect_to_last_page();
	}

	
	public function  do_recalculate_post_character_count() {
		
		$postIds = $this->get_bulk_action_post_ids();

		$postCharacterCountRecalculatedCount = 0;

		$processedPostCount = $this->iterate_posts_for_actions( $postIds, true,
			function ( $markersManager, $postsExtras, $options, $post, $postUserId ) use ( &$postCharacterCountRecalculatedCount ) {
				
				
				
				
				

				
				$postsExtras->recalculate_post_character_count_in_db( $post );

				$postCharacterCountRecalculatedCount++;

				return true;
			}
		);


		

		$failedPostCharacterCountRecalculatedCount = count( $postIds ) - $postCharacterCountRecalculatedCount;

		if ( $postCharacterCountRecalculatedCount > 0 ) {
			$this->add_admin_message(
				_n( 'Die Zeichenanzahl eines Beitrags wurde neuberechnet.',
					sprintf( 'Die Zeichenanzahlen von %s Beiträgen wurden neuberechnet.', number_format_i18n( $postCharacterCountRecalculatedCount ) ),
					$postCharacterCountRecalculatedCount,
					WPVGW_TEXT_DOMAIN
				),
				WPVGW_ErrorType::Update
			);
		}

		if ( $failedPostCharacterCountRecalculatedCount > 0 ) {
			$this->add_admin_message(
				_n( 'Die Zeichenanzahl eines Beitrags konnte nicht neuberechnet werden.',
					sprintf( 'Die Zeichenanzahlen von %s Beiträgen konnte nicht neuberechnet werden.', number_format_i18n( $failedPostCharacterCountRecalculatedCount ) ),
					$failedPostCharacterCountRecalculatedCount,
					WPVGW_TEXT_DOMAIN
				)
			);
		}

		
		$this->redirect_to_last_page();
	}

	
	public function on_admin_notices() {
		
		$adminMessages = get_transient( WPVGW . '_post_table_admin_messages' );

		if ( $adminMessages === false )
			return;

		
		delete_transient( WPVGW . '_post_table_admin_messages' );

		
		WPVGW_Helper::render_admin_messages( $adminMessages );
	}

}
