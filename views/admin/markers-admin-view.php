<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: 2014 Ronny Harbich
 * License: GPLv2 or later
 */


/**
 * Represents the markers table view.
 */
class WPVGW_MarkersAdminView extends WPVGW_AdminViewBase {

	/**
	 * @var WPVGW_MarkersListTable|null The markers table.
	 */
	private $markerTable = null;


	/**
	 * See {@link WPVGW_AdminViewBase::get_slug()}.
	 */
	public static function get_slug_static() {
		return 'markers';
	}

	/**
	 * See {@link WPVGW_AdminViewBase::get_long_name()}.
	 */
	public static function get_long_name_static() {
		return __( 'Zählmarken und Export', WPVGW_TEXT_DOMAIN );
	}

	/**
	 * See {@link WPVGW_AdminViewBase::get_short_name()}.
	 */
	public static function get_short_name_static() {
		return __( 'Zählmarken', WPVGW_TEXT_DOMAIN );
	}


	/**
	 * Creates a new instance of {@link WPVGW_MarkersAdminView}.
	 *
	 * @param WPVGW_MarkersManager $markers_manager A markers manager.
	 * @param WPVGW_PostsExtras $posts_extras The posts extras.
	 * @param WPVGW_Options $options The options.
	 */
	public function __construct( WPVGW_MarkersManager $markers_manager, WPVGW_PostsExtras $posts_extras, WPVGW_Options $options ) {
		parent::__construct( self::get_slug_static(), self::get_long_name_static(), self::get_short_name_static(), $markers_manager, $posts_extras, $options );

		// hook ajax get post content action
		add_action( 'wp_ajax_' . WPVGW . '_get_post_content', array( $this, 'ajax_get_post_content' ) );
	}

	/**
	 * Initializes the view. This function must be called before using the view.
	 */
	public function init() {
		// has to be called
		parent::init_base(
		// javascript data
			array(
				array(
					'file'         => 'views/admin/markers-admin-view.js',
					'slug'         => 'admin-view-markers',
					'dependencies' => array( 'jquery' ),
				)
			)
		);


		// get stored admin message
		$adminMessages = get_transient( WPVGW . '_markers_admin_view_admin_messages' );

		if ( $adminMessages !== false ) {
			// set admin messages (will be shown later)
			$this->adminMessages = $adminMessages;
			// delete stored admin messages
			delete_transient( WPVGW . '_markers_admin_view_admin_messages' );
		}

		// set screen options (tab); not used currently because storing the value has to be in an early hook
		/*$screenOptions = array(
			'label'   => __( 'Seiten', WPVGW_TEXT_DOMAIN ),
			'default' => $this->options->get_number_of_markers_per_page(),
		);
		add_screen_option( 'per_page', $screenOptions );*/

		// create new markers table
		// we need to create it at this position to add table column checkboxes to the screen options automatically
		$this->markerTable = new WPVGW_MarkersListTable( $this->markersManager, $this->postsExtras, $this->options );
	}


