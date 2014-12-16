<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: 2014 Ronny Harbich
 * License: GPLv2 or later
 */


/**
 * Represents the configuration view.
 */
class WPVGW_ConfigurationAdminView extends WPVGW_AdminViewBase {

	/**
	 * See {@link WPVGW_AdminViewBase::get_slug()}.
	 */
	public static function get_slug_static() {
		return 'configuration';
	}

	/**
	 * See {@link WPVGW_AdminViewBase::get_long_name()}.
	 */
	public static function get_long_name_static() {
		return __( 'Einstellungen', WPVGW_TEXT_DOMAIN );
	}

	/**
	 * See {@link WPVGW_AdminViewBase::get_short_name()}.
	 */
	public static function get_short_name_static() {
		return __( 'Einstellungen', WPVGW_TEXT_DOMAIN );
	}


	/**
	 * Creates a new instance of {@link WPVGW_ConfigurationAdminView}.
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
			array()
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
			<?php _e( 'Hier können allgemeine Einstellungen vorgenommen werden.', WPVGW_TEXT_DOMAIN ); ?>
		</p>
		<form method="post">
			<?php echo( parent::get_wp_number_once_field() ) ?>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?php _e( 'Zeichenanzahl', WPVGW_TEXT_DOMAIN ); ?></th>
						<td>
							<p>
								<label for="wpvgw_minimum_character_count"><?php _e( 'Minimale Zeichenanzahl', WPVGW_TEXT_DOMAIN ); ?></label>
								<br/>
								<input type="text" id="wpvgw_minimum_character_count" name="wpvgw_minimum_character_count" class="regular-text" value="<?php echo( esc_attr( $this->options->get_vg_wort_minimum_character_count() ) ); ?>"/>
								<br/>
								<span class="description"><?php _e( 'Die Mindestanzahl an Zeichen, die ein Beitrag haben muss, damit eine Zählmarke zugeordnet werden darf (wird von der VG WORT vorgegeben).', WPVGW_TEXT_DOMAIN ); ?></span>
								<br/>
								<span class="description"><?php echo( sprintf( __( 'Der Standardwert ist: %s', WPVGW_TEXT_DOMAIN ), esc_html( $this->options->default_vg_wort_minimum_character_count() ) ) ) ?></span>
							</p>
							<p>
								<input type="checkbox" name="wpvgw_do_shortcodes_for_character_count_calculation" id="wpvgw_do_shortcodes_for_character_count_calculation" value="1" class="checkbox" <?php echo( WPVGW_Helper::get_html_checkbox_checked( $this->options->get_do_shortcodes_for_character_count_calculation() ) ) ?>/>
								<label for="wpvgw_do_shortcodes_for_character_count_calculation"><?php _e( 'Shortcodes bei Berechnung der Zeichenanzahl auswerten', WPVGW_TEXT_DOMAIN ); ?></label>
								<br/>
								<span class="description"><?php _e( 'Bei Aktivierung werden <a href="http://codex.wordpress.org/Shortcode" target="_blank">Shortcodes</a> bei der Berechnung der Zeichanzahl eines Beitrags mit ausgewertet. Die Zeichenanzahl wird geanuer, aber die Berechnung dauert länger. Die Zeichanzahlen der Beiträge müssen nach Änderung neuberechnet werden.', WPVGW_TEXT_DOMAIN ) ?></span>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Zählmarken', WPVGW_TEXT_DOMAIN ); ?></th>
						<td>
							<p>
								<label for="wpvgw_output_format"><?php _e( 'Ausgabeformat einer Zählmarke', WPVGW_TEXT_DOMAIN ); ?></label>
								<br/>
								<input type="text" id="wpvgw_output_format" name="wpvgw_output_format" class="regular-text" value="<?php echo( esc_attr( $this->options->get_output_format() ) ); ?>"/>
								<br/>
								<span class="description"><?php _e( 'So wie in diesem Textfeld angegeben, wird eine Zählmarke auf Ihrer Website ausgegeben. Dies ist in der Regel ein HTML-Code.', WPVGW_TEXT_DOMAIN ); ?></span>
								<br/>
								<span class="description"><?php _e( '%1$s wird durch den Server ersetzt (ohne http://); %2$s wird durch die öffentliche Zählmarke ersetzt.', WPVGW_TEXT_DOMAIN ); ?></span>
								<br/>
								<span class="description"><?php echo( sprintf( __( 'Der Standardwert ist: %s', WPVGW_TEXT_DOMAIN ), esc_html( $this->options->default_output_format() ) ) ) ?></span>
							</p>
							<p>
								<label for="wpvgw_default_server"><?php _e( 'Standard-Server', WPVGW_TEXT_DOMAIN ); ?></label>
								<br/>
								<input type="text" id="wpvgw_default_server" name="wpvgw_default_server" class="regular-text" value="<?php echo( esc_attr( $this->options->get_default_server() ) ); ?>"/>
								<br/>
								<span class="description"><?php _e( 'Wenn für Zählmarken nicht explizit ein Server angegeben wurde (z. B. beim Importieren), wird dieser Server verwendet.', WPVGW_TEXT_DOMAIN ); ?></span>
								<br/>
								<span class="description"><?php echo( sprintf( __( 'Der Standardwert ist: %s', WPVGW_TEXT_DOMAIN ), esc_html( $this->options->default_default_server() ) ) ) ?></span>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Plugin-Warnungen', WPVGW_TEXT_DOMAIN ); ?></th>
						<td>
							<p>
								<?php _e( 'Bei Aktivierung der jeweiligen Einstellung wird eine entsprechende Warnung im Administrationsbereich angezeigt.', WPVGW_TEXT_DOMAIN ) ?>
							</p>
							<p>
								<input type="checkbox" name="wpvgw_show_other_active_vg_wort_plugins_warning" id="wpvgw_show_other_active_vg_wort_plugins_warning" value="1" class="checkbox" <?php echo( WPVGW_Helper::get_html_checkbox_checked( $this->options->get_show_other_active_vg_wort_plugins_warning() ) ) ?>/>
								<label for="wpvgw_show_other_active_vg_wort_plugins_warning"><?php _e( 'Warnung, falls andere VG-WORT-Plugins aktiviert sind', WPVGW_TEXT_DOMAIN ); ?></label>
								<br/>
								<span class="description"><?php _e( 'Wird angezeigt, falls andere Plugins zur Integration von Zählmarken der VG WORT aktiviert sind.', WPVGW_TEXT_DOMAIN ) ?></span>
							</p>
							<p>
								<input type="checkbox" name="wpvgw_show_old_plugin_import_warning" id="wpvgw_show_old_plugin_import_warning" value="1" class="checkbox" <?php echo( WPVGW_Helper::get_html_checkbox_checked( $this->options->get_operation_old_plugin_import_necessary() ) ) ?>/>
								<label for="wpvgw_show_old_plugin_import_warning"><?php _e( 'Warnung, falls Zählmarken aus früherer Plugin-Version importiert werden sollten', WPVGW_TEXT_DOMAIN ); ?></label>
								<br/>
								<span class="description"><?php _e( 'Wird angezeigt, falls Zählmarken aus einer früheren Version des Plugins importiert werden sollten. Diese Warnung wird nach dem Import automatisch deaktiviert.', WPVGW_TEXT_DOMAIN ) ?></span>
							</p>
						</td>
					</tr>
					<tr>
					<th scope="row"><label for="wpvgw_number_of_markers_per_page"><?php _e( 'Zählmarken pro Seite', WPVGW_TEXT_DOMAIN ); ?></label></th>
						<td>
							<p>
								<input type="text" id="wpvgw_number_of_markers_per_page" name="wpvgw_number_of_markers_per_page" class="regular-text" value="<?php echo( esc_attr( $this->options->get_number_of_markers_per_page() ) ); ?>"/>
								<br/>
								<span class="description"><?php _e( 'Die Anzahl der Zählmarken, die auf einer Seite in der Zählmarken-Übersicht (Tabelle) angezeigt werden soll.', WPVGW_TEXT_DOMAIN ); ?></span>
								<br/>
								<span class="description"><?php echo( sprintf( __( 'Der Standardwert ist: %s', WPVGW_TEXT_DOMAIN ), esc_html( $this->options->default_number_of_markers_per_page() ) ) ) ?></span>
							</p>
						</td>
					</tr>
					<tr>
					<th scope="row"><?php _e( 'Verschiedenes', WPVGW_TEXT_DOMAIN ); ?></th>
						<td>
							<p>
								<label for="wpvgw_number_of_markers_per_page"><?php _e( 'Zählmarken pro Seite in der Übersicht', WPVGW_TEXT_DOMAIN ); ?></label>
								<br/>
								<input type="text" id="wpvgw_number_of_markers_per_page" name="wpvgw_number_of_markers_per_page" class="regular-text" value="<?php echo( esc_attr( $this->options->get_number_of_markers_per_page() ) ); ?>"/>
								<br/>
								<span class="description"><?php _e( '<strong>Achtung</strong>: Bei Aktivierung werden sämtlichen Daten (Zählmarken, Zuordnungen usw.) unwiderruflich gelöscht, sobald das VG-WORT-Plugin deaktiviert wird!', WPVGW_TEXT_DOMAIN ) ?></span>
							</p>
							<p>
								<label for="wpvgw_operations_max_execution_time"><?php _e( 'Maximale Ausführungszeit für Operationen (in Sekunden)', WPVGW_TEXT_DOMAIN ); ?></label>
								<br/>
								<input type="text" id="wpvgw_operations_max_execution_time" name="wpvgw_operations_max_execution_time" class="regular-text" value="<?php echo( esc_attr( $this->options->get_operation_max_execution_time() ) ); ?>"/>
								<br/>
								<span class="description"><?php _e( 'Legt die maximale Zeitspanne in Sekunden fest, um Operationen im Bereich „Operationen“ auszuführen. Bitte erhöhen, falls Operationen abbrechen (siehe auch <a href="http://php.net/manual/de/function.set-time-limit.php" target="_blank">set_time_limit</a>).', WPVGW_TEXT_DOMAIN ); ?></span>
								<br/>
								<span class="description"><?php echo( sprintf( __( 'Der Standardwert ist: %s', WPVGW_TEXT_DOMAIN ), esc_html( $this->options->default_operation_max_execution_time() ) ) ) ?></span>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Deinstallation', WPVGW_TEXT_DOMAIN ); ?></th>
						<td>
							<p>
								<input type="checkbox" name="wpvgw_remove_data_on_uninstall" id="wpvgw_remove_data_on_uninstall" value="1" class="checkbox" <?php echo( WPVGW_Helper::get_html_checkbox_checked( $this->options->get_remove_data_on_uninstall() ) ) ?>/>
								<label for="wpvgw_remove_data_on_uninstall"><?php _e( 'Daten bei Plugin-Deaktivierung löschen', WPVGW_TEXT_DOMAIN ); ?></label>
								<br/>
								<span class="description"><?php _e( '<strong>Achtung</strong>: Bei Aktivierung werden sämtlichen Daten (Zählmarken, Zuordnungen usw.) unwiderruflich gelöscht, sobald das VG-WORT-Plugin deaktiviert wird!', WPVGW_TEXT_DOMAIN ) ?></span>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" name="wpvgw_configuration" value="<?php _e( 'Einstellungen speichern', WPVGW_TEXT_DOMAIN ); ?>" class="button-primary" / >
			</p>
		</form>
		<?php

		// has to be called
		parent::end_render_base();
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


		// minimum character count
		$minimumCharacterCount = isset( $_POST['wpvgw_minimum_character_count'] ) ? $_POST['wpvgw_minimum_character_count'] : null;

		if ( $minimumCharacterCount !== null ) {
			if ( is_numeric( $minimumCharacterCount ) ) {
				// convert minimum character count to integer
				$minimumCharacterCount = intval( $minimumCharacterCount );

				// minimum character count has to be non-negative
				if ( $minimumCharacterCount >= 0 )
					$this->options->set_vg_wort_minimum_character_count( $minimumCharacterCount );
				else
					$this->add_admin_message( __( 'Die nötige Zeichenanzahl muss einen Wert größer oder gleich 0 haben.', WPVGW_TEXT_DOMAIN ) );
			}
			else
				$this->add_admin_message( __( 'Die nötige Zeichenanzahl muss eine natürliche Zahl größer oder gleich 0 sein.', WPVGW_TEXT_DOMAIN ) );
		}


		// do shortcodes for character count calculation
		$doShortcodesForCharacterCountCalculation = isset( $_POST['wpvgw_do_shortcodes_for_character_count_calculation'] );

		// set only if updated
		if ( $doShortcodesForCharacterCountCalculation !== $this->options->get_do_shortcodes_for_character_count_calculation() ) {
			// set option
			$this->options->set_do_shortcodes_for_character_count_calculation( $doShortcodesForCharacterCountCalculation );
			// post character count recalculations are now necessary
			$this->options->set_operations_post_character_count_recalculations_necessary( true );
		}


		// output format
		$outputFormat = isset( $_POST['wpvgw_output_format'] ) ? stripslashes( $_POST['wpvgw_output_format'] ) : null;

		if ( $outputFormat !== null )
			$this->options->set_output_format( $outputFormat );


		// default server
		$defaultServer = isset( $_POST['wpvgw_default_server'] ) ? stripslashes( $_POST['wpvgw_default_server'] ) : null;

		if ( $defaultServer !== null && $this->markersManager->server_validator( $defaultServer ) )
			$this->options->set_default_server( $defaultServer );
		else
			$this->add_admin_message( __( 'Der eingegebene Standard-Server hat ein ungültiges Format.', WPVGW_TEXT_DOMAIN ) );


		// number of markers per page
		$numberOfMarkersPerPage = isset( $_POST['wpvgw_number_of_markers_per_page'] ) ? $_POST['wpvgw_number_of_markers_per_page'] : null;

		if ( $numberOfMarkersPerPage !== null ) {
			if ( is_numeric( $numberOfMarkersPerPage ) ) {
				// convert to integer
				$numberOfMarkersPerPage = intval( $numberOfMarkersPerPage );

				// minimum character count has to be non-negative
				if ( $numberOfMarkersPerPage >= 1 )
					$this->options->set_number_of_markers_per_page( $numberOfMarkersPerPage );
				else
					$this->add_admin_message( __( 'Die Anzahl der Zählmarken pro Seite muss einen Wert größer oder gleich 1 haben.', WPVGW_TEXT_DOMAIN ) );
			}
			else
				$this->add_admin_message( __( 'Die Anzahl der Zählmarken pro Seite muss eine natürliche Zahl größer oder gleich 1 sein.', WPVGW_TEXT_DOMAIN ) );
		}


		// maximum number of seconds operations can be executed
		$operationMaxExecutionTime = isset( $_POST['wpvgw_operations_max_execution_time'] ) ? $_POST['wpvgw_operations_max_execution_time'] : null;

		if ( $operationMaxExecutionTime !== null ) {
			if ( is_numeric( $operationMaxExecutionTime ) ) {
				// convert maximum number of seconds operations can be executed
				$operationMaxExecutionTime = intval( $operationMaxExecutionTime );

				// maximum number of seconds operations can be executed has to be positive
				if ( $operationMaxExecutionTime >= 1 )
					$this->options->set_operation_max_execution_time( $operationMaxExecutionTime );
				else
					$this->add_admin_message( __( 'Die maximale Ausführungszeit für Operationen (in Sekunden) muss einen Wert größer 0 haben.', WPVGW_TEXT_DOMAIN ) );
			}
			else
				$this->add_admin_message( __( 'Die maximale Ausführungszeit für Operationen (in Sekunden) muss eine natürliche Zahl größer als 0 sein.', WPVGW_TEXT_DOMAIN ) );
		}


		//show other active VG WORT Plugins warning
		$showOtherActiveVgWortPluginsWarning = isset( $_POST['wpvgw_show_other_active_vg_wort_plugins_warning'] );

		$this->options->set_show_other_active_vg_wort_plugins_warning( $showOtherActiveVgWortPluginsWarning );


		//show old plugin import warning
		$wpvgwShowOldPluginImportWarning = isset( $_POST['wpvgw_show_old_plugin_import_warning'] );

		$this->options->set_operation_old_plugin_import_necessary( $wpvgwShowOldPluginImportWarning );


		// remove data on uninstall
		$removeDataOnUninstall = isset( $_POST['wpvgw_remove_data_on_uninstall'] );

		$this->options->set_remove_data_on_uninstall( $removeDataOnUninstall );


		$this->add_admin_message( __( 'Einstellungen erfolgreich übernommen.', WPVGW_TEXT_DOMAIN ), WPVGW_ErrorType::Update );
	}

}