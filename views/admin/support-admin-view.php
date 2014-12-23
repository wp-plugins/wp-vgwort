<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */



class WPVGW_SupportAdminView extends WPVGW_AdminViewBase {

	
	public static function get_slug_static() {
		return 'support';
	}

	
	public static function get_long_name_static() {
		return __( 'Hilfe und Support', WPVGW_TEXT_DOMAIN );
	}

	
	public static function get_short_name_static() {
		return __( 'Hilfe', WPVGW_TEXT_DOMAIN );
	}


	
	public function __construct( WPVGW_MarkersManager $markers_manager, WPVGW_PostsExtras $posts_extras, WPVGW_Options $options ) {
		parent::__construct( self::get_slug_static(), self::get_long_name_static(), self::get_short_name_static(), $markers_manager, $posts_extras, $options );
	}

	
	public function init() {
		
		parent::init_base(
		
			array()
		);
	}


	
	public function render() {
		
		parent::begin_render_base();


		
		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><?php _e( 'Neue Funktionen und Hilfe', WPVGW_TEXT_DOMAIN ); ?></th>
					<td>
						<p>
							<?php _e( 'Wenn Sie einen Wunsch für eine neue Funktion haben oder einfach nur Hilfe benötigen, treten Sie bitte mit uns in Kontakt:', WPVGW_TEXT_DOMAIN ); ?>
						</p>
						<p>
							<?php echo( sprintf( __( 'Websites: %s, %s (WordPress.org) oder alternativ %s', WPVGW_TEXT_DOMAIN ),
								sprintf( '<a href="https://wordpress.org/plugins/wp-vgwort/faq/" target="_blank">%s</a>', __( 'FAQ', WPVGW_TEXT_DOMAIN ) ),
								sprintf( '<a href="https://wordpress.org/support/plugin/wp-vgwort" target="_blank">%s</a>', __( 'Support', WPVGW_TEXT_DOMAIN ) ),
								sprintf( '<a href="http://www.mywebcheck.de/vg-wort-plugin-wordpress/" target="_blank">%s</a>', __( 'Plugin-Seite auf my Webcheck', WPVGW_TEXT_DOMAIN ) )
							) ) ?>
						</p>
						<p>
							<?php echo( sprintf( __( 'E-Mail: %s', WPVGW_TEXT_DOMAIN ),
								'<a href="mailto:wgwortplugin@mywebcheck.de">wgwortplugin@mywebcheck.de</a>'
							) ) ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Fehler melden', WPVGW_TEXT_DOMAIN ); ?></th>
					<td>
						<p>
							<?php _e( 'Wenn Sie einen kritischen Fehler im Plugin entdeckt haben, können Sie sich auch direkt an die Entwickler wenden:', WPVGW_TEXT_DOMAIN ); ?>
						</p>
						<p>
							<?php echo( sprintf( __( 'E-Mail: %s', WPVGW_TEXT_DOMAIN ),
								'<a href="mailto:developer@prosodia.de">developer@prosodia.de</a>'
							) ) ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php


		
		parent::end_render_base();
	}

	
	public function do_action() {
		
		if ( !parent::do_action_base() )
			
			return;
	}

}