	/**
	 * Renders the view.
	 *
	 * @throws Exception Thrown if view is not initialized.
	 */
	public function render() {
		// has to be called
		parent::begin_render_base();

		// fill table
		$this->markerTable->prepare_items();

		// some necessary fields for the forms
		$formFields = parent::get_wp_number_once_field();

		// post content overlay window; post title HTML is injected by javascript
		?>
		<div id="wpvgw-markers-view-copy-window-background">
			<div id="wpvgw-markers-view-copy-window">
				<a id="wpvgw-markers-view-copy-window-close" href="#" title="<?php _e( 'Das Fenster schließen', WPVGW_TEXT_DOMAIN ) ?>"><?php _e( 'Schließen', WPVGW_TEXT_DOMAIN ) ?></a>
				<div id="wpvgw-markers-view-copy-window-content">
					<p id="wpvgw-markers-view-post-content-loading"><?php _e( 'Bitte warten …', WPVGW_TEXT_DOMAIN ) ?></p>
				</div>
			</div>
		</div>
		<?php

		// render view links
		$this->markerTable->views();

		?>
		<form id="wpvgw-markers" method="get" action="">
			<input type="hidden" name="page" value="<?php echo( WPVGW . '-' . self::get_slug_static() ) ?>"/>
			<?php
			// render some input fields
			echo( $formFields );

			// render search box
			$this->markerTable->search_box( __( 'Suchen', WPVGW_TEXT_DOMAIN ), 'wpvgw-markers-search' );
			// render markers table
			$this->markerTable->display();

			// bulk edit
			?>
			<div id="wpvgw-markers-bulk-edit">
				<h3><?php _e( 'Alle ausgewählten Zählmarken bearbeiten', WPVGW_TEXT_DOMAIN ) ?></h3>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><?php _e( 'Aktionen', WPVGW_TEXT_DOMAIN ) ?></th>
							<td>
								<p>
									<input type="checkbox" name="wpvgw_e_remove_post_from_marker" id="wpvgw_e_remove_post_from_marker" value="1" class="checkbox"/>
									<label for="wpvgw_e_remove_post_from_marker"><?php _e( 'Zählmarken-Zuordnung aufheben', WPVGW_TEXT_DOMAIN ); ?></label>
									<br/>
									<span class="description"><?php _e( 'In der Regel sollte die Zuordnung nicht aufgehoben, sondern die Zählmarke inaktiv gesetzt werden.', WPVGW_TEXT_DOMAIN ) ?></span>
								</p>
								<p>
									<input type="checkbox" name="wpvgw_e_delete_marker" id="wpvgw_e_delete_marker" value="1" class="checkbox"/>
									<label for="wpvgw_e_delete_marker"><?php _e( 'Löschen (nicht empfohlen)', WPVGW_TEXT_DOMAIN ); ?></label>
									<br/>
									<span class="description"><?php _e( 'In der Regel sollten nur falsch importierte Zählmarken gelöscht werden.', WPVGW_TEXT_DOMAIN ) ?></span>
								</p>
								<p>
									<input type="checkbox" name="wpvgw_e_recalculate_post_character_count" id="wpvgw_e_recalculate_post_character_count" value="1" class="checkbox"/>
									<label for="wpvgw_e_recalculate_post_character_count"><?php _e( 'Zeichenanzahl neuberechnen', WPVGW_TEXT_DOMAIN ); ?></label>
									<br/>
									<span class="description"><?php _e( 'Zeichenanzahlen der Beiträge neuberechnen. Sinnvoll, wenn die Zeichenanzahlen falsch oder nicht vorhanden sind.', WPVGW_TEXT_DOMAIN ) ?></span>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Aktivierung', WPVGW_TEXT_DOMAIN ); ?></th>
							<td>
								<p>
									<input type="checkbox" name="wpvgw_e_marker_disabled" id="wpvgw_e_marker_disabled" value="1" class="checkbox"/>
									<label for="wpvgw_e_marker_disabled"><?php _e( 'Inaktiv', WPVGW_TEXT_DOMAIN ); ?></label>
									<input type="checkbox" name="wpvgw_e_marker_disabled_set" id="wpvgw_e_marker_disabled_set" value="1" class="checkbox"/>
									<label for="wpvgw_e_marker_disabled_set"><?php _e( 'Wert ändern', WPVGW_TEXT_DOMAIN ); ?></label>
									<br/>
									<span class="description"><?php _e( 'Inaktive Zählmarken werden für den zugeordneten Beitrag nicht mehr ausgegeben (keine Zählung mehr bei VG WORT).', WPVGW_TEXT_DOMAIN ) ?></span>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="wpvgw_e_server"><?php _e( 'Server', WPVGW_TEXT_DOMAIN ); ?></label></th>
							<td>
								<p>
									<input type="text" name="wpvgw_e_server" id="wpvgw_e_server" class="regular-text"/>
									<input type="checkbox" name="wpvgw_e_server_set" id="wpvgw_e_server_set" value="1" class="checkbox"/>
									<label for="wpvgw_e_server_set"><?php _e( 'Wert ändern', WPVGW_TEXT_DOMAIN ); ?></label>
									<br/>
									<span class="description"><?php echo( sprintf( __( 'Wenn der Server nicht angegeben wird, wird der Standard-Server (%s) verwendet.', WPVGW_TEXT_DOMAIN ), $this->options->get_default_server() ) ); ?></span>
								</p>
							</td>
						</tr>
					</tbody>
				</table>
				<p class="submit">
					<input type="submit" name="wpvgw_bulk_edit" value="<?php _e( 'Ausgewählte Zählmarken bearbeiten', WPVGW_TEXT_DOMAIN ); ?>" class="button-primary"/>
					<a class="button-secondary cancel wpvgw-bulk-edit-cancel" href="#"><?php _e( 'Abbrechen', WPVGW_TEXT_DOMAIN ); ?></a>
					<br/>
					<span class="description"><?php _e( 'Die Bearbeitung kann nicht Rückgängig gemacht werden!', WPVGW_TEXT_DOMAIN ) ?></span>
				</p>
			</div>
		</form>
		<form method="post" enctype="multipart/form-data">
			<?php
			// render some input fields
			echo( $formFields );

			?>
			<h3><?php _e( 'Zählmarken exportieren', WPVGW_TEXT_DOMAIN ) ?></h3>
			<p>
				<?php _e( 'Es werden <em>alle</em> Zählmarken entsprend der in der Tabelle ausgewählten Filter und Sortierung exportiert.', WPVGW_TEXT_DOMAIN ); ?>
			</p>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?php _e( 'Export als CSV-Datei', WPVGW_TEXT_DOMAIN ); ?></th>
						<td>
							<p>
								<?php _e( 'CSV-Dateien können mit Tabellenprogrammen wie LibreOffice Calc oder Microsoft Excel geöffnet werden.', WPVGW_TEXT_DOMAIN ); ?>
							</p>
							<p>
								<input type="checkbox" name="wpvgw_export_csv_output_headlines" id="wpvgw_export_csv_output_headlines" value="1" class="checkbox" <?php echo( WPVGW_Helper::get_html_checkbox_checked( $this->options->get_export_csv_output_headlines() ) ) ?>/>
								<label for="wpvgw_export_csv_output_headlines"><?php _e( 'Tabellenkopf ausgeben', WPVGW_TEXT_DOMAIN ); ?></label>
								<br/>
								<span class="description"><?php _e( 'Gibt an, ob der Tabellenkopf (Beschreibung der einzelnen Spalten) als erste Zeile ausgegeben werden soll.', WPVGW_TEXT_DOMAIN ) ?></span>
							</p>
							<p class="submit">
								<input type="submit" name="wpvgw_export_csv" value="<?php _e( 'Zählmarken als CSV-Datei exportieren', WPVGW_TEXT_DOMAIN ); ?>" class="button-primary" / >
							</p>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
		<?php

		// has to be called
		parent::end_render_base();
	}


