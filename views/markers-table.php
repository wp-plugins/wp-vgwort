<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: 2014 Ronny Harbich
 * License: GPLv2 or later
 */


require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );


/**
 * Represents the markers table that extends the WordPress’ core WP_List_Table class.
 */
class WPVGW_MarkersListTable extends WP_List_Table {

	/**
	 * @var WPVGW_MarkersManager A markers manager.
	 */
	private $markersManager;
	/**
	 * @var WPVGW_PostsExtras Posts extras.
	 */
	private $postsExtras;
	/**
	 * @var WPVGW_Options Plugin options and settings.
	 */
	private $options;
	/**
	 * @var array The array of columns that will be displayed in the table: $column_slug => $headline, …
	 */
	private $columns;
	/**
	 * @var array The array of columns slugs from {@link $columns} that are sortable.
	 */
	private $sortableColumns;
	/**
	 * @var array
	 */
	private $sortableColumnsWithSlugs;
	/**
	 * @var array The array of filter options to be used for HTML selects.
	 */
	private $filterableColumnsSelects;
	/**
	 * @var array The array of bulk actions: $action_slug => $label
	 */
	private $bulkActions;
	/**
	 * @var array The array of view links: $id => $link
	 */
	private $viewLinks;
	/**
	 * @var string HTML template for the single row actions. First %s represents the action slug, second %s represents the marker ID.
	 */
	private $urlActionUrlTemplate;
	/**
	 * @var string Text that should be output if some data is empty (null).
	 */
	private $emptyDataText;


	/**
	 * @var array Used in base class.
	 */
	protected $_column_headers;

