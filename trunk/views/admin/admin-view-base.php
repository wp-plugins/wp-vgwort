<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: 2014 Ronny Harbich
 * License: GPLv2 or later
 */
 

/**
 * Represents the administration views.
 */
abstract class WPVGW_AdminViewBase extends WPVGW_ViewBase {

	/**
	 * @var array An array of admin messages: array( 'message' => $message, 'type' => $type )
	 */
	protected $adminMessages = array();
	/**
	 * @var string The slug for the view.
	 */
	private $slug;
	/**
	 * @var string The long name of the view.
	 */
	private $longName;
	/**
	 * @var string The short name of the view.
	 */
	private $shortName;


	/**
	 * Gets the slug for the view.
	 *
	 * @return string The slug for the view.
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Gets the long name of the view.
	 *
	 * @return string The long name of the view.
	 */
	public function get_long_name() {
		return $this->longName;
	}

	/**
	 * Gets The short name of the view.
	 *
	 * @return string The short name of the view.
	 */
	public function get_short_name() {
		return $this->shortName;
	}


	/**
	 * Creates a new instance of {@link WPVGW_AdminViewBase}.
	 *
	 * @param string $slug The slug for the view.
	 * @param string $long_name The long name of the view.
	 * @param string $short_name The short name of the view.
	 * @param WPVGW_MarkersManager $markers_manager A markers manager.
	 * @param WPVGW_PostsExtras $posts_extras The posts extras.
	 * @param WPVGW_Options $options The options.
	 */
	public function __construct( $slug, $long_name, $short_name, WPVGW_MarkersManager $markers_manager, WPVGW_PostsExtras $posts_extras, WPVGW_Options $options ) {
		parent::__construct( $markers_manager, $options, $posts_extras );

		$this->slug = $slug;
		$this->longName = $long_name;
		$this->shortName = $short_name;
	}


	/**
	 * Renders the view.
	 * Inheritors must call {link begin_render_base()} and {link end_render_base()} in this function.
	 *
	 * @throws Exception Thrown if view is not initialized or a database error occurred.
	 */
	public abstract function render();

	/**
	 * Begins the view rendering.
	 * Inheritors must call this function in {link render()}.
	 *
	 * @throws Exception Thrown if view is not initialized.
	 */
	protected function begin_render_base() {
		// throw exception if view is not initialized
		if ( !$this->is_init() )
			throw new Exception( 'View not initialized. Rendering cannot be done.' );

		// check if current user has permission
		if ( !current_user_can( 'manage_options' ) )
			WPVGW_Helper::die_cheating();

		// start render
		?>
		<div class="wrap">
		<h2><?php echo( esc_html( $this->get_long_name() ) ); ?></h2>
		<?php
		// render admin messages
		WPVGW_Helper::render_admin_messages( $this->adminMessages );
	}

	/**
	 * Ends the view rendering.
	 * Inheritors must call this function in {link render()}.
	 */
	protected function end_render_base() {
		// end render
		?>
		</div>
	<?php
	}

	/**
	 * Handles the actions for the view.
	 * Inheritors must call {link do_action()} in this function.
	 */
	public abstract function do_action();

	/**
	 * Handles the base actions for the view.
	 * Inheritors must call this function in {link do_action()}.
	 *
	 * @return bool True if actions should be done, otherwise false.
	 * @throws Exception Thrown if view is not initialized.
	 */
	protected function do_action_base() {
		// throw exception if view is not initialized
		if ( !$this->is_init() )
			throw new Exception( 'View not initialized. Do action cannot be done.' );

		// check if current user has permission
		if ( !current_user_can( 'manage_options' ) )
			WPVGW_Helper::die_cheating();

		if ( isset( $_REQUEST['_wpvgwadminviewnonce'] ) ) {
			// security: verify wordpress’ number once
			if ( !wp_verify_nonce( $_REQUEST['_wpvgwadminviewnonce'], $this->slug ) )
				WPVGW_Helper::die_cheating();

			return true;
		}
		else
			return false;
	}


	/**
	 * Gets a hidden HTML input field that contains WordPress’ number once.
	 * For security this field must be included if an inheritor renders HTML forms.
	 *
	 * @return string A hidden HTML input field that contains WordPress’ number once.
	 */
	protected function get_wp_number_once_field() {
		return wp_nonce_field( $this->slug, '_wpvgwadminviewnonce', false, false );
	}


	/**
	 * Creates a message from specified import marker stats for the user.
	 *
	 * @param WPVGW_ImportMarkersStats $import_markers_stats
	 *
	 * @return string A message for the user.
	 */
	protected function create_import_markers_stats_message( WPVGW_ImportMarkersStats $import_markers_stats ) {
		return
			sprintf(
				__( 'hinzugefügt %s, aktualisiert %s, bereits vorhanden %s, mit ungültigem Format %s, die Integrität verletzten %s, insgesamt gefunden %s', WPVGW_TEXT_DOMAIN ),
				number_format_i18n( $import_markers_stats->numberOfInsertedMarkers ),
				number_format_i18n( $import_markers_stats->numberOfUpdatedMarkers ),
				number_format_i18n( $import_markers_stats->numberOfDuplicateMarkers ),
				number_format_i18n( $import_markers_stats->numberOfFormatErrors ),
				number_format_i18n( $import_markers_stats->numberOfIntegrityErrors ),
				number_format_i18n( $import_markers_stats->numberOfMarkers )
			);
	}

	/**
	 * Adds an admin message.
	 *
	 * @param string $message An admin message, e. g., an error message.
	 * @param int $type One of the constants defined in {@link WPVGW_ErrorType}.
	 */
	protected function add_admin_message( $message, $type = WPVGW_ErrorType::Error ) {
		$this->adminMessages[] = array( 'message' => $message, 'type' => $type );
	}

}



