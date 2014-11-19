<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: 2014 Ronny Harbich
 * License: GPLv2 or later
 */


/**
 * Integrates markers features into the WordPress’ post table (GUI, not database). It adds table columns and (bulk) actions for markers.
 */
class WPVGW_PostTableView extends WPVGW_ViewBase {
	/**
	 * @var string The database table column alias for the character count column in the posts extras table. Must be unique in relation to WordPress names.
	 */
	private $characterCountColumnName;
	/**
	 * @var string The database table column alias for the posts ID column in the markers table. Must be unique in relation to WordPress names.
	 */
	private $postIdColumnName;
	/**
	 * @var string The database table column alias for the marker disabled column. Must be unique in relation to WordPress names.
	 */
	private $isMarkerDisabledColumnName;
	/**
	 * @var string|null The post type of the posts that are shown by the WordPress’ post table (GUI) currently.
	 */
	private $postType = null;
	/**
	 * @var array Filters for the marker columns: $filter_name => array( array( 'label' => $a_label), array( 'label' => $a_label,'where' => $sql_where),
	)
	 */
	private $filters;
	/**
	 * @var array An array of admin messages: array( 'message' => $message, 'type' => $type )
	 */
	private $adminMessages = array();


	/**
	 * @param string $value The post type of the posts that are shown by the WordPress’ post table (GUI) currently. Must be set before {link init()} is called.
	 */
	public function set_post_type( $value ) {
		$this->postType = $value;
	}


	/**
	 * Creates a new instance of {@link WPVGW_PostTableView}.
	 *
	 * @param WPVGW_MarkersManager $markers_manager A markers manager.
	 * @param WPVGW_PostsExtras $posts_extras The posts extras.
	 * @param WPVGW_Options $options The options.
	 */
	public function __construct( WPVGW_MarkersManager $markers_manager, WPVGW_PostsExtras $posts_extras, WPVGW_Options $options ) {
		parent::__construct( $markers_manager, $options, $posts_extras );

		$this->characterCountColumnName = WPVGW . '_posts_extras_character_count';
		$this->postIdColumnName = WPVGW . '_markers_post_id';
		$this->isMarkerDisabledColumnName = WPVGW . '_markers_is_marker_disabled';

		// hook marker actions
		add_action( 'admin_action_' . WPVGW . '_add_marker', array( $this, 'do_add_marker_action' ) );

		// add bulk marker actions
		add_action( 'admin_action_' . WPVGW . '_add_markers', array( $this, 'do_add_markers_action' ) );
		add_action( 'admin_action_' . WPVGW . '_remove_markers', array( $this, 'do_remove_markers_action' ) );
	}