	/**
	 * Create a new instance of {@link WPVGW_MarkersListTable}.
	 *
	 * @param WPVGW_MarkersManager $markers_manager A markers manager.
	 * @param WPVGW_PostsExtras $posts_extras The posts extras.
	 * @param WPVGW_Options $options The options.
	 */
	public function __construct( WPVGW_MarkersManager $markers_manager, WPVGW_PostsExtras $posts_extras, WPVGW_Options $options ) {
		// columns that will be displayed in the table
		$this->columns = array(
			'cb'                => '<input type="checkbox" />', // render a checkbox instead of text
			//'id'              => __( 'ID', WPVGW_TEXT_DOMAIN ),
			'post_title'        => __( 'Beitrag', WPVGW_TEXT_DOMAIN ),
			'post_date'         => __( 'Datum', WPVGW_TEXT_DOMAIN ),
			'e_character_count' => __( 'Zeichenanzahl', WPVGW_TEXT_DOMAIN ),
			//'post_type'         => __( 'Beitrags-Typ', WPVGW_TEXT_DOMAIN ),
			'up_display_name'   => __( 'Beitrags-Autor', WPVGW_TEXT_DOMAIN ),
			'marker'            => __( 'Zählmarke', WPVGW_TEXT_DOMAIN ),
		);

		// columns that are sortable
		$this->sortableColumns = array(
			'post_title',
			'post_date',
			//'post_type', // does not work because it will only sort the post type slugs from database
			'up_display_name',
			'e_character_count',
		);

		$this->sortableColumnsWithSlugs = array();
		// 'slugs' => array('data_values',bool)
		foreach ( $this->sortableColumns as $column ) {
			$this->sortableColumnsWithSlugs[$column] = array( $column, false );
		}


		// post type; used for HTML select (filter)
		$allowedPostTypes = $markers_manager->get_allowed_post_types();
		$sqlAllowedPostTypesSql = WPVGW_Helper::prepare_with_null( WPVGW_Helper::sql_values( $allowedPostTypes ), $allowedPostTypes );
		$allowedPostTypesOptions = array(
			// default
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

		// iterate allowed post types
		foreach ( $allowedPostTypes as $allowedPostType ) {
			$postTypeObject = get_post_type_object( $allowedPostType );

			if ( $postTypeObject !== null )
				// add post type HTML select (filter)
				$allowedPostTypesOptions[] =
					array(
						'label' => sprintf( __( 'Typ: %s', WPVGW_TEXT_DOMAIN ), $postTypeObject->labels->name ),
						'where' => WPVGW_Helper::prepare_with_null( 'p.post_type = %s', $allowedPostType ),
					);
		}

		// iterate year for post year filter
		$postYearOptions = array(
			// default
			array(
				'label' => __( 'Beitrags-Jahr', WPVGW_TEXT_DOMAIN ),
			),
		);
		// get current year
		$currentYear = current_time( 'Y' );
		// iterate 10 years ago and 2 years in future
		for ( $year = $currentYear + 2; $year >= $currentYear - 10; $year-- ) {
			$postYearOptions[] =
				array(
					'label' => sprintf( __( '%s', WPVGW_TEXT_DOMAIN ), $year ),
					'where' => WPVGW_Helper::prepare_with_null( 'YEAR(p.post_date) = %d', $year ),
				);
		}


		// SQL to test for sufficient characters count of posts
		$characterCountSufficientSql = $markers_manager->is_character_count_sufficient_sql( 'e.character_count', $options->get_vg_wort_minimum_character_count() );

		// Warning: Indexes (integers) of the array items are used in the view links!
		// HTML selects to filter markers
		$this->filterableColumnsSelects = array(
			// marker disabled filter
			WPVGW . '_marker_disabled'       => array(
				// default
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
			// post type filter
			WPVGW . '_post_type'             => $allowedPostTypesOptions,
			// post deleted filter
			WPVGW . '_post_deleted'          => array(
				// default
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
			// post sufficient character count filter
			WPVGW . '_sufficient_characters' => array(
				// default
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
			// post year filter
			WPVGW . '_post_year'             => $postYearOptions,

		);


		// set bulk actions
		$this->bulkActions = array(
			'edit'                                      => __( 'Bearbeiten', WPVGW_TEXT_DOMAIN ), // special JavaScript bulk edit; have to be named "edit"
			WPVGW . '_enable_marker'                    => __( 'Aktiv setzen', WPVGW_TEXT_DOMAIN ),
			WPVGW . '_disable_marker'                   => __( 'Inaktiv setzen', WPVGW_TEXT_DOMAIN ),
			WPVGW . '_remove_post_from_marker'          => __( 'Zuordnung aufheben', WPVGW_TEXT_DOMAIN ),
			WPVGW . '_delete_marker'                    => __( 'Löschen (nicht empfohlen)', WPVGW_TEXT_DOMAIN ),
			WPVGW . '_recalculate_post_character_count' => __( 'Zeichenanzahl neuberechnen', WPVGW_TEXT_DOMAIN ),
		);


		// Warning: must be escaped!
		$this->urlActionUrlTemplate = esc_attr( WPVGW_AdminViewsManger::create_admin_view_url() ) . '&amp;action=%s&amp;wpvgw_marker=%s&amp;_wpvgwadminviewnonce=' . esc_attr( wp_create_nonce( 'markers' ) );

		//Set parent defaults
		parent::__construct( array(
				'singular' => 'wpvgw_marker', //singular name of the listed records
				'plural'   => 'wpvgw_markers', //plural name of the listed records
				'ajax'     => false //does this table support ajax?
			)
		);


		$this->markersManager = $markers_manager;
		$this->postsExtras = $posts_extras;
		$this->options = $options;

		$this->emptyDataText = __( '–', WPVGW_TEXT_DOMAIN );
	}


	/**
	 * This method is called when the parent class can't find a method specifically build for a given column.
	 *
	 * @param array $row A singular table row, i. e., the data of the row as array.
	 * @param string $column_name The name/slug of the column to be processed.
	 *
	 * @return string Text or HTML to be placed inside the column. The value is HTML escaped.
	 */
	protected function column_default( $row, $column_name ) {
		return esc_html( $row[$column_name] );
	}

	/**
	 * For displaying checkboxes or using bulk actions.
	 * The "cb" column is given special treatment when columns are processed.
	 *
	 * @see WP_List_Table::single_row_columns()
	 *
	 * @param array $row A singular table row, i. e., the data of the row as array.
	 *
	 * @return string A HTML checkbox for the current row.
	 */
	protected function column_cb( $row ) {
		return sprintf( '<input type="checkbox" name="%s[]" value="%s" />',
			$this->_args['singular'], // the table's singular label
			$row['id'] // the value of the checkbox should be the record's id
		);
	}

	/**
	 * Gets the post title column. Outputs row actions.
	 *
	 * @param array $row A singular table row, i. e., the data of the row as array.
	 *
	 * @return string Text or HTML to be placed inside the column. The value is HTML escaped.
	 */
	protected function column_post_title( $row ) {
		//$markerId = $item['id'];

		// build row actions
		$actions = array();
		$linkTemplate = '<a href="%s" title="%s">%s</a>';

		if ( $row['is_marker_disabled'] == '1' )
			// enable marker link
			$actions[WPVGW . '_enable_marker'] = sprintf(
				$linkTemplate,
				sprintf( $this->urlActionUrlTemplate, WPVGW . '_enable_marker', $row['id'] ),
				esc_attr( __( 'Diese Zählmarke aktiv setzen', WPVGW_TEXT_DOMAIN ) ),
				__( 'Aktiv setzen', WPVGW_TEXT_DOMAIN )
			);
		else
			// disable marker link
			$actions[WPVGW . '_disable_marker'] = sprintf(
				$linkTemplate,
				sprintf( $this->urlActionUrlTemplate, WPVGW . '_disable_marker', $row['id'] ),
				esc_attr( __( 'Diese Zählmarke inaktiv setzen', WPVGW_TEXT_DOMAIN ) ),
				__( 'Inaktiv setzen', WPVGW_TEXT_DOMAIN )
			);

		if ( $row['post_id'] !== null )
			// edit post link
			$actions[WPVGW . '_edit_post'] = sprintf(
				$linkTemplate,
				get_edit_post_link( $row['post_id'] ),
				esc_attr( __( 'Den zugeordneten Beitrag bearbeiten', WPVGW_TEXT_DOMAIN ) ),
				__( 'Bearbeiten', WPVGW_TEXT_DOMAIN )
			);


		// post title
		// disabled marker prefix for title
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


		// post type
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

	/**
	 * Gets the post date column.
	 *
	 * @param array $row A singular table row, i. e., the data of the row as array.
	 *
	 * @return string Text or HTML to be placed inside the column. The value is HTML escaped.
	 */
	protected function column_post_date( $row ) {
		if ( $row['post_date'] == null )
			return $this->emptyDataText;

		// format post date
		return sprintf( __( '%s', WPVGW_TEXT_DOMAIN ), esc_html( date_i18n( __( 'd.m.Y', WPVGW_TEXT_DOMAIN ), strtotime( $row['post_date'] ) ) ) );
	}

	/**
	 * Gets the post user (author) column.
	 *
	 * @param array $row A singular table row, i. e., the data of the row as array.
	 *
	 * @return string Text or HTML to be placed inside the column. The value is HTML escaped.
	 */
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

	/**
	 * Gets the marker user (author) column.
	 *
	 * @param array $row A singular table row, i. e., the data of the row as array.
	 *
	 * @return string Text or HTML to be placed inside the column. The value is HTML escaped.
	 */
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


	/**
	 * Gets the maker column.
	 *
	 * @param array $row A singular table row, i. e., the data of the row as array.
	 *
	 * @return string Text or HTML to be placed inside the column. The value is HTML escaped.
	 */
	protected function column_marker( $row ) {
		// return the title contents
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

	/**
	 * Gets the post character count column.
	 *
	 * @param array $row A singular table row, i. e., the data of the row as array.
	 *
	 * @return string Text or HTML to be placed inside the column. The value is HTML escaped.
	 */
	protected function column_e_character_count( $row ) {

		if ( $row['e_character_count'] === null ) {
			if ( $row['is_post_deleted'] == '1' || $row['post_id'] === null )
				return $this->emptyDataText;

			return __( 'nicht berechnet', WPVGW_TEXT_DOMAIN );
		}
		else {
			$characterCount = intval( $row['e_character_count'] );

			// echo character count and too less or enough characters indicator
			if ( $this->markersManager->is_character_count_sufficient( $characterCount, $this->options->get_vg_wort_minimum_character_count() ) )
				return sprintf( __( 'genügend, %s', WPVGW_TEXT_DOMAIN ), number_format_i18n( $characterCount ) );
			else
				return sprintf(
					'<span class="wpvgw-invalid-data">%s</span>',
					sprintf( __( 'zu wenig, %s', WPVGW_TEXT_DOMAIN ), number_format_i18n( $characterCount ) )
				);
		}
	}


	/**
	 * @inheritdoc
	 */
	public function get_columns() {
		return $this->columns;
	}

	/**
	 * @inheritdoc
	 */
	public function get_sortable_columns() {
		return $this->sortableColumnsWithSlugs;
	}

	/**
	 * @inheritdoc
	 */
	public function extra_tablenav( $which ) {
		// consider top navigation only
		if ( $which != 'top' )
			return;

		?>
		<div class="alignleft actions"><?php

		WPVGW_Helper::render_html_selects( $this->filterableColumnsSelects );
		submit_button( __( 'Auswählen', WPVGW_TEXT_DOMAIN ), 'button', WPVGW . '_filter_submit', false, array( 'id' => WPVGW . '_filter_submit' ) );

		?></div><?php
	}

	/**
	 * @inheritdoc
	 */
	public function get_bulk_actions() {
		return $this->bulkActions;
	}

	/**
	 * @inheritdoc
	 */
	public function get_views() {
		return $this->viewLinks;
	}

	/**
	 * Returns the markers and posts data considering the filters, sorts and limits (can be disabled) specified by the table (POST data actually).
	 *
	 * @param int|null $current_page The current page or null (if null {@link $rowsPerPage} has to be null too).
	 * @param int|null $rowsPerPage The rows per page or null (if null {@link $$current_page} has to be null too).
	 * @param bool $include_post_content Returned data will contain post contents if true, otherwise it is not contained.
	 * @param bool $get_total_markers_count Sets whether the total number markers/rows will be returned. If true, an extra SQL query is will be executed.
	 * @param int $total_markers_count Returns the total number markers/rows or null if {@link $get_total_markers_count} is false.
	 *
	 * @throws Exception Thrown if arguments are invalid or a database error occurred.
	 * @return array An array of marker and post data.
	 */
	private function prepare_items_internal( $current_page, $rowsPerPage, $include_post_content, $get_total_markers_count, &$total_markers_count ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		// argument validation
		if ( ( $current_page !== null && $rowsPerPage === null ) || ( $current_page === null && $rowsPerPage !== null ) )
			throw new Exception( 'The arguments $current_page and $rowsPerPage have to be integers or both null.' );

		// get order by column from HTTP request
		$orderBy = ( isset( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'id';
		if ( !in_array( $orderBy, $this->sortableColumns ) )
			$orderBy = 'id';

		// get sort order (ascending or descending) from HTTP request
		$order = ( isset( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc';
		if ( $order != 'asc' && $order != 'desc' )
			$order = 'asc';

		// get search string from HTTP request
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

		// create SQL WHERE filter from HTML request (i. e. from the HTML select filters)
		$sqlFilters = '';
		foreach ( $this->filterableColumnsSelects as $htmlSelect => $options ) {
			if ( isset( $_REQUEST[$htmlSelect] ) ) {
				$currentOption = intval( $_REQUEST[$htmlSelect] );

				if ( $currentOption != 0 && array_key_exists( $currentOption, $options ) )
					// append filter
					$sqlFilters .= ' AND (' . $options[$currentOption]['where'] . ')';
			}
		}
		$sqlWhere .= $sqlFilters;


		// get database table names
		$markersTableName = $this->markersManager->get_markers_table_name();
		$postExtrasTableName = $this->postsExtras->get_post_extras_table_name();

		// include post content in SQL SELECT?
		$postContentColumn = ( $include_post_content ? 'p.post_content, ' : '' );
		// SQL SELECT
		$sqlSelect =
			"
			m.id, m.post_id, m.user_id, m.public_marker, m.private_marker, m.server, m.is_marker_disabled, m.is_post_deleted, m.deleted_post_title,
			p.post_author, p.post_title, p.post_type, p.post_date, $postContentColumn
			e.character_count as e_character_count,
			um.display_name as um_display_name,
			up.display_name as up_display_name
			";

		// SQL FROM
		$sqlFrom =
			"
			$markersTableName AS m
			LEFT OUTER JOIN $wpdb->posts AS p ON m.post_id = p.ID
			LEFT OUTER JOIN $postExtrasTableName AS e ON m.post_id = e.post_id
			LEFT OUTER JOIN $wpdb->users AS um ON m.user_id = um.ID
			LEFT OUTER JOIN $wpdb->users AS up ON p.post_author = up.ID
			";

		$limit = '';
		// do we have a limit?
		if ( $current_page !== null )
			// calculate SQL LIMIT (the from part)
			$limit = sprintf( 'LIMIT %s, %s',
				( $current_page - 1 ) * $rowsPerPage,
				$rowsPerPage
			);

		// get markers (count is limited) from database
		$markers = $wpdb->get_results(
			"SELECT $sqlSelect FROM $sqlFrom WHERE $sqlWhere ORDER BY $orderBy $order $limit",
			ARRAY_A
		);

		if ( $wpdb->last_error !== '' )
			WPVGW_Helper::throw_database_exception();

		// count total number of markers?
		if ( $get_total_markers_count )
			// count total number of markers (same query like before but it counts the rows only)
			$total_markers_count = $wpdb->get_var(
				"SELECT COUNT(*) FROM $sqlFrom WHERE $sqlWhere"
			);
		else
			// markers were not count
			$total_markers_count = null;

		if ( $wpdb->last_error !== '' )
			WPVGW_Helper::throw_database_exception();


		return $markers;
	}

	/**
	 * Returns the markers and posts data considering the filters, sorts and limits specified by the table (POST data actually).
	 *
	 * @throws Exception Thrown if  a database error occurred.
	 */
	public function prepare_items() {
		// decide how many records per page to show
		$rowsPerPage = $this->options->get_number_of_markers_per_page();

		/*
		 * Build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = $this->get_column_info();

		// get current page
		$current_page = $this->get_pagenum();

		// get items/markers
		$markers = $this->prepare_items_internal( $current_page, $rowsPerPage, false, true, $totalMarkersCount );

		// add markers to the items array, i. e., the rows array
		$this->items = $markers;


		// register pagination options and calculations
		$this->set_pagination_args( array(
				'total_items' => $totalMarkersCount, // total number of rows
				'per_page'    => $rowsPerPage, // rows to show per page
				'total_pages' => ceil( $totalMarkersCount / $rowsPerPage ) // calculate the total number of pages
			)
		);
	}


	/**
	 * Returns the markers and posts data considering the filters and sorts specified by the table (POST data actually) but not the limits.
	 * Useful for export functions.
	 *
	 * @param bool $include_post_content Returned data will contain post contents if true, otherwise it is not contained.
	 *
	 * @throws Exception Thrown a database error occurred.
	 * @return array An array of all marker and post data.
	 */
	public function get_all_items( $include_post_content ) {
		// TODO: WP_Query used in this function will store all database results in array which can cause too much memory consumption (WTF! Why no iterated fetch?)
		return $this->prepare_items_internal( null, null, $include_post_content, false, $totalMarkersCount );
	}

}
