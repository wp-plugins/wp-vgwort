<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */


require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );



class WPVGW_MarkersListTable extends WP_List_Table {

	
	private $markersManager;
	
	private $postsExtras;
	
	private $options;
	
	private $columns;
	
	private $sortableColumns;
	
	private $sortableColumnsWithSlugs;
	
	private $filterableColumnsSelects;
	
	private $bulkActions;
	
	private $viewLinks;
	
	private $urlActionUrlTemplate;
	
	private $emptyDataText;


	
	protected $_column_headers;

	
	public function __construct( WPVGW_MarkersManager $markers_manager, WPVGW_PostsExtras $posts_extras, WPVGW_Options $options ) {
		
		$this->columns = array(
			'cb'                => '<input type="checkbox" />', 
			
			'post_title'        => __( 'Beitrag', WPVGW_TEXT_DOMAIN ),
			'post_date'         => __( 'Datum', WPVGW_TEXT_DOMAIN ),
			'e_character_count' => __( 'Zeichenanzahl', WPVGW_TEXT_DOMAIN ),
			
			'up_display_name'   => __( 'Beitrags-Autor', WPVGW_TEXT_DOMAIN ),
			'marker'            => __( 'Zählmarke', WPVGW_TEXT_DOMAIN ),
		);

		
		$this->sortableColumns = array(
			'post_title',
			'post_date',
			
			'up_display_name',
			'e_character_count',
		);

		$this->sortableColumnsWithSlugs = array();
		
		foreach ( $this->sortableColumns as $column ) {
			$this->sortableColumnsWithSlugs[$column] = array( $column, false );
		}


		
		$allowedPostTypes = $markers_manager->get_allowed_post_types();
		
		
		if ( empty ( $allowedPostTypes ) )
			
			$sqlAllowedPostTypesSql = '" "';
		else
			
			$sqlAllowedPostTypesSql = WPVGW_Helper::prepare_with_null( WPVGW_Helper::sql_values( $allowedPostTypes ), $allowedPostTypes );

		$allowedPostTypesOptions = array(
			
			array(
				'label' => __( 'Beitrags-Typ', WPVGW_TEXT_DOMAIN ),
			),
			array(
				'label' => __( 'Zugelassen', WPVGW_TEXT_DOMAIN ),
				'where' => "p.post_type IN ($sqlAllowedPostTypesSql)",
			),
			array(
				'label' => __( 'Nicht Zugelassen', WPVGW_TEXT_DOMAIN ),
				'where' => "p.post_type NOT IN ($sqlAllowedPostTypesSql)",
			),
		);

		
		foreach ( $allowedPostTypes as $allowedPostType ) {
			$postTypeObject = get_post_type_object( $allowedPostType );

			if ( $postTypeObject !== null )
				
				$allowedPostTypesOptions[] =
					array(
						'label' => sprintf( __( 'Typ: %s', WPVGW_TEXT_DOMAIN ), $postTypeObject->labels->name ),
						'where' => WPVGW_Helper::prepare_with_null( 'p.post_type = %s', $allowedPostType ),
					);
		}

		
		$postYearOptions = array(
			
			array(
				'label' => __( 'Beitrags-Jahr', WPVGW_TEXT_DOMAIN ),
			),
		);
		
		$currentYear = current_time( 'Y' );
		
		for ( $year = $currentYear + 2; $year >= $currentYear - 10; $year-- ) {
			$postYearOptions[] =
				array(
					'label' => sprintf( __( '%s', WPVGW_TEXT_DOMAIN ), $year ),
					'where' => WPVGW_Helper::prepare_with_null( 'YEAR(p.post_date) = %d', $year ),
				);
		}


		
		$characterCountSufficientSql = $markers_manager->is_character_count_sufficient_sql( 'e.character_count', $options->get_vg_wort_minimum_character_count() );

		$validMarkerFormatSql = sprintf( '(%s) AND (%s)', $markers_manager->has_valid_marker_format_sql( "m.public_marker" ), $markers_manager->has_valid_marker_format_sql( "m.private_marker" ) );

		
		
		$this->filterableColumnsSelects = array(
			
			WPVGW . '_has_marker'            => array(
				
				array(
					'label' => __( 'Zuordnung', WPVGW_TEXT_DOMAIN ),
				),
				array(
					'label' => __( 'Zugeordnet', WPVGW_TEXT_DOMAIN ),
					'where' => 'm.post_id IS NOT NULL',
				),
				array(
					'label' => __( 'Nicht zugeordnet', WPVGW_TEXT_DOMAIN ),
					'where' => 'm.post_id IS NULL',
				),
			),
			
			WPVGW . '_marker_disabled'       => array(
				
				array(
					'label' => __( 'Zählmarke', WPVGW_TEXT_DOMAIN ),
				),
				array(
					'label' => __( 'Aktiv', WPVGW_TEXT_DOMAIN ),
					'where' => 'm.is_marker_disabled = 0',
				),
				array(
					'label' => __( 'Inaktiv', WPVGW_TEXT_DOMAIN ),
					'where' => 'm.is_marker_disabled = 1',
				),
			),
			
			WPVGW . '_invalid_markers'       => array(
				
				array(
					'label' => __( 'Zählm.-Format', WPVGW_TEXT_DOMAIN ),
				),
				array(
					'label' => __( 'Gültig', WPVGW_TEXT_DOMAIN ),
					'where' => $validMarkerFormatSql,
					),
				array(
					'label' => __( 'Ungültig', WPVGW_TEXT_DOMAIN ),
					'where' => "NOT ($validMarkerFormatSql)",
				),
			),
			
			WPVGW . '_post_type'             => $allowedPostTypesOptions,
			
			WPVGW . '_post_deleted'          => array(
				
				array(
					'label' => __( 'Beitrag', WPVGW_TEXT_DOMAIN ),
				),
				array(
					'label' => __( 'Nicht gelöscht', WPVGW_TEXT_DOMAIN ),
					'where' => 'm.is_post_deleted = 0',
				),
				array(
					'label' => __( 'Gelöscht', WPVGW_TEXT_DOMAIN ),
					'where' => 'm.is_post_deleted = 1',
				),
			),
			
			WPVGW . '_sufficient_characters' => array(
				
				array(
					'label' => __( 'Zeichenanzahl', WPVGW_TEXT_DOMAIN ),
				),
				array(
					'label' => __( 'Genügend', WPVGW_TEXT_DOMAIN ),
					'where' => $characterCountSufficientSql,
				),
				array(
					'label' => __( 'Zu wenig', WPVGW_TEXT_DOMAIN ),
					'where' => "NOT ($characterCountSufficientSql)",
				),
			),
			
			WPVGW . '_post_year'             => $postYearOptions,

		);


		
		$this->bulkActions = array(
			'edit'                                      => __( 'Bearbeiten', WPVGW_TEXT_DOMAIN ), 
			WPVGW . '_enable_marker'                    => __( 'Aktiv setzen', WPVGW_TEXT_DOMAIN ),
			WPVGW . '_disable_marker'                   => __( 'Inaktiv setzen', WPVGW_TEXT_DOMAIN ),
			WPVGW . '_remove_post_from_marker'          => __( 'Zuordnung aufheben', WPVGW_TEXT_DOMAIN ),
			WPVGW . '_delete_marker'                    => __( 'Löschen (nicht empfohlen)', WPVGW_TEXT_DOMAIN ),
			WPVGW . '_recalculate_post_character_count' => __( 'Zeichenanzahl neuberechnen', WPVGW_TEXT_DOMAIN ),
		);


		
		$this->urlActionUrlTemplate = esc_attr( WPVGW_AdminViewsManger::create_admin_view_url() ) . '&amp;action=%s&amp;wpvgw_marker=%s&amp;_wpvgwadminviewnonce=' . esc_attr( wp_create_nonce( 'markers' ) );

		
		parent::__construct( array(
				'singular' => 'wpvgw_marker', 
				'plural'   => 'wpvgw_markers', 
				'ajax'     => false 
			)
		);


		$this->markersManager = $markers_manager;
		$this->postsExtras = $posts_extras;
		$this->options = $options;

		$this->emptyDataText = __( '–', WPVGW_TEXT_DOMAIN );
	}


	
	protected function column_default( $row, $column_name ) {
		return esc_html( $row[$column_name] );
	}

	
	protected function column_cb( $row ) {
		return sprintf( '<input type="checkbox" name="%s[]" value="%s" />',
			$this->_args['singular'], 
			$row['id'] 
		);
	}

	
	protected function column_post_title( $row ) {
		

		
		$actions = array();
		$linkTemplate = '<a href="%s" title="%s">%s</a>';
		$jsLinkTemplate = '<a class="%s" data-object-id="%s" href="#" title="%s">%s</a>';

		$copyActions = array(
			
			array(
				'key'       => 'private_marker',
				'condition' => true,
				'class'     => WPVGW . '-markers-view-copy-private-marker',
				'object_id' => $row['id'],
				'title'     => __( 'Ermöglicht, die private Zählmarke in die Zwischenablage zu kopieren', WPVGW_TEXT_DOMAIN ),
				'text'      => __( 'Priv.', WPVGW_TEXT_DOMAIN )
			),
			
			array(
				'key'       => 'post_title',
				'condition' => $row['post_title'] !== null,
				'class'     => WPVGW . '-markers-view-copy-post-title',
				'object_id' => $row['post_id'],
				'title'     => __( 'Ermöglicht, den Beitrags-Titel in die Zwischenablage zu kopieren', WPVGW_TEXT_DOMAIN ),
				'text'      => __( 'Titel', WPVGW_TEXT_DOMAIN )
			),
			
			array(
				'key'       => 'post_content',
				'condition' => $row['post_id'] !== null,
				'class'     => WPVGW . '-markers-view-copy-post-content',
				'object_id' => $row['post_id'],
				'title'     => __( 'Ermöglicht, den Beitrags-Text in die Zwischenablage zu kopieren', WPVGW_TEXT_DOMAIN ),
				'text'      => __( 'Text', WPVGW_TEXT_DOMAIN )
			),
			
			array(
				'key'       => 'post_link',
				'condition' => $row['post_id'] !== null,
				'class'     => WPVGW . '-markers-view-copy-post-link',
				'object_id' => $row['post_id'],
				'title'     => __( 'Ermöglicht, den Beitrags-Link in die Zwischenablage zu kopieren', WPVGW_TEXT_DOMAIN ),
				'text'      => __( 'Link', WPVGW_TEXT_DOMAIN )
			),
		);

		
		foreach ( $copyActions as $copyAction ) {
			
			if ( $copyAction['condition'] ) {
				
				$actions[WPVGW . '_copy_' . $copyAction['key']] = sprintf(
					$jsLinkTemplate,
					$copyAction['class'],
					esc_attr( $copyAction['object_id'] ),
					esc_attr( $copyAction['title'] ),
					$copyAction['text']
				);
			}
		}


		if ( $row['is_marker_disabled'] == '1' )
			
			$actions[WPVGW . '_enable_marker'] = sprintf(
				$linkTemplate,
				sprintf( $this->urlActionUrlTemplate, WPVGW . '_enable_marker', $row['id'] ),
				esc_attr( __( 'Diese Zählmarke aktiv setzen', WPVGW_TEXT_DOMAIN ) ),
				__( 'Aktiv setzen', WPVGW_TEXT_DOMAIN )
			);
		else
			
			$actions[WPVGW . '_disable_marker'] = sprintf(
				$linkTemplate,
				sprintf( $this->urlActionUrlTemplate, WPVGW . '_disable_marker', $row['id'] ),
				esc_attr( __( 'Diese Zählmarke inaktiv setzen', WPVGW_TEXT_DOMAIN ) ),
				__( 'Inaktiv setzen', WPVGW_TEXT_DOMAIN )
			);

		if ( $row['post_id'] !== null )
			
			$actions[WPVGW . '_edit_post'] = sprintf(
				$linkTemplate,
				get_edit_post_link( $row['post_id'] ),
				esc_attr( __( 'Den zugeordneten Beitrag bearbeiten', WPVGW_TEXT_DOMAIN ) ),
				__( 'Bearbeiten', WPVGW_TEXT_DOMAIN )
			);


		
		
		$markerDisabledPrefix = ( $row['is_marker_disabled'] ? __( 'Inaktiv: ', WPVGW_TEXT_DOMAIN ) : '' );
		$postTitleOutput = '';
		if ( $row['post_id'] === null )
			$postTitleOutput = $markerDisabledPrefix . __( 'Kein Beitrag zugeordnet', WPVGW_TEXT_DOMAIN );
		elseif ( $row['is_post_deleted'] == '1' )
			$postTitleOutput = sprintf( __( 'gelöscht (%s)', WPVGW_TEXT_DOMAIN ),
				$row['deleted_post_title'] === null ? __( 'unbekannt', WPVGW_TEXT_DOMAIN ) : esc_html( $row['deleted_post_title'] === '' ? __( 'kein Titel)', WPVGW_TEXT_DOMAIN ) : $row['deleted_post_title'] )
			);
		elseif ( $row['post_title'] === null )
			$postTitleOutput = sprintf( '<strong><span class="wpvgw-invalid-data">%s</span></strong>', __( 'gelöscht? (Titel unbekannt)', WPVGW_TEXT_DOMAIN ) );
		else
			$postTitleOutput = sprintf( '<strong><span class="%s" title="%s"><a id="%s" href="%s" target="_blank">%s</a></span></strong>',
				$row['is_marker_disabled'] ? 'wpvgw-marker-disabled' : 'wpvgw-marker-enabled',
				$row['is_marker_disabled'] ? __( 'Die Zugriffe auf diesen Beitrag werden nicht gezählt', WPVGW_TEXT_DOMAIN ) : '',
				esc_attr( WPVGW . '-markers-view-post-title-link-' . $row['post_id'] ),
				esc_attr( get_permalink( $row['post_id'] ) ),
				esc_html( $row['post_title'] === '' ? __( '(kein Titel)', WPVGW_TEXT_DOMAIN ) : $row['post_title'] )
			);


		
		$postTypeOutput = '';
		if ( $row['post_type'] !== null ) {
			$postTypeObject = get_post_type_object( $row['post_type'] );

			if ( $postTypeObject === null )
				$postTypeOutput = sprintf(
					'<span class="wpvgw-invalid-data">%s</span>',
					sprintf( __( 'Beitrags-Typ unbekannt (%s)', WPVGW_TEXT_DOMAIN ), esc_html( $row['post_type'] ) )
				);
			else
				$postTypeOutput =
					esc_html( $postTypeObject->labels->singular_name ) .
					( $this->markersManager->is_post_type_allowed( $row['post_type'] ) ? '' : sprintf( '<br/><span class="wpvgw-invalid-data">%s</span>', __( 'Beitrags-Typ nicht zugelassen', WPVGW_TEXT_DOMAIN ) ) );
		}

		return sprintf( '%s<br/>%s',
			$postTitleOutput,
			$postTypeOutput . $this->row_actions( $actions )
		);
	}

	
	protected function column_post_date( $row ) {
		if ( $row['post_date'] == null )
			return $this->emptyDataText;

		
		return sprintf( __( '%s', WPVGW_TEXT_DOMAIN ), esc_html( date_i18n( __( 'd.m.Y', WPVGW_TEXT_DOMAIN ), strtotime( $row['post_date'] ) ) ) );
	}

	
	protected function column_up_display_name( $row ) {
		if ( $row['post_author'] === null )
			return $this->emptyDataText;

		$editUserLink = get_edit_user_link( $row['user_id'] );

		if ( ( $row['up_display_name'] === null && $row['post_id'] !== null ) || $editUserLink == '' )
			return sprintf( '<span class="wpvgw-invalid-data">%s</span>', __( 'gelöscht?', WPVGW_TEXT_DOMAIN ) );

		$invalidUserMessage = '';

		if ( !$this->markersManager->is_user_allowed( (int)$row['post_author'] ) )
			$invalidUserMessage = sprintf( '<br/><span class="wpvgw-invalid-data">%s</span>', __( 'Autor nicht zugelassen', WPVGW_TEXT_DOMAIN ) );

		if ( $row['user_id'] != null && $row['post_author'] != $row['user_id'] )
			$invalidUserMessage = sprintf( '<br/><span class="wpvgw-invalid-data">%s</span>', __( 'Autor nicht für diese Zählmarke bestimmt', WPVGW_TEXT_DOMAIN ) );

		return sprintf(
			'<a href="%s">%s</a>%s',
			get_edit_user_link( $row['post_author'] ),
			esc_html( $row['up_display_name'] ),
			$invalidUserMessage
		);
	}

	
	protected function column_um_display_name( $row ) {
		if ( $row['user_id'] === null )
			return __( 'beliebiger', WPVGW_TEXT_DOMAIN );

		$editUserLink = get_edit_user_link( $row['user_id'] );

		if ( $row['um_display_name'] === null || $editUserLink == '' )
			return sprintf( '<span class="wpvgw-invalid-data">%s</span>', __( 'gelöscht?', WPVGW_TEXT_DOMAIN ) );

		$invalidUserMessage = '';
		if ( !$this->markersManager->is_user_allowed( (int)$row['user_id'] ) )
			$invalidUserMessage = sprintf( '<br/><span class="wpvgw-invalid-data">%s</span>', __( 'Autor nicht zugelassen', WPVGW_TEXT_DOMAIN ) );

		return sprintf(
			'<a href="%s">%s</a>%s',
			$editUserLink,
			esc_html( $row['um_display_name'] ),
			$invalidUserMessage
		);
	}


	
	protected function column_marker( $row ) {
		
		return sprintf( '<span class="%s" title="%s"><abbr title="%s">%s</abbr>:&nbsp;%s<br/><abbr title="%s">%s</abbr>:&nbsp;<span id="%s">%s</span><br/><abbr title="%s">%s</abbr>:&nbsp;%s</span>',
			$row['is_marker_disabled'] ? 'wpvgw-marker-disabled' : 'wpvgw-marker-enabled',
			$row['is_marker_disabled'] ? __( 'Zählmarke ist inaktiv', WPVGW_TEXT_DOMAIN ) : '',
			__( 'Öffentliche Zählmarke', WPVGW_TEXT_DOMAIN ),
			__( 'Ö', WPVGW_TEXT_DOMAIN ),
			esc_html( $row['public_marker'] ),
			__( 'Private Zählmarke', WPVGW_TEXT_DOMAIN ),
			__( 'P', WPVGW_TEXT_DOMAIN ),
			esc_attr( WPVGW . '-markers-view-private-marker-' . $row['id'] ),
			esc_html( $row['private_marker'] === null ? $this->emptyDataText : $row['private_marker'] ),
			__( 'Server', WPVGW_TEXT_DOMAIN ),
			__( 'S', WPVGW_TEXT_DOMAIN ),
			esc_html( $row['server'] )
		);
	}

	
	protected function column_e_character_count( $row ) {

		if ( $row['e_character_count'] === null ) {
			if ( $row['is_post_deleted'] == '1' || $row['post_id'] === null )
				return $this->emptyDataText;

			return __( 'nicht berechnet', WPVGW_TEXT_DOMAIN );
		}
		else {
			$characterCount = intval( $row['e_character_count'] );

			
			if ( $this->markersManager->is_character_count_sufficient( $characterCount, $this->options->get_vg_wort_minimum_character_count() ) )
				return sprintf( __( 'genügend, %s', WPVGW_TEXT_DOMAIN ), number_format_i18n( $characterCount ) );
			else
				return sprintf(
					'<span class="wpvgw-invalid-data">%s</span>',
					sprintf( __( 'zu wenig, %s', WPVGW_TEXT_DOMAIN ), number_format_i18n( $characterCount ) )
				);
		}
	}


	
	public function get_columns() {
		return $this->columns;
	}

	
	public function get_sortable_columns() {
		return $this->sortableColumnsWithSlugs;
	}

	
	public function extra_tablenav( $which ) {
		
		if ( $which != 'top' )
			return;

		?>
		<div class="alignleft actions"><?php

		WPVGW_Helper::render_html_selects( $this->filterableColumnsSelects );
		submit_button( __( 'Auswählen', WPVGW_TEXT_DOMAIN ), 'button', WPVGW . '_filter_submit', false, array( 'id' => WPVGW . '_filter_submit' ) );

		?></div><?php
	}

	
	public function get_bulk_actions() {
		return $this->bulkActions;
	}

	
	public function get_views() {
		return $this->viewLinks;
	}

	
	private function prepare_items_internal( $current_page, $rowsPerPage, $include_post_content, $get_total_markers_count, &$total_markers_count ) {
		
		global $wpdb;

		
		if ( ( $current_page !== null && $rowsPerPage === null ) || ( $current_page === null && $rowsPerPage !== null ) )
			throw new Exception( 'The arguments $current_page and $rowsPerPage have to be integers or both null.' );

		
		$orderBy = ( isset( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'id';
		if ( !in_array( $orderBy, $this->sortableColumns ) )
			$orderBy = 'id';

		
		$order = ( isset( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc';
		if ( $order != 'asc' && $order != 'desc' )
			$order = 'asc';

		
		if ( isset( $_REQUEST['s'] ) && $_REQUEST['s'] != '' ) {
			$searchString = '%' . $_REQUEST['s'] . '%';

			$safeSearchString = WPVGW_Helper::prepare_with_null( '%s', $searchString );
			$sqlWhere =
				"
				p.post_title LIKE $safeSearchString OR
				m.public_marker LIKE $safeSearchString OR
				m.private_marker LIKE $safeSearchString OR
				m.server LIKE $safeSearchString
				";
		}
		else {
			$sqlWhere = '0=0';
		}

		
		$sqlFilters = '';
		foreach ( $this->filterableColumnsSelects as $htmlSelect => $options ) {
			if ( isset( $_REQUEST[$htmlSelect] ) ) {
				$currentOption = intval( $_REQUEST[$htmlSelect] );

				if ( $currentOption != 0 && array_key_exists( $currentOption, $options ) )
					
					$sqlFilters .= ' AND (' . $options[$currentOption]['where'] . ')';
			}
		}
		$sqlWhere .= $sqlFilters;


		
		$markersTableName = $this->markersManager->get_markers_table_name();
		$postExtrasTableName = $this->postsExtras->get_post_extras_table_name();

		
		$postContentColumn = ( $include_post_content ? 'p.post_content, ' : '' );
		
		$sqlSelect =
			"
			m.id, m.post_id, m.user_id, m.public_marker, m.private_marker, m.server, m.is_marker_disabled, m.is_post_deleted, m.deleted_post_title,
			p.post_author, p.post_title, p.post_type, p.post_date, $postContentColumn
			e.character_count as e_character_count,
			um.display_name as um_display_name,
			up.display_name as up_display_name
			";

		
		$sqlFrom =
			"
			$markersTableName AS m
			LEFT OUTER JOIN $wpdb->posts AS p ON m.post_id = p.ID
			LEFT OUTER JOIN $postExtrasTableName AS e ON m.post_id = e.post_id
			LEFT OUTER JOIN $wpdb->users AS um ON m.user_id = um.ID
			LEFT OUTER JOIN $wpdb->users AS up ON p.post_author = up.ID
			";

		$limit = '';
		
		if ( $current_page !== null )
			
			$limit = sprintf( 'LIMIT %s, %s',
				( $current_page - 1 ) * $rowsPerPage,
				$rowsPerPage
			);

		
		$markers = $wpdb->get_results(
			"SELECT $sqlSelect FROM $sqlFrom WHERE $sqlWhere ORDER BY $orderBy $order $limit",
			ARRAY_A
		);

		if ( $wpdb->last_error !== '' )
			WPVGW_Helper::throw_database_exception();

		
		if ( $get_total_markers_count )
			
			$total_markers_count = $wpdb->get_var(
				"SELECT COUNT(*) FROM $sqlFrom WHERE $sqlWhere"
			);
		else
			
			$total_markers_count = null;

		if ( $wpdb->last_error !== '' )
			WPVGW_Helper::throw_database_exception();


		return $markers;
	}

	
	public function prepare_items() {
		
		$rowsPerPage = $this->options->get_number_of_markers_per_page();

		
		$this->_column_headers = $this->get_column_info();

		
		$current_page = $this->get_pagenum();

		
		$markers = $this->prepare_items_internal( $current_page, $rowsPerPage, false, true, $totalMarkersCount );

		
		$this->items = $markers;


		
		$this->set_pagination_args( array(
				'total_items' => $totalMarkersCount, 
				'per_page'    => $rowsPerPage, 
				'total_pages' => ceil( $totalMarkersCount / $rowsPerPage ) 
			)
		);
	}


	
	public function get_all_items( $include_post_content ) {
		
		return $this->prepare_items_internal( null, null, $include_post_content, false, $totalMarkersCount );
	}

}