	/**
	 * Echos a CSV file that contains all marker and post data. Filters and sorts made in the table are considered.
	 * This function terminates the script execution.
	 *
	 * @throws Exception Thrown a database error occurred.
	 */
	private function do_markers_action_csv() {
		// get options
		$outputHeadlines = isset( $_POST['wpvgw_export_csv_output_headlines'] );

		// set options
		$this->options->set_export_csv_output_headlines( $outputHeadlines );


		// get all all marker and post data; filters and sorts made in the table are considered
		$markers = $this->markerTable->get_all_items( true );


		// outputs a HTTP header for CSV files
		WPVGW_Helper::http_header_csv( "zaehlmarken.csv" );

		$outputStream = fopen( 'php://output', 'w' );

		// write headlines as CSV data
		if ( $outputHeadlines ) {
			fputcsv( $outputStream,
				array(
					__( 'Private Zählmarke', WPVGW_TEXT_DOMAIN ),
					__( 'Beitrags-Titel', WPVGW_TEXT_DOMAIN ),
					__( 'Beitrags-Text', WPVGW_TEXT_DOMAIN ),
					__( 'Link', WPVGW_TEXT_DOMAIN ),
					__( 'Beitrags-Datum', WPVGW_TEXT_DOMAIN ),
					__( 'Beitrags-Autor', WPVGW_TEXT_DOMAIN ),
					__( 'Beitrags-Typ', WPVGW_TEXT_DOMAIN ),
					__( 'Zeichenanzahl', WPVGW_TEXT_DOMAIN ),
					__( 'Öffentliche Zählmarke', WPVGW_TEXT_DOMAIN ),
					__( 'Server', WPVGW_TEXT_DOMAIN ),
					__( 'Zählmarke inaktiv', WPVGW_TEXT_DOMAIN ),
					__( 'Beitrag gelöscht', WPVGW_TEXT_DOMAIN ),
					__( 'Titel gelöschter Beitrag', WPVGW_TEXT_DOMAIN ),
				),
				$this->options->get_export_csv_delimiter(),
				$this->options->get_export_csv_enclosure()
			);
		}

		// iterate markers
		foreach ( $markers as $marker ) {

			$postContent = null;
			// get post content
			if ( $marker['post_content'] !== null ) {
				// get post content
				$postContent = $marker['post_content'];

				// remove special shortcodes from post content
				$postContent = preg_replace( array(
						WPVGW_Helper::$captionShortcodeRegex, // remove caption shortcodes and its content
					),
					'',
					$postContent
				);

				// do shortcodes and other filters on post content
				$postContent = apply_filters( 'the_content', $postContent );

				// remove all tag from post content
				$postContent = strip_tags( $postContent );

				// convert html entities (e. g. &amp; to &)
				$postContent = html_entity_decode( $postContent );

				// remove leftover shortcodes from post content
				$postContent = preg_replace(
					WPVGW_Helper::$shortcodeRegex, // remove shortcodes, but not content between shortcodes; it is escaping aware
					'',
					$postContent
				);

				// remove whitespace from beginning and ending
				$postContent = trim( $postContent );
			}


			$postTitle = null;
			// get post title
			if ( $marker['post_title'] !== null ) {
				// get post title
				$postTitle = apply_filters( 'the_title', $marker['post_title'] );

				// convert html entities (e. g. &amp; to &)
				$postTitle = html_entity_decode( $postTitle );
			}


			$permanentLink = null;
			// get permanent link of marker’s post
			if ( $marker['post_id'] !== null ) {
				$pLink = get_permalink( $marker['post_id'] );

				if ( $permanentLink !== false )
					$permanentLink = $pLink;
			}


			// get post type name
			$postType = null;
			if ( $marker['post_type'] !== null ) {
				$postTypeObject = get_post_type_object( $marker['post_type'] );

				// get post type name
				if ( $postTypeObject !== null )
					$postType = $postTypeObject->labels->singular_name;
			}


			// write marker as CSV data
			fputcsv( $outputStream,
				array(
					$marker['private_marker'],
					$postTitle,
					$postTitle . "\n" . $postContent, // title and content
					$permanentLink,
					$marker['post_date'],
					$marker['up_display_name'],
					$postType,
					$marker['e_character_count'],
					$marker['public_marker'],
					$marker['server'],
					$marker['is_marker_disabled'],
					$marker['is_post_deleted'],
					$marker['deleted_post_title'],
				),
				$this->options->get_export_csv_delimiter(),
				$this->options->get_export_csv_enclosure()
			);
		}

		fclose( $outputStream );

		exit;
	}