	/**
	 * Initializes the view. This function should be called before using the view.
	 * {@link set_post_type()} must be called before calling this function.
	 *
	 * @throws Exception Thrown if {@link set_post_type()} was not called before calling this function.
	 */
	public function init() {
		if ( $this->postType === null )
			throw new Exception( 'Post type must be set before calling init().' );

		// has to be called
		parent::init_base(
		// javascript data
			array(
				array(
					'file'         => 'views/post-table-view.js',
					'slug'         => 'post-table-view',
					'dependencies' => array( 'jquery' ),
					'localize'     => array(
						'object_name' => 'translations',
						'data'        => array(
							'add_markers_title'     => __( 'Zählmarke zuordnen', WPVGW_TEXT_DOMAIN ),
							'show_remove_markers'   => current_user_can( 'manage_options' ), // allow admin users only
							'remove_markers_title'  => __( 'Zählmarken-Zuordnung aufheben', WPVGW_TEXT_DOMAIN ),
						)
					)
				)
			)
		);


		// get database table names
		$postsExtrasTableName = $this->postsExtras->get_post_extras_table_name();
		$markersTableName = $this->markersManager->get_markers_table_name();

		// get SQL for sufficient character count
		$characterCountSufficientSql = $this->markersManager->is_character_count_sufficient_sql( $postsExtrasTableName . '.character_count', $this->options->get_vg_wort_minimum_character_count() );

		$this->filters = array(
			// sufficient character count filter
			WPVGW . '_sufficient' => array(
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
			// marker related filter
			WPVGW . '_marker'     => array(
				// default
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

		// hooks that adds further columns to the WordPress’ post table (GUI)
		add_filter( 'manage_' . $this->postType . '_posts_columns', array( $this, 'on_add_column' ) );
		add_action( 'manage_' . $this->postType . '_posts_custom_column', array( $this, 'on_render_column' ), 10, 2 );
		add_filter( 'manage_edit-' . $this->postType . '_sortable_columns', array( $this, 'on_register_sortable_column' ) );

		// hooks that manipulate the SQL that fills the WordPress’ post table (GUI)
		add_filter( 'posts_fields', array( $this, 'on_wp_query_posts_fields' ) );
		add_filter( 'posts_join', array( $this, 'on_wp_query_posts_join' ) );
		add_filter( 'posts_where', array( $this, 'on_wp_query_posts_where' ) );
		add_filter( 'posts_orderby', array( $this, 'on_wp_query_posts_order_by' ) );

		// hooks that renders the attached filters (for markers)
		add_action( 'restrict_manage_posts', array( $this, 'on_render_filter_html' ) );

		// hooks adds post row actions
		add_filter( 'post_row_actions', array( $this, 'on_add_row_actions' ), 10, 2 );
		add_filter( 'page_row_actions', array( $this, 'on_add_row_actions' ), 10, 2 );

		// hook for admin messages
		add_action( 'admin_notices', array( $this, 'on_admin_notices' ) );
	}

	/**
	 * Adds an admin message.
	 *
	 * @param string $message An admin message, e. g., an error message.
	 * @param int $type One of the constants defined in {@link WPVGW_ErrorType}.
	 */
	private function add_admin_message( $message, $type = WPVGW_ErrorType::Error ) {
		$this->adminMessages[] = array( 'message' => $message, 'type' => $type );
	}

	/**
	 * Manipulates the SQL SELECT that fills the WordPress’ post table (GUI) by adding marker related fields.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 *
	 * @param string $fields_statement The original SQL SELECT.
	 *
	 * @return string The new SQL SELECT.
	 */
	public function on_wp_query_posts_fields( $fields_statement ) {
		// get database table names
		$postsExtrasTableName = $this->postsExtras->get_post_extras_table_name();
		$markersTableName = $this->markersManager->get_markers_table_name();

		// manipulate SQL SELECT; add marker related fields
		$fields_statement .=
			", {$postsExtrasTableName}.character_count AS {$this->characterCountColumnName}, {$markersTableName}.post_id AS {$this->postIdColumnName}, {$markersTableName}.is_marker_disabled AS {$this->isMarkerDisabledColumnName}";

		return $fields_statement;
	}

	/**
	 * Manipulates the SQL JOIN that fills the WordPress’ post table (GUI) by adding marker related tables.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 *
	 * @param string $join_statement The original SQL JOIN.
	 *
	 * @return string The new SQL JOIN.
	 */
	public function on_wp_query_posts_join( $join_statement ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		// get database table names
		$postsExtrasTableName = $this->postsExtras->get_post_extras_table_name();
		$markersTableName = $this->markersManager->get_markers_table_name();

		// manipulate SQL JOIN; add marker related tables
		$join_statement .=
			" LEFT OUTER JOIN $postsExtrasTableName ON {$postsExtrasTableName}.post_id = {$wpdb->posts}.ID " .
			"LEFT OUTER JOIN $markersTableName ON {$markersTableName}.post_id = {$wpdb->posts}.ID";

		return $join_statement;
	}

	/**
	 * Manipulates the SQL WHERE that fills the WordPress’ post table (GUI) by adding marker related filters.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 *
	 * @param string $where_statement The original SQL WHERE.
	 *
	 * @return string The new SQL WHERE.
	 */
	public function on_wp_query_posts_where( $where_statement ) {
		$sqlFilters = '';

		// iterate marker filters
		foreach ( $this->filters as $htmlSelect => $options ) {
			if ( isset( $_REQUEST[$htmlSelect] ) ) {
				// get current filter option (index)
				$currentOption = intval( $_REQUEST[$htmlSelect] );

				if ( $currentOption != 0 && array_key_exists( $currentOption, $options ) )
					// append SQL WHERE condition
					$sqlFilters .= ' AND ' . $options[$currentOption]['where'];
			}
		}

		// append new SQL WHERE conditions
		return $where_statement . $sqlFilters;
	}

	/**
	 * Replaces the SQL ORDER BY that fills the WordPress’ post table (GUI) by adding marker related fields.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 *
	 * @param string $order_by_statement The original SQL ORDER BY.
	 *
	 * @return string The new SQL ORDER BY.
	 */
	public function on_wp_query_posts_order_by( $order_by_statement ) {
		// get order by value from URL
		$orderBy = get_query_var( 'orderby' );

		// only care about marker columns
		if ( $orderBy != $this->characterCountColumnName )
			return $order_by_statement;

		// get order direction (ascending or descending) value from URL
		$order = strtolower( get_query_var( 'order' ) );

		if ( !( $order == 'asc' || $order == 'desc' ) ) {
			$order = 'asc';
		}

		// character count column
		$asColumnName = $this->characterCountColumnName;

		// new SQL ORDER BY
		$order_by_statement = "$asColumnName $order";

		return $order_by_statement;
	}


	/**
	 * Adds columns to the WordPress’ post table (GUI).
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 *
	 * @param array $columns An array of columns
	 *
	 * @return array A new array of columns.
	 */
	public function on_add_column( $columns ) {
		// add character count column
		$columns[$this->characterCountColumnName] = __( 'Zeichen', WPVGW_TEXT_DOMAIN );

		return $columns;
	}

	/**
	 * Registers columns for WordPress’ post table (GUI) that are sortable.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 *
	 * @param array $columns An array of columns that are allowed to be sorted.
	 *
	 * @return array A new array of columns that are allowed to be sorted.
	 */
	public function on_register_sortable_column( $columns ) {
		// allow character count column to be sortable
		$columns[$this->characterCountColumnName] = $this->characterCountColumnName;

		return $columns;
	}


	/**
	 * Renders the marker columns in WordPress’ post table (GUI).
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 *
	 * @param string $column_name The column name.
	 * @param int $post_id The post ID of the current post in the WordPress’ post table (GUI).
	 */
	public function on_render_column( $column_name, $post_id ) {
		// only care about marker columns
		if ( $column_name != $this->characterCountColumnName )
			return;

		// get post from post ID
		$post = get_post( $post_id );

		// post author allowed?
		if ( !$this->markersManager->is_user_allowed( (int)$post->post_author ) ) {
			_e( 'Autor nicht zugelassen', WPVGW_TEXT_DOMAIN );
		}
		else {
			// get data that was retrieved by manipulated SQL
			$characterCount = $post->{$this->characterCountColumnName};
			$hasMarker = $post->{$this->postIdColumnName} !== null;
			$isMakerDisabled = $post->{$this->isMarkerDisabledColumnName} == '1';

			// echo character count and too less or enough characters indicator
			if ( $this->markersManager->is_character_count_sufficient( $characterCount, $this->options->get_vg_wort_minimum_character_count() ) )
				echo( sprintf( __( 'genügend, %s', WPVGW_TEXT_DOMAIN ), number_format_i18n( $characterCount ) ) );
			else
				echo( sprintf( __( 'zu wenig, %s', WPVGW_TEXT_DOMAIN ), number_format_i18n( $characterCount ) ) );

			// line break
			echo( '<br />' );

			// echo post has marker assigned or not
			if ( $hasMarker )
				_e( 'Zählmarke zugeordnet', WPVGW_TEXT_DOMAIN );
			elseif ( $this->markersManager->is_character_count_sufficient( $characterCount, $this->options->get_vg_wort_minimum_character_count() ) )
				echo( sprintf( '<em>%s</em>', __( 'Zählmarke möglich', WPVGW_TEXT_DOMAIN ) ) );

			// echo marker is disabled?
			if ( $isMakerDisabled ) {
				// line break
				echo( '<br />' );
				_e( 'Zählmarke inaktiv', WPVGW_TEXT_DOMAIN );
			}
		}
	}

	/**
	 * Renders the HTML filter controls for markers.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 */
	public function on_render_filter_html() {
		WPVGW_Helper::render_html_selects( $this->filters );
	}


	/**
	 * Adds new actions to a post row in the WordPress’ post table (GUI).
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 *
	 * @param array $actions An array of actions.
	 * @param WP_Post $post The current post in the WordPress’ post table (GUI).
	 *
	 * @return array An array of new actions.
	 */
	public function on_add_row_actions( $actions, $post ) {
		// check if current user has the permission to edit this post
		$postTypeObject = get_post_type_object( $post->post_type );
		if ( !current_user_can( $postTypeObject->cap->edit_post, $post->ID ) )
			return $actions;

		// add action only if current post author is allowed
		if ( !$this->markersManager->is_user_allowed( (int)$post->post_author ) )
			return $actions;


		// does current post has a maker?
		$hasMarker = ( $post->{$this->postIdColumnName} !== null );

		if ( !$hasMarker ) {
			// create action as HTML link
			$action = sprintf(
				'<a href="%s" title="%s">%s</a>',
				wp_nonce_url( admin_url( 'admin.php?action=' . WPVGW . '_add_marker&amp;post=' . $post->ID ), WPVGW . '_add_marker' ),
				__( 'Diesem Beitrag automatisch eine Zählmarke zuordnen', WPVGW_TEXT_DOMAIN ),
				__( 'Zählmarke zuordnen', WPVGW_TEXT_DOMAIN )
			);

			// add new action to actions
			$actions[WPVGW . '_add_marker'] = $action;
		}

		return $actions;
	}

	/**
	 * Aborts the current script and redirects (via HTTP) to the page before.
	 */
	private function redirect_to_last_page() {
		// get HTTP referer
		$referer = wp_get_referer();

		if ( $referer === false )
			// go to home page
			wp_safe_redirect( get_home_url() );
		else {
			// go to referer’s page
			wp_safe_redirect( $referer );
			// store admin messages for next page
			set_transient( WPVGW . '_post_table_admin_messages', $this->adminMessages, 30 );
		}

		exit;
	}

	/**
	 * Iterates specified posts to execute a specified action on each post.
	 *
	 * @param array|null $post_ids
	 * @param $check_user_allowed
	 * @param callable $do_action A function that will be called for each post: function (WPVGW_PostTableView $thisObject, WP_Post $post, int $postUserId ) : bool.
	 * If $do_action returns false the iteration will be aborted, otherwise the iteration is continued.
	 *
	 * @return int The number of processed posts. Iteration can be aborted, so this number is not always the number of the specified posts.
	 */
	private function iterate_posts_for_actions( array $post_ids, $check_user_allowed, callable $do_action ) {
		// return if no post ids are given
		if ( $post_ids === null )
			return 0;


		$processedPostCount = 0;
		$userNotAllowedCount = 0;

		// iterate post IDs
		foreach ( $post_ids as $postId ) {
			$processedPostCount++;

			// convert post ID to int
			$postId = intval( $postId );

			// get post from post ID
			$post = get_post( $postId );

			// continue if post was not found
			if ( $post === null )
				continue;

			// check if current user has the permission to edit this post
			$postTypeObject = get_post_type_object( $post->post_type );
			if ( !current_user_can( $postTypeObject->cap->edit_post, $post->ID ) )
				WPVGW_Helper::die_cheating();


			// get post author
			$postUserId = (int)$post->post_author;

			// add continue if current post author is not allowed (if we should check for allowed users)
			if ( $check_user_allowed && !$this->markersManager->is_user_allowed( $postUserId ) ) {
				$userNotAllowedCount++;
				continue;
			}


			if ( !$do_action( $this, $post, $postUserId ) )
				break;
		}


		// add admin messages

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


	/**
	 *  Automatically adds markers to posts specified by IDs.
	 *
	 * @throws Exception Thrown if a Regex error occurred.
	 *
	 * @param array $post_ids An array of post IDs. The IDs will be converted to integers.
	 */
	private function add_marker_to_post( array $post_ids ) {
		// stats
		$noFreeMarker = false;
		$markerAddedCount = 0;
		$markerAlreadyExistsCount = 0;
		$markerNotAddedCount = 0;
		$postCharacterCountUnknownCount = 0;
		$postCharacterCountNotSufficientCount = 0;


		$processedPostCount = $this->iterate_posts_for_actions( $post_ids, true,
			function ( $thisObject, $post, $postUserId ) use ( &$noFreeMarker, &$markerAddedCount, &$markerAlreadyExistsCount, &$markerNotAddedCount, &$postCharacterCountUnknownCount, &$postCharacterCountNotSufficientCount ) {
				/** @var WPVGW_PostTableView $thisObject */
				/** @var WP_Post $post */
				/** @var int $postUserId */

				$postId = $post->ID;

				// get post’s extras.
				$postExtras = $thisObject->postsExtras->get_post_extras_from_db( $postId );
				// get post’s character count
				$postCharacterCount = ( $postExtras === false ? null : $postExtras['character_count'] );

				if ( $postCharacterCount === null )
					$postCharacterCountUnknownCount++;
				else if ( !$thisObject->markersManager->is_character_count_sufficient( $postCharacterCount, $thisObject->options->get_vg_wort_minimum_character_count() ) )
					$postCharacterCountNotSufficientCount++;


				// get a free marker for the post author
				$marker = $thisObject->markersManager->get_free_marker_from_db( $postUserId );

				if ( $marker === false )
					// get a free marker for arbitrary author
					$marker = $thisObject->markersManager->get_free_marker_from_db();

				if ( $marker === false ) {
					// no free marker found
					$noFreeMarker = true;

					return false;
				}
				else {
					// add marker to post
					$markerUpdateResult =
						$thisObject->markersManager->update_marker_in_db(
							$marker['public_marker'], // key
							'public_marker', // column
							array( // marker
								'post_id' => $post->ID,
							),
							$postUserId, // check user
							array( // conditions (just to be safe that the old post id is null)
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


		// create admin messages

		if ( $noFreeMarker ) {
			$notProcessedPostCount = count( $post_ids ) - $processedPostCount;

			$this->add_admin_message(
				__( 'Es sind nicht mehr genügend Zählmarken für einen oder mehrere Beitrags-Autor vorhanden. Fügen Sie bitte zunächst neue Zählmarken für die betreffenden Beitrags-Autoren hinzu und wiederholen Sie den Vorgang.', WPVGW_TEXT_DOMAIN ) .
				' ' .
				_n( 'Einem Beitrag konnte daher keine Zählmarke zugeordnet werden.',
					sprintf( '%s Beiträgen konnten daher keine Zählmarken zugeordnet werden.', number_format_i18n( $notProcessedPostCount ) ),
					$notProcessedPostCount,
					WPVGW_TEXT_DOMAIN
				)
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

	/**
	 * Handles the add marker to post action from the WordPress’ post table (GUI).
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 */
	public function do_add_marker_action() {
		// security: verify wordpress’ number once
		check_admin_referer( WPVGW . '_add_marker' );

		// get post ID for the action
		$postId = ( isset( $_REQUEST['post'] ) ) ? intval( $_REQUEST['post'] ) : null;

		// if post ID found, add a marker to post automatically
		$this->add_marker_to_post( array( $postId ) );

		// redirect to last page
		$this->redirect_to_last_page();
	}

	/**
	 * Get the post IDs for the current bulk action.
	 *
	 * @return string[]|null An array of string post IDs if success, otherwise null.
	 */
	private function get_bulk_action_post_ids() {
		// security: verify wordpress’ number once
		check_admin_referer( 'bulk-posts' );

		// get post IDs for the bulk action
		$postIds = ( isset( $_REQUEST['post'] ) ) ? $_REQUEST['post'] : null;

		if ( $postIds === null || !is_array( $postIds ) )
			return null;

		return $postIds;
	}

	/**
	 * Handles the add marker to post bulk action from the WordPress’ post table (GUI).
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 */
	public function do_add_markers_action() {
		// get post IDs chosen for bulk action
		$postIds = $this->get_bulk_action_post_ids();

		// if post IDs found, add markers to posts automatically
		$this->add_marker_to_post( $postIds );

		// redirect to last page
		$this->redirect_to_last_page();
	}

	/**
	 * Handles the remove marker from post action from the WordPress’ post table (GUI).
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 */
	public function do_remove_markers_action() {
		// allow admin users only
		if ( !current_user_can( 'manage_options' ) )
			WPVGW_Helper::die_cheating();

		// get post IDs chosen for bulk action
		$postIds = $this->get_bulk_action_post_ids();

		$removedPostFromMarkerCount = 0;

		// don’t check for allowed user because we want to allow removing post from markers for any author
		$processedPostCount = $this->iterate_posts_for_actions( $postIds, false,
			function ( $thisObject, $post, $postUserId ) use ( &$removedPostFromMarkerCount ) {
				/** @var WPVGW_PostTableView $thisObject */
				/** @var WP_Post $post */
				/** @var int $postUserId */

				// remove marker from post
				if ( $thisObject->markersManager->remove_post_from_marker_in_db( $post->ID ) )
					$removedPostFromMarkerCount++;

				return true;
			}
		);


		// add admin messages

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


		// redirect to last page
		$this->redirect_to_last_page();
	}


	/**
	 * Renders WordPress admin notices.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 */
	public function on_admin_notices() {
		// get stored admin messages
		$adminMessages = get_transient( WPVGW . '_post_table_admin_messages' );

		if ( $adminMessages === false )
			return;

		// delete stored admin messages
		delete_transient( WPVGW . '_post_table_admin_messages' );

		// render admin messages
		WPVGW_Helper::render_admin_messages( $adminMessages );
	}

}
