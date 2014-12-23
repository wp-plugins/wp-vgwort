<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */



class WPVGW_AboutAdminView extends WPVGW_AdminViewBase {

	
	public static function get_slug_static() {
		return 'about';
	}

	
	public static function get_long_name_static() {
		return __( 'Impressum Prosodia VGW OS', WPVGW_TEXT_DOMAIN );
	}

	
	public static function get_short_name_static() {
		return __( 'Impressum', WPVGW_TEXT_DOMAIN );
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
					<th scope="row"><?php _e( 'Autor', WPVGW_TEXT_DOMAIN ); ?></th>
					<td>
						<p>
							<a href="http://prosodia.de/">
								<img class="wpvgw-about-logo" src="<?php echo( WPVGW_PLUGIN_URL . '/images/prosodia-logo.png' ) ?>" alt="Prosodia – Verlag für Musik und Literatur"/>
							</a>
						</p>
						<p>
							<?php _e( 'Max Heckel, Ronny Harbich, Rebekka Hempel, Torsten Klein – Prosodia GbR<br/>Max Heckel z. Hd. Ronny Harbich<br/>Arneburger Straße 37T<br/>39590 Tangermünde', WPVGW_TEXT_DOMAIN ); ?>
						</p>
						<p>
							<?php _e( 'E-Mail: <a href="mailto:info@prosodia.de">info@prosodia.de</a><br/>Website: <a href="http://prosodia.de/">prosodia.de</a>', WPVGW_TEXT_DOMAIN ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Partner', WPVGW_TEXT_DOMAIN ); ?></th>
					<td>
						<p>
							<a href="http://conversion-junkies.de/">
								<img class="wpvgw-about-logo" src="<?php echo( WPVGW_PLUGIN_URL . '/images/conversionjunkies-logo.png' ) ?>" alt="Conversion Junkies"/>
							</a>
						</p>
						<p>
							<?php _e( 'Conversion Junkies 2.0 Gesellschaft mit beschränkter Haftung (GmbH)<br/>Marcus Franke, Ronny Siegel<br/>Gutzkowstr. 30<br/>01069 Dresden', WPVGW_TEXT_DOMAIN ); ?>
						</p>
						<p>
							<?php _e( 'E-Mail: <a href="mailto:info@conversion-junkies.de">info@conversion-junkies.de</a><br/>Website: <a href="http://conversion-junkies.de/">conversion-junkies.de</a>', WPVGW_TEXT_DOMAIN ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Lizenz und Haftung', WPVGW_TEXT_DOMAIN ); ?></th>
					<td>
						<p>
							<?php _e( 'Prosodia VGW OS wird von der Max Heckel, Ronny Harbich, Rebekka Hempel, Torsten Klein – Prosodia GbR unter der GPLv2-Lizenz vertrieben, die unter <a href="http://www.gnu.org/licenses/gpl-2.0.html">http://www.gnu.org/licenses/gpl-2.0.html</a> nachzulesen ist. Sie vertreibt diese Software vollständig kostenlos und übernimmt für diese daher keine Haftung außer die vom Bürgerlichen Gesetzbuch (BGB) zwingend erforderliche. Der Haftungsausschluss soll – soweit wie mit dem BGB vereinbar – der GPLv2-Lizenz entsprechen.', WPVGW_TEXT_DOMAIN ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Hinweis', WPVGW_TEXT_DOMAIN ); ?></th>
					<td>
						<p>
							<?php _e( 'Prosodia VGW OS wird von der VG WORT weder unterstützt noch von ihr vertrieben.', WPVGW_TEXT_DOMAIN ); ?>
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