	/**
	 * Handles the actions for the view.
	 *
	 * @throws Exception Thrown if view is not initialized or if a database error occurred.
	 */
	public function do_action() {
		// has to be called
		if ( !parent::do_action_base() )
			// do no actions
			return;


		// CSV export?
		if ( isset( $_POST['wpvgw_export_csv'] ) ) {
			$this->do_markers_action_csv();

			return;
		}


		// get markers action
		$action = isset( $_GET['action'] ) ? $_GET['action'] : '-1';
		if ( $action == '-1' )
			$action = isset( $_GET['action2'] ) ? $_GET['action2'] : '-1';

		// is bulk edit?
		$isBulkEdit = isset( $_REQUEST['wpvgw_bulk_edit'] );

		// return if no action was chosen
		if ( ( $action == '-1' && !$isBulkEdit ) || !isset( $_GET['wpvgw_marker'] ) )
			return;


		$markerIds = $_GET['wpvgw_marker'];

		// if marker IDs is not an array, a single marker action was done
		if ( !is_array( $markerIds ) ) {
			$markerIds = array( intval( $markerIds ) );
		}


		$setMarkerDisabled = false;
		$markerDisabled = null;

		$removePostFromMarker = false;

		$deleteMarker = false;

		$recalculatePostCharacterCount = false;

		$setServer = false;
		$server = null;

		$setUserId = false;
		$userId = null;


		// bulk actions
		if ( $isBulkEdit ) {

			if ( isset( $_REQUEST['wpvgw_e_marker_disabled_set'] ) ) {
				$setMarkerDisabled = true;
				$markerDisabled = isset( $_REQUEST['wpvgw_e_marker_disabled'] );
			}

			if ( isset( $_REQUEST['wpvgw_e_remove_post_from_marker'] ) )
				$removePostFromMarker = true;

			if ( isset( $_REQUEST['wpvgw_e_delete_marker'] ) )
				$deleteMarker = true;

			if ( isset( $_REQUEST['wpvgw_e_recalculate_post_character_count'] ) )
				$recalculatePostCharacterCount = true;

			if ( isset( $_REQUEST['wpvgw_e_server_set'] ) ) {
				$setServer = true;
				$server = isset( $_REQUEST['wpvgw_e_server'] ) ? stripslashes( $_REQUEST['wpvgw_e_server'] ) : null;
			}
		}


		switch ( $action ) {
			case WPVGW . '_enable_marker':
				$setMarkerDisabled = true;
				$markerDisabled = false;
				break;
			case WPVGW . '_disable_marker':
				$setMarkerDisabled = true;
				$markerDisabled = true;
				break;
			case WPVGW . '_remove_post_from_marker':
				$removePostFromMarker = true;
				break;
			case WPVGW . '_delete_marker':
				$deleteMarker = true;
				break;
			case WPVGW . '_recalculate_post_character_count';
				$recalculatePostCharacterCount = true;
				break;
			default:
				break;
		}


		// validation

		$validationFailed = false;
		$updateMarker = array();

		// disable or enable marker
		if ( $setMarkerDisabled ) {
			if ( !$this->markersManager->is_marker_disabled_validator( $markerDisabled ) )
				throw new Exception( 'Is maker disabled should always have a valid value.' );

			$updateMarker['is_marker_disabled'] = $markerDisabled;
		}

		// validate server
		if ( $setServer ) {
			if ( $server === null || $server === '' )
				$server = $this->options->get_default_server();

			if ( !$this->markersManager->server_validator( $server ) ) {
				$validationFailed = true;
				$this->add_admin_message( __( 'Das Format für den eingegeben Server ist ungültig.', WPVGW_TEXT_DOMAIN ) );
			}

			$updateMarker['server'] = $server;
		}

		// validate user id
		if ( $setUserId ) {
			if ( $userId == 0 )
				// all user allowed
				$updateMarker['user_id'] = null;
			elseif ( get_user_by( 'id', $userId ) === false ) {
				$validationFailed = true;
				$this->add_admin_message( __( 'Der ausgewählte Benutzer ist ungültig.', WPVGW_TEXT_DOMAIN ) );
			}

			$updateMarker['user_id'] = $userId;
		}


		// validation failed
		if ( $validationFailed ) {
			if ( $validationFailed )
				$this->add_admin_message( __( 'Die ausgewählten Zählmarken wurde nicht bearbeitet, da Fehler aufgetreten sind.', WPVGW_TEXT_DOMAIN ) );
		}
		// validation successful
		else {
			// stats
			$markerUpdated = false;
			$numberOfMarkers = 0;
			$numberOfRemovedPostsFromMarkers = 0;
			$numberOfDeletedMarkers = 0;
			$numberOfRecalculatedPostCharacterCount = 0;
			$numberOfUpdatedMarkers = 0;
			$numberOfUpToDateMarkers = 0;

			// iterate marker IDs
			foreach ( $markerIds as $markerId ) {
				$numberOfMarkers++;

				// cast to marker ID to integer
				$markerId = intval( $markerId );


				// delete marker
				if ( $deleteMarker ) {
					if ( $this->markersManager->delete_marker_in_db( $markerId ) )
						$numberOfDeletedMarkers++;

					// continue because deleted markers cannot be updated anymore
					continue;
				}

				// recalculate post character count
				if ( $recalculatePostCharacterCount ) {
					// get current marker
					$marker = $this->markersManager->get_marker_from_db( $markerId, 'id' );

					// marker has post?
					if ( $marker['post_id'] !== null ) {
						// get post
						$post = get_post( $marker['post_id'] );

						// post found?
						if ( $post !== false ) {
							// recalculate post character count
							$this->postsExtras->recalculate_post_character_count_in_db( $post );

							$numberOfRecalculatedPostCharacterCount++;
						}
					}
				}

				// remove marker from post
				if ( $removePostFromMarker ) {
					if ( $this->markersManager->remove_post_from_marker_in_db( $markerId, 'marker' ) )
						$numberOfRemovedPostsFromMarkers++;
				}

				// do update marker
				if ( count( $updateMarker ) > 0 ) {
					$markerUpdated = true;

					$updateResult = $this->markersManager->update_marker_in_db( $markerId, 'id', $updateMarker );

					if ( $updateResult == WPVGW_UpdateMarkerResults::Updated )
						$numberOfUpdatedMarkers++;
					elseif ( $updateResult == WPVGW_UpdateMarkerResults::UpdateNotNecessary )
						$numberOfUpToDateMarkers++;
				}

			}

			// add admin messages

			$this->add_admin_message(
				_n( 'Es wurde eine Zählmarke zur Bearbeitung ausgewählt.',
					sprintf( '%s Zählmarken wurden zur Bearbeitung ausgewählt.', number_format_i18n( $numberOfMarkers ) ),
					$numberOfMarkers,
					WPVGW_TEXT_DOMAIN
				),
				WPVGW_ErrorType::Update
			);

			if ( $removePostFromMarker ) {
				if ( $numberOfRemovedPostsFromMarkers > 0 )
					$this->add_admin_message(
						_n( 'Eine Zählmarken-Zuordnung wurde aufgehoben.',
							sprintf( '%s Zählmarken-Zuordnungen wurden aufgehoben.', number_format_i18n( $numberOfRemovedPostsFromMarkers ) ),
							$numberOfRemovedPostsFromMarkers,
							WPVGW_TEXT_DOMAIN
						),
						WPVGW_ErrorType::Update
					);

				if ( $numberOfRemovedPostsFromMarkers < $numberOfMarkers ) {
					$numberOfNotRemovedPostsFromMarkers = $numberOfMarkers - $numberOfRemovedPostsFromMarkers;

					$this->add_admin_message(
						_n( 'Eine Zählmarken-Zuordnung konnte nicht aufgehoben werden.',
							sprintf( '%s Zählmarken-Zuordnungen konnten nicht aufgehoben werden.', number_format_i18n( $numberOfNotRemovedPostsFromMarkers ) ),
							$numberOfNotRemovedPostsFromMarkers,
							WPVGW_TEXT_DOMAIN
						)
					);
				}
			}


			if ( $deleteMarker ) {
				if ( $numberOfDeletedMarkers > 0 )
					$this->add_admin_message(
						_n( 'Eine Zählmarke wurde gelöscht.',
							sprintf( '%s Zählmarken wurden gelöscht.', number_format_i18n( $numberOfDeletedMarkers ) ),
							$numberOfDeletedMarkers,
							WPVGW_TEXT_DOMAIN
						),
						WPVGW_ErrorType::Update
					);

				if ( $numberOfDeletedMarkers < $numberOfMarkers ) {
					$numberOfNotDeletedMarkers = $numberOfMarkers - $numberOfDeletedMarkers;

					$this->add_admin_message(
						_n( 'Eine Zählmarke konnte nicht gelöscht werden.',
							sprintf( '%s Zählmarken konnten nicht gelöscht werden.', number_format_i18n( $numberOfNotDeletedMarkers ) ),
							$numberOfNotDeletedMarkers,
							WPVGW_TEXT_DOMAIN
						)
					);
				}
			}


			if ( $recalculatePostCharacterCount ) {
				if ( $numberOfRecalculatedPostCharacterCount > 0 )
					$this->add_admin_message(
						_n( 'Die Zeichenanzahl eines Beitrags wurde neuberechnet.',
							sprintf( 'Die Zeichenanzahlen von %s Beiträgen wurden neuberechnet.', number_format_i18n( $numberOfRecalculatedPostCharacterCount ) ),
							$numberOfRecalculatedPostCharacterCount,
							WPVGW_TEXT_DOMAIN
						),
						WPVGW_ErrorType::Update
					);
			}


			if ( $markerUpdated ) {
				if ( $numberOfUpdatedMarkers > 0 )
					$this->add_admin_message(
						_n( 'Eine Zählmarke wurde aktualisiert.',
							sprintf( '%s Zählmarken wurden aktualisiert.', number_format_i18n( $numberOfUpdatedMarkers ) ),
							$numberOfUpdatedMarkers,
							WPVGW_TEXT_DOMAIN
						),
						WPVGW_ErrorType::Update
					);

				if ( $numberOfUpToDateMarkers > 0 )
					$this->add_admin_message(
						_n( 'Eine Zählmarke wurde nicht aktualisiert, da sie bereits die gewünschten Einstellungen hat.',
							sprintf( '%s Zählmarken wurden nicht aktualisiert, da sie bereits die gewünschten Einstellungen haben.', number_format_i18n( $numberOfUpToDateMarkers ) ),
							$numberOfUpToDateMarkers, WPVGW_TEXT_DOMAIN
						),
						WPVGW_ErrorType::Update
					);

				$numberOfUpdatedOrUpToDateMarkers = $numberOfUpdatedMarkers + $numberOfUpToDateMarkers;

				if ( $numberOfUpdatedOrUpToDateMarkers < $numberOfMarkers ) {
					$numberOfNotUpdatedOrUpToDateMarkers = $numberOfMarkers - $numberOfUpdatedOrUpToDateMarkers;

					$this->add_admin_message(
						_n( 'Eine Zählmarke konnte nicht aktualisiert werden.',
							sprintf( '%s Zählmarken konnten nicht aktualisiert werden.', number_format_i18n( $numberOfNotUpdatedOrUpToDateMarkers ) ),
							$numberOfNotUpdatedOrUpToDateMarkers,
							WPVGW_TEXT_DOMAIN
						)
					);
				}
			}
		}


		// get HTTP referer
		$referer = wp_get_referer();

		if ( $referer === false )
			// go to home page
			wp_safe_redirect( get_home_url() );
		else {
			// go to referer’s page
			wp_safe_redirect( $referer );
			// store admin messages for next page
			set_transient( WPVGW . '_markers_admin_view_admin_messages', $this->adminMessages, 30 );
		}

		exit;
	}

} 