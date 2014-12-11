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
class WPVGW_OperationsAdminView extends WPVGW_AdminViewBase {

	/**
	 * See {@link WPVGW_AdminViewBase::get_slug()}.
	 */
	public static function get_slug_static() {
		return 'operations';
	}

	/**
	 * See {@link WPVGW_AdminViewBase::get_long_name()}.
	 */
	public static function get_long_name_static() {
		return __( 'Komplexe Operationen und Einstellungen', WPVGW_TEXT_DOMAIN );
	}

	/**
	 * See {@link WPVGW_AdminViewBase::get_short_name()}.
	 */
	public static function get_short_name_static() {
		return __( 'Operationen', WPVGW_TEXT_DOMAIN );
	}


	/**
	 * Creates a new instance of {@link WPVGW_OperationsAdminView}.
	 *
	 * @param WPVGW_MarkersManager $markers_manager A markers manager.
	 * @param WPVGW_PostsExtras $posts_extras The posts extras.
	 * @param WPVGW_Options $options The options.
	 */
	public function __construct( WPVGW_MarkersManager $markers_manager, WPVGW_PostsExtras $posts_extras, WPVGW_Options $options ) {
		parent::__construct( self::get_slug_static(), self::get_long_name_static(), self::get_short_name_static(), $markers_manager, $posts_extras, $options );
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
					'file'         => 'views/admin/operations-admin-view.js',
					'slug'         => 'admin-view-operations',
					'dependencies' => array( 'jquery' )
				)
			)
		);
	}


	/**
	 * Renders the view.
	 *
	 * @throws Exception Thrown if view is not initialized.
	 */
	public function render() {
		// has to be called
		parent::begin_render_base();

		?>
		<p>
			<?php _e( 'An dieser Stelle können aufwendigere Operationen und Einstellungen vorgenommen werden. Die Bearbeitung einzelner Operationen und Einstellungen kann mehrere Sekunden in Anspruch nehmen. <strong>Gestartete Operationen sollten nicht abgebrochen werden!</strong>', WPVGW_TEXT_DOMAIN ); ?>
		</p>
		<form method="post">
			<?php echo( parent::get_wp_number_once_field() ) ?>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?php _e( 'Zugelassene Beitrags-Typen', WPVGW_TEXT_DOMAIN ); ?></th>
						<td>
							<p>
								<?php _e( 'Die ausgewählten Beitrags-Typen werden mit der Zählmarken-Funktion versehen:', WPVGW_TEXT_DOMAIN ) ?>
							</p>
							<?php

							// get WordPress post types
							$postTypes = $this->markersManager->get_possible_post_types();

							echo( '<ul class="wpvgw-check-list-box">' );

							if ( count( $postTypes ) < 0 ) {
								?>
								<li>
									<?php _e( 'Keine Beitrags-Typen vorhanden!', WPVGW_TEXT_DOMAIN ); ?>
								</li>
							<?php
							}

							// iterate post types
							foreach ( $postTypes as $type ) {
								$postTypeObject = get_post_type_object( $type );
								// check checkbox if post type is allowed
								$checked = WPVGW_Helper::get_html_checkbox_checked( $this->markersManager->is_post_type_allowed( $type ) );
								?>
								<li>
									<input type="checkbox" <?php echo( $checked ) ?> id="wpvgw_allowed_post_types_<?php echo( $type ) ?>" name="wpvgw_allowed_post_types[<?php echo( $type ) ?>]"/>
									<label for="wpvgw_allowed_post_types_<?php echo( $type ) ?>"><?php echo( esc_html( $postTypeObject->labels->name ) ) ?></label>
								</li>
							<?php
							}


							// get removed post types
							$removedPostTypes = $this->markersManager->get_removed_post_types();

							// iterate removed post types
							foreach ( $removedPostTypes as $type ) {
								?>
								<li class="wpvgw-invalid">
									<input type="checkbox" id="wpvgw_removed_post_types_<?php echo( $type ) ?>" name="wpvgw_removed_post_types[<?php echo( $type ) ?>]"/>
									<label for="wpvgw_removed_post_types_<?php echo( $type ) ?>"><?php echo( esc_html( $type ) ) ?></label>
								</li>
							<?php
							}

							echo( '</ul>' );
							?>
							<p>
								<?php _e( 'Beim Abwählen eines Beitrag-Typs werden die Zählmarken-Zuordnungen entsprechender Beiträge nicht gelöscht.', WPVGW_TEXT_DOMAIN ); ?>
							</p>
							<p>
								<?php _e( 'Die Zeichenanzahlen aller Beiträge der ausgewählten Beitrags-Typen werden automatisch neuberechnet.', WPVGW_TEXT_DOMAIN ); ?>
							</p>
							<p>
								<?php _e( 'Rotmarkierte Beitrags-Typen (entfernbar) sind nicht verfügbar, da Plugins/Themes, die diese definieren, deaktiviert oder deinstalliert wurden.', WPVGW_TEXT_DOMAIN ); ?>
							</p>
							<p class="submit">
								<input type="submit" name="wpvgw_operation_allowed_post_types" value="<?php _e( 'Beitrags-Typen zulassen', WPVGW_TEXT_DOMAIN ); ?>" class="button-primary" / >
								<input type="submit" name="wpvgw_operation_removed_post_types" value="<?php _e( 'Nicht verfügbare Beitrags-Typen entfernen', WPVGW_TEXT_DOMAIN ); ?>" class="button" / >
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Zeichenanzahl neuberechnen', WPVGW_TEXT_DOMAIN ); ?></th>
						<td>
							<p>
								<?php _e( 'Die Zeichenanzahlen aller Beiträge werden separat gespeichert.', WPVGW_TEXT_DOMAIN ); ?>
							</p>
							<p>
								<?php _e( 'Wenn die Zeichenanzahl eines Beitrags falsch oder nicht vorhanden ist, kann sie hier neuberechnet werden.', WPVGW_TEXT_DOMAIN ); ?>
							</p>
							<p class="submit">
								<input type="submit" name="wpvgw_operation_recalculate_character_count" value="<?php _e( 'Zeichenanzahl aller Beiträge neuberechnen', WPVGW_TEXT_DOMAIN ); ?>" class="button-primary" / >
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Alte Zählmarken importieren', WPVGW_TEXT_DOMAIN ); ?></th>
						<td>
							<p>
								<input type="checkbox" name="wpvgw_operation_import_old_plugin_markers" id="wpvgw_operation_import_old_plugin_markers" value="1" class="checkbox"/>
								<label for="wpvgw_operation_import_old_plugin_markers"><?php _e( 'Zählmarken aus altem VG-WORT-Plugin vor Version 3.0.0 importieren', WPVGW_TEXT_DOMAIN ); ?></label>
								<br/>
								<label for="wpvgw_operation_import_old_plugin_markers_meta_name"><?php _e( 'Meta-Name aus altem Plugin: ', WPVGW_TEXT_DOMAIN ); ?></label>
								<input type="text" name="wpvgw_operation_import_old_plugin_markers_meta_name" id="wpvgw_operation_import_old_plugin_markers_meta_name" class="regular-text" value="<?php echo( esc_attr( $this->options->get_meta_name() ) ) ?>"/>
								<br/>
								<span class="description"><?php echo( sprintf( __( 'Der Standardwert ist: %s', WPVGW_TEXT_DOMAIN ), esc_html( $this->options->default_meta_name() ) ) ); ?></span>
								<br/>
								<span class="description"><?php _e( 'Wenn dieses VG-WORT-Plugin bereits vor Version 3.0.0 verwendet wurde, können hier die zuvor verwendeten Zählmarken importiert werden. Es werden keine Daten gelöscht.', WPVGW_TEXT_DOMAIN ) ?></span>
							</p>
							<p>
								<input type="checkbox" name="wpvgw_operation_import_old_manual_markers" id="wpvgw_operation_import_old_manual_markers" value="1" class="checkbox"/>
								<label for="wpvgw_operation_import_old_manual_markers"><?php _e( 'Zählmarken (manuelle) aus Beiträgen erkennen und importieren', WPVGW_TEXT_DOMAIN ); ?></label>
								<input type="checkbox" name="wpvgw_operation_import_old_manual_markers_delete" id="wpvgw_operation_import_old_manual_markers_delete" value="1" class="checkbox"/>
								<label for="wpvgw_operation_import_old_manual_markers_delete"><?php _e( 'Gefundene Zählmarken aus Beiträgen (deren Inhalt) entfernen (empfohlen)', WPVGW_TEXT_DOMAIN ); ?></label>
								<br/>
								<label for="wpvgw_operation_import_old_manual_markers_regex"><?php _e( 'Regulärer Ausdruck (PHP-Stil) zur Zählmarkenerkennung: ', WPVGW_TEXT_DOMAIN ); ?></label>
								<input type="text" name="wpvgw_operation_import_old_manual_markers_regex" id="wpvgw_operation_import_old_manual_markers_regex" class="regular-text" value="<?php echo( esc_attr( $this->options->get_import_from_post_regex() ) ) ?>"/>
								<br/>
								<span class="description"><?php echo( sprintf( __( 'Der Standardwert ist: %s', WPVGW_TEXT_DOMAIN ), esc_html( $this->options->default_import_from_post_regex() ) ) ); ?></span>
								<br/>
								<span class="description"><?php echo( sprintf( __( 'Wenn bereits Zählmarken der Form %s manuell in Beiträgen (deren Inhalt) eingefügt wurden, können diese hier importiert und entfernt werden.', WPVGW_TEXT_DOMAIN ), esc_html( '<img src="http://vg02.met.vgwort.de/na/abc123" … >' ) ) ) ?></span>
								<br/>
								<span class="description"><?php _e( '<strong>Achtung:</strong> Aus Beiträgen (deren Inhalt) entfernte Zählmarken können nicht wiederhergestellt werden. Bitte sämtliche Beiträge sichern (Backup), bevor diese Operation durchgeführt wird.', WPVGW_TEXT_DOMAIN ) ?></span>
							</p>
							<p>
								<input type="checkbox" name="wpvgw_operation_import_old_tl_vgwort_plugin_markers" id="wpvgw_operation_import_old_tl_vgwort_plugin_markers" value="1" class="checkbox"/>
								<label for="wpvgw_operation_import_old_tl_vgwort_plugin_markers"><?php _e( 'Zählmarken aus dem Plugin „VG Wort“ von T. Leuschner importieren', WPVGW_TEXT_DOMAIN ); ?></label>
								<br/>
								<span class="description"><?php _e( 'Wenn das Plugin „VG Wort“ von T. Leuschner zuvor verwendet wurde, können die dort zugeordneten Zählmarken hier importiert werden. Es werden keine Daten gelöscht.', WPVGW_TEXT_DOMAIN ) ?></span>
							</p>
							<p>
								<?php _e( 'Diese Operationen sollten (jeweils) nur ein Mal ausgeführt werden. Es werden sowohl Zählmarken als auch deren Zuordnung zu Beiträgen importiert.', WPVGW_TEXT_DOMAIN ); ?>
							</p>
							<p>
								<?php _e( 'Falls es zu Konflikten mit bereits vorhandenen Beitrags-Zuordnungen kommt, wird keine Überschreibung vorgenommen.', WPVGW_TEXT_DOMAIN ); ?>
							</p>
							<p class="submit">
								<input type="submit" name="wpvgw_operation_import_old_markers" value="<?php _e( 'Alte Zählmarken und Beitrags-Zuordnungen importieren', WPVGW_TEXT_DOMAIN ); ?>" class="button-primary" / >
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
	 * Recalculates the post character count for posts.
	 * Only allowed post statuses and allowed post types will be considered.
	 */
	private function recalculate_post_character_count() {
		// recalculate the post character count
		$postsExtrasFillStats = $this->postsExtras->recalculate_all_post_character_count_in_db();

		// add stats admin message
		$this->add_admin_message(
		// number of filled posts
			_n(
				'Für einen Beitrag wurde die Zeichenanzahl neuberechnet.',
				sprintf( 'Für %s Beiträge wurden die Zeichenanzahlen neuberechnet.', number_format_i18n( $postsExtrasFillStats->numberOfPostExtrasUpdates ) ),
				$postsExtrasFillStats->numberOfPostExtrasUpdates,
				WPVGW_TEXT_DOMAIN
			),
			WPVGW_ErrorType::Update
		);

		// recalculations are no longer necessary
		$this->options->set_operations_post_character_count_recalculations_necessary( false );
	}

	/**
	 * Creates a message for import old makers stats.
	 *
	 * @param WPVGW_ImportOldMarkersAndPostsStats $import_old_markers_and_posts_stats Stats to get a message for.
	 *
	 * @return string A message for the stats.
	 */
	private function create_import_old_markers_and_posts_stats( WPVGW_ImportOldMarkersAndPostsStats $import_old_markers_and_posts_stats ) {
		return
			sprintf(
				__( 'aktualisiert %s, bereits vorhanden %s, die Integrität verletzten %s, Beiträge durchsucht %s', WPVGW_TEXT_DOMAIN ),
				number_format_i18n( $import_old_markers_and_posts_stats->numberOfUpdates ),
				number_format_i18n( $import_old_markers_and_posts_stats->numberOfDuplicates ),
				number_format_i18n( $import_old_markers_and_posts_stats->numberOfIntegrityErrors ),
				number_format_i18n( $import_old_markers_and_posts_stats->numberOfPosts )
			);
	}


	/**
	 * Handles the actions for the view.
	 */
	public function do_action() {
		// has to be called
		if ( !parent::do_action_base() )
			// do no actions
			return;


		// TODO: Seems to be a hack.
		// set maximum number of seconds operations can be executed to avoid aborts
		set_time_limit( $this->options->get_operation_max_execution_time() );


		// allowed post types
		if ( isset( $_POST['wpvgw_operation_allowed_post_types'] ) ) {
			// allowed post types
			$allowedPostTypes = array();

			// get allowed post types (strip slashes) from HTTP POST
			if ( isset( $_POST['wpvgw_allowed_post_types'] ) && is_array( $_POST['wpvgw_allowed_post_types'] ) ) {
				foreach ( $_POST['wpvgw_allowed_post_types'] as $key => $value ) {
					$allowedPostTypes[] = stripslashes( $key );
				}
			}

			// set new allowed post types; unknown post types will be removed
			$this->markersManager->set_allowed_post_types( $allowedPostTypes );

			$allowedPostTypesCount = count( $allowedPostTypes );

			// add admin message
			$this->add_admin_message(
				_n(
					'Der ausgewählte Beitrags-Typ wurde mit der Zählmarken-Funktion versehen.',
					sprintf( '%s ausgewählte Beitrags-Typen wurden mit der Zählmarken-Funktion versehen.', number_format_i18n( $allowedPostTypesCount ) ),
					$allowedPostTypesCount,
					WPVGW_TEXT_DOMAIN
				),
				WPVGW_ErrorType::Update
			);

			// recalculate character count of posts
			$this->recalculate_post_character_count();
		}


		// removed post types
		if ( isset( $_POST['wpvgw_operation_removed_post_types'] ) ) {
			// allowed post types
			$removedPostTypes = array();

			// get removed post types (strip slashes) from HTTP POST
			if ( isset( $_POST['wpvgw_removed_post_types'] ) && is_array( $_POST['wpvgw_removed_post_types'] ) ) {
				foreach ( $_POST['wpvgw_removed_post_types'] as $key => $value ) {
					$removedPostTypes[] = stripslashes( $key );
				}
			}

			// set new removed post types; choose all post types from current removed post types array without the selected post types (difference)
			$this->markersManager->set_removed_post_types( array_diff( $this->markersManager->get_removed_post_types(), $removedPostTypes ) );

			$removedPostTypesCount = count( $removedPostTypes );

			// add admin message
			$this->add_admin_message(
				_n(
					'Der ausgewählte nicht verfügbare Beitrags-Typ wurde entfernt.',
					sprintf( '%s ausgewählte nicht verfügbare Beitrags-Typen wurden entfernt.', number_format_i18n( $removedPostTypesCount ) ),
					$removedPostTypesCount,
					WPVGW_TEXT_DOMAIN
				),
				WPVGW_ErrorType::Update
			);
		}


		// recalculate character count of posts
		if ( isset( $_POST['wpvgw_operation_recalculate_character_count'] ) ) {
			$this->recalculate_post_character_count();
		}


		// import old markers
		if ( isset( $_POST['wpvgw_operation_import_old_markers'] ) ) {
			// import from old plugin version
			if ( isset( $_POST['wpvgw_operation_import_old_plugin_markers'] ) ) {

				$metaName = isset( $_POST['wpvgw_operation_import_old_plugin_markers_meta_name'] ) ? stripslashes( $_POST['wpvgw_operation_import_old_plugin_markers_meta_name'] ) : null;

				$invalidMetaName = false;
				try {
					$this->options->set_meta_name( $metaName );
				} catch ( Exception $e ) {
					$invalidMetaName = true;
					$this->add_admin_message( __( 'Der Meta-Name ist ungültig und wurde zurückgesetzt. Zählmarken aus altem VG-WORT-Plugin wurden nicht importiert.', WPVGW_TEXT_DOMAIN ) );
				}

				if ( !$invalidMetaName ) {
					// import old markers from WordPress posts meta
					$importOldMarkersAndPostsStats = $this->markersManager->import_markers_and_posts_from_old_version( $this->options->get_meta_name(), $this->options->get_default_server() );

					// import of markers from old plugin is not necessary anymore
					$this->options->set_operation_old_plugin_import_necessary( false );

					// add admin messages
					$this->add_admin_message( __( 'Zählmarken aus altem VG-WORT-Plugin importiert: ', WPVGW_TEXT_DOMAIN ) . $this->create_import_markers_stats_message( $importOldMarkersAndPostsStats->importMarkersStats ), WPVGW_ErrorType::Update );
					$this->add_admin_message( __( 'Zählmarken-Zuordnungen aus altem VG-WORT-Plugin importiert: ', WPVGW_TEXT_DOMAIN ) . $this->create_import_old_markers_and_posts_stats( $importOldMarkersAndPostsStats ), WPVGW_ErrorType::Update );
				}
			}

			// import manual markers from posts
			if ( isset( $_POST['wpvgw_operation_import_old_manual_markers'] ) ) {
				$manualMarkersRegex = isset( $_POST['wpvgw_operation_import_old_manual_markers_regex'] ) ? stripslashes( $_POST['wpvgw_operation_import_old_manual_markers_regex'] ) : null;
				$deleteManualMarkers = isset( $_POST['wpvgw_operation_import_old_manual_markers_delete'] );

				$invalidRegex = false;
				try {
					$this->options->set_import_from_post_regex( $manualMarkersRegex );
				} catch ( Exception $e ) {
					$invalidRegex = true;
					$this->add_admin_message( __( 'Der Reguläre Ausdruck hat eine ungültige Syntax und wurde zurückgesetzt. Zählmarken (manuelle) aus Beiträgen wurden nicht importiert.', WPVGW_TEXT_DOMAIN ) );
				}

				if ( !$invalidRegex ) {
					// import old manual markers from WordPress posts
					$importOldMarkersAndPostsStats = $this->markersManager->import_markers_and_posts_from_posts( $this->options->get_import_from_post_regex(), $this->options->get_default_server(), $deleteManualMarkers );

					// add admin messages
					$this->add_admin_message( __( 'Zählmarken (manuelle) aus Beiträgen importiert: ', WPVGW_TEXT_DOMAIN ) . $this->create_import_markers_stats_message( $importOldMarkersAndPostsStats->importMarkersStats ), WPVGW_ErrorType::Update );
					$this->add_admin_message( __( 'Zählmarken-Zuordnungen aus Beiträgen importiert: ', WPVGW_TEXT_DOMAIN ) . $this->create_import_old_markers_and_posts_stats( $importOldMarkersAndPostsStats ), WPVGW_ErrorType::Update );
				}
			}

			// import from T. Leuschner’s VG WORT plugin
			if ( isset( $_POST['wpvgw_operation_import_old_tl_vgwort_plugin_markers'] ) ) {
				// import old markers from T. Leuschner’s VG WORT plugin
				$importOldMarkersAndPostsStats = $this->markersManager->import_markers_and_posts_from_tl_vgwort_plugin( $this->options->get_default_server() );

				// add admin messages
				$this->add_admin_message( __( 'Zählmarken aus Plugin „VG Wort“ von T. Leuschner importiert: ', WPVGW_TEXT_DOMAIN ) . $this->create_import_markers_stats_message( $importOldMarkersAndPostsStats->importMarkersStats ), WPVGW_ErrorType::Update );
				$this->add_admin_message( __( 'Zählmarken-Zuordnungen aus Plugin „VG Wort“ von T. Leuschner importiert: ', WPVGW_TEXT_DOMAIN ) . $this->create_import_old_markers_and_posts_stats( $importOldMarkersAndPostsStats ), WPVGW_ErrorType::Update );
			}
		}
	}
}