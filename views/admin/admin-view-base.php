<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */



abstract class WPVGW_AdminViewBase extends WPVGW_ViewBase {

	
	protected $adminMessages = array();
	
	private $slug;
	
	private $longName;
	
	private $shortName;


	
	public function get_slug() {
		return $this->slug;
	}

	
	public function get_long_name() {
		return $this->longName;
	}

	
	public function get_short_name() {
		return $this->shortName;
	}


	
	public function __construct( $slug, $long_name, $short_name, WPVGW_MarkersManager $markers_manager, WPVGW_PostsExtras $posts_extras, WPVGW_Options $options ) {
		parent::__construct( $markers_manager, $options, $posts_extras );

		$this->slug = $slug;
		$this->longName = $long_name;
		$this->shortName = $short_name;
	}


	
	public abstract function render();

	
	protected function begin_render_base() {
		
		if ( !$this->is_init() )
			throw new Exception( 'View not initialized. Rendering cannot be done.' );

		
		if ( !current_user_can( 'manage_options' ) )
			WPVGW_Helper::die_cheating();

		
		?>
		<div class="wrap">
		<h2><?php echo( esc_html( $this->get_long_name() ) ); ?></h2>
		<?php
		
		WPVGW_Helper::render_admin_messages( $this->adminMessages );
	}

	
	protected function end_render_base() {
		
		?>
		</div>
	<?php
	}

	
	public abstract function do_action();

	
	protected function do_action_base() {
		
		if ( !$this->is_init() )
			throw new Exception( 'View not initialized. Do action cannot be done.' );

		
		if ( !current_user_can( 'manage_options' ) )
			WPVGW_Helper::die_cheating();

		if ( isset( $_REQUEST['_wpvgwadminviewnonce'] ) ) {
			
			if ( !wp_verify_nonce( $_REQUEST['_wpvgwadminviewnonce'], $this->slug ) )
				WPVGW_Helper::die_cheating();

			return true;
		}
		else
			return false;
	}


	
	protected function get_wp_number_once_field() {
		return wp_nonce_field( $this->slug, '_wpvgwadminviewnonce', false, false );
	}


	
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

	
	protected function add_admin_message( $message, $type = WPVGW_ErrorType::Error, $escape = true ) {
		$this->adminMessages[] = array( 'message' => $message, 'type' => $type, 'escape' => $escape );
	}

}



