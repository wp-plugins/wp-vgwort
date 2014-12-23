<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */



class WPVGW_MarkersAdminView extends WPVGW_AdminViewBase {

	
	private $markerTable = null;


	
	public static function get_slug_static() {
		return 'markers';
	}

	
	public static function get_long_name_static() {
		return __( 'Zählmarken und Export', WPVGW_TEXT_DOMAIN );
	}

	
	public static function get_short_name_static() {
		return __( 'Zählmarken', WPVGW_TEXT_DOMAIN );
	}


	
	public function __construct( WPVGW_MarkersManager $markers_manager, WPVGW_PostsExtras $posts_extras, WPVGW_Options $options ) {
		parent::__construct( self::get_slug_static(), self::get_long_name_static(), self::get_short_name_static(), $markers_manager, $posts_extras, $options );

		
		add_action( 'wp_ajax_' . WPVGW . '_get_post_content', array( $this, 'ajax_get_post_content' ) );
	}

	
	public function init() {
		
		parent::init_base(
		
			array(
				array(
					'file'         => 'views/admin/markers-admin-view.js',
					'slug'         => 'admin-view-markers',
					'dependencies' => array( 'jquery' ),
				)
			)
		);


		
		$adminMessages = get_transient( WPVGW . '_markers_admin_view_admin_messages' );

		if ( $adminMessages !== false ) {
			
			$this->adminMessages = $adminMessages;
			
			delete_transient( WPVGW . '_markers_admin_view_admin_messages' );
		}

		
		

		
		
		$this->markerTable = new WPVGW_MarkersListTable( $this->markersManager, $this->postsExtras, $this->options );
	}


	
	public function render() {
		
		parent::begin_render_base();

		
		$this->markerTable->prepare_items();

		
		$formFields = parent::get_wp_number_once_field();

		
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

		
		$this->markerTable->views();

		?>
		<form id="wpvgw-markers" method="get" action="">
			<input type="hidden" name="page" value="<?php echo( WPVGW . '-' . self::get_slug_static() ) ?>"/>
			<?php
			
			echo( $formFields );

			
			$this->markerTable->search_box( __( 'Suchen', WPVGW_TEXT_DOMAIN ), 'wpvgw-markers-search' );
			
			$this->markerTable->display();

			
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

		
		parent::end_render_base();
	}


	
	private function do_markers_action_csv() {
		
		$outputHeadlines = isset( $_POST['wpvgw_export_csv_output_headlines'] );

		
		$this->options->set_export_csv_output_headlines( $outputHeadlines );


		
		$markers = $this->markerTable->get_all_items( true );


		
		WPVGW_Helper::http_header_csv( "zaehlmarken.csv" );

		$outputStream = fopen( 'php://output', 'w' );

		
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

		
		foreach ( $markers as $marker ) {

			$postContent = null;
			
			if ( $marker['post_content'] !== null ) {
				
				$postContent = $marker['post_content'];

				
				$postContent = preg_replace( array(
						WPVGW_Helper::$captionShortcodeRegex, 
					),
					'',
					$postContent
				);

				
				$postContent = apply_filters( 'the_content', $postContent );

				
				$postContent = strip_tags( $postContent );

				
				$postContent = html_entity_decode( $postContent );

				
				$postContent = preg_replace(
					WPVGW_Helper::$shortcodeRegex, 
					'',
					$postContent
				);

				
				$postContent = trim( $postContent );
			}


			$postTitle = null;
			
			if ( $marker['post_title'] !== null ) {
				
				$postTitle = apply_filters( 'the_title', $marker['post_title'] );

				
				$postTitle = html_entity_decode( $postTitle );
			}


			$permanentLink = null;
			
			if ( $marker['post_id'] !== null ) {
				$pLink = get_permalink( $marker['post_id'] );

				if ( $permanentLink !== false )
					$permanentLink = $pLink;
			}


			
			$postType = null;
			if ( $marker['post_type'] !== null ) {
				$postTypeObject = get_post_type_object( $marker['post_type'] );

				
				if ( $postTypeObject !== null )
					$postType = $postTypeObject->labels->singular_name;
			}


			
			fputcsv( $outputStream,
				array(
					$marker['private_marker'],
					$postTitle,
					$postTitle . "\n" . $postContent, 
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

	
	public function do_action() {
		
		if ( !parent::do_action_base() )
			
			return;


		
		if ( isset( $_POST['wpvgw_export_csv'] ) ) {
			$this->do_markers_action_csv();

			return;
		}


		
		$action = isset( $_GET['action'] ) ? $_GET['action'] : '-1';
		if ( $action == '-1' )
			$action = isset( $_GET['action2'] ) ? $_GET['action2'] : '-1';

		
		$isBulkEdit = isset( $_REQUEST['wpvgw_bulk_edit'] );

		
		if ( ( $action == '-1' && !$isBulkEdit ) || !isset( $_GET['wpvgw_marker'] ) )
			return;


		$markerIds = $_GET['wpvgw_marker'];

		
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


		

		$validationFailed = false;
		$updateMarker = array();

		
		if ( $setMarkerDisabled ) {
			if ( !$this->markersManager->is_marker_disabled_validator( $markerDisabled ) )
				throw new Exception( 'Is maker disabled should always have a valid value.' );

			$updateMarker['is_marker_disabled'] = $markerDisabled;
		}

		
		if ( $setServer ) {
			if ( $server === null || $server === '' )
				$server = $this->options->get_default_server();

			if ( !$this->markersManager->server_validator( $server ) ) {
				$validationFailed = true;
				$this->add_admin_message( __( 'Das Format für den eingegeben Server ist ungültig.', WPVGW_TEXT_DOMAIN ) );
			}

			$updateMarker['server'] = $server;
		}

		
		if ( $setUserId ) {
			if ( $userId == 0 )
				
				$updateMarker['user_id'] = null;
			elseif ( get_user_by( 'id', $userId ) === false ) {
				$validationFailed = true;
				$this->add_admin_message( __( 'Der ausgewählte Benutzer ist ungültig.', WPVGW_TEXT_DOMAIN ) );
			}

			$updateMarker['user_id'] = $userId;
		}


		
		if ( $validationFailed ) {
			if ( $validationFailed )
				$this->add_admin_message( __( 'Die ausgewählten Zählmarken wurde nicht bearbeitet, da Fehler aufgetreten sind.', WPVGW_TEXT_DOMAIN ) );
		}
		
		else {
			
			$markerUpdated = false;
			$numberOfMarkers = 0;
			$numberOfRemovedPostsFromMarkers = 0;
			$numberOfDeletedMarkers = 0;
			$numberOfRecalculatedPostCharacterCount = 0;
			$numberOfUpdatedMarkers = 0;
			$numberOfUpToDateMarkers = 0;

			
			foreach ( $markerIds as $markerId ) {
				$numberOfMarkers++;

				
				$markerId = intval( $markerId );


				
				if ( $deleteMarker ) {
					if ( $this->markersManager->delete_marker_in_db( $markerId ) )
						$numberOfDeletedMarkers++;

					
					continue;
				}

				
				if ( $recalculatePostCharacterCount ) {
					
					$marker = $this->markersManager->get_marker_from_db( $markerId, 'id' );

					
					if ( $marker['post_id'] !== null ) {
						
						$post = get_post( $marker['post_id'] );

						
						if ( $post !== false ) {
							
							$this->postsExtras->recalculate_post_character_count_in_db( $post );

							$numberOfRecalculatedPostCharacterCount++;
						}
					}
				}

				
				if ( $removePostFromMarker ) {
					if ( $this->markersManager->remove_post_from_marker_in_db( $markerId, 'marker' ) )
						$numberOfRemovedPostsFromMarkers++;
				}

				
				if ( count( $updateMarker ) > 0 ) {
					$markerUpdated = true;

					$updateResult = $this->markersManager->update_marker_in_db( $markerId, 'id', $updateMarker );

					if ( $updateResult == WPVGW_UpdateMarkerResults::Updated )
						$numberOfUpdatedMarkers++;
					elseif ( $updateResult == WPVGW_UpdateMarkerResults::UpdateNotNecessary )
						$numberOfUpToDateMarkers++;
				}

			}

			

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


		
		$referer = wp_get_referer();

		if ( $referer === false )
			
			wp_safe_redirect( get_home_url() );
		else {
			
			wp_safe_redirect( $referer );
			
			set_transient( WPVGW . '_markers_admin_view_admin_messages', $this->adminMessages, 30 );
		}

		exit;
	}

} 