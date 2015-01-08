<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */
 


class WPVGW_DataPrivacyAdminView extends WPVGW_AdminViewBase {

	
	public static function get_slug_static() {
		return 'data-privacy';
	}

	
	public static function get_long_name_static() {
		return __( 'Datenschutz-Hinweis', WPVGW_TEXT_DOMAIN );
	}

	
	public static function get_short_name_static() {
		return __( 'Datenschutz', WPVGW_TEXT_DOMAIN );
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
		<p class="wpvgw-admin-page-description">
			<?php _e( 'Sobald Zählmarken der VG WORT durch dieses Plugin verwendet werden, sollte der nachstehenden Datenschutz-Hinweis der VG WORT auf die Website eingefügt werden.', WPVGW_TEXT_DOMAIN ); ?>
		</p>
		<form method="post">
		<?php echo( parent::get_wp_number_once_field() ) ?>
			<table class="form-table wpvgw-form-table">
				<tbody>
					<tr>
						<th scope="row"><?php _e( 'Datenschutz-Hinweis', WPVGW_TEXT_DOMAIN ); ?></th>
						<td>
							<div class="wpvgw-quote-text">
								<h1>Cookies und Meldungen zu Zugriffszahlen</h1>
								<p>
									Wir setzen „Session-Cookies“ der VG Wort, München, zur Messung von Zugriffen auf Texten ein, um die
									Kopierwahrscheinlichkeit zu erfassen. Session-Cookies sind kleine Informationseinheiten, die ein Anbieter im
									Arbeitsspeicher des Computers des Besuchers speichert. In einem Session-Cookie wird eine zufällig erzeugte
									eindeutige Identifikationsnummer abgelegt, eine sogenannte Session-ID. Außerdem enthält ein Cookie die
									Angabe über seine Herkunft und die Speicherfrist. Session-Cookies können keine anderen Daten speichern.
									Diese Messungen werden von der INFOnline GmbH nach dem Skalierbaren Zentralen Messverfahren (SZM) durchgeführt.
									Sie helfen dabei, die Kopierwahrscheinlichkeit einzelner Texte zur Vergütung von gesetzlichen
									Ansprüchen von Autoren und Verlagen zu ermitteln. Wir erfassen keine personenbezogenen Daten über Cookies.
								</p>
								<p>
									Viele unserer Seiten sind mit JavaScript-Aufrufen versehen, über die wir die Zugriffe an die
									Verwertungsgesellschaft Wort (VG Wort) melden. Wir ermöglichen
									damit, dass unsere Autoren an den Ausschüttungen der VG Wort partizipieren, die die gesetzliche Vergütung
									für die Nutzungen urheberrechtlich geschützter Werke gem. § 53 UrhG sicherstellen.
								</p>
								<p>
									Eine Nutzung unserer Angebote ist auch ohne Cookies möglich. Die meisten Browser sind so eingestellt, dass
									sie Cookies automatisch akzeptieren. Sie können das Speichern von Cookies jedoch deaktivieren oder Ihren
									Browser so einstellen, dass er Sie benachrichtigt, sobald Cookies gesendet werden.
								</p>
								<h1>Datenschutzerklärung zur Nutzung des Skalierbaren Zentralen Messverfahrens</h1>
								<p>
									Unsere Website und unser mobiles Webangebot nutzen das „Skalierbare Zentrale Messverfahren“ (SZM) der
									INFOnline GmbH (https:
									Kopierwahrscheinlichkeit von Texten.
								</p>
								<p>
									Dabei werden anonyme Messwerte erhoben. Die Zugriffszahlenmessung verwendet zur Wiedererkennung von
									Computersystemen alternativ ein Session-Cookie oder eine Signatur, die aus verschiedenen automatisch
									übertragenen Informationen Ihres Browsers erstellt wird. IP-Adressen werden nur in anonymisierter Form verarbeitet.
								</p>
								<p>
									Das Verfahren wurde unter der Beachtung des Datenschutzes entwickelt. Einziges Ziel des Verfahrens ist es,
									die Kopierwahrscheinlichkeit einzelner Texte zu ermitteln.
									Zu keinem Zeitpunkt werden einzelne Nutzer identifiziert. Ihre Identität bleibt immer geschützt. Sie erhalten
									über das System keine Werbung.
								</p>
							</div>
							<p>
								<?php echo( sprintf( __( 'Quelle: %s', WPVGW_TEXT_DOMAIN ),
									sprintf( '<a href="https://tom.vgwort.de/portal/showParticipationCondition">%s</a>', __( 'PDF „Teilnahmebedingungen – T.O.M. (Stand Dezember 2013)“ von VG WORT', WPVGW_TEXT_DOMAIN ) )
								) ) ?>
							</p>
							<p>
								<?php _e( 'Die Autoren dieses Plugins übernehmen keine Haftung für die Korrektheit und Aktualität des zitierten Datenschutz-Hinweises.', WPVGW_TEXT_DOMAIN ); ?>
							</p>
							<p>
								<input type="checkbox" name="wpvgw_privacy_hide_warning" id="wpvgw_privacy_hide_warning" value="1" class="checkbox" <?php echo( WPVGW_Helper::get_html_checkbox_checked( $this->options->get_privacy_hide_warning() ) ) ?>/>
								<label for="wpvgw_privacy_hide_warning"><?php _e( 'Datenschutz-Hinweis zur Kenntnis genommen', WPVGW_TEXT_DOMAIN ); ?></label>
								<br/>
								<span class="description"><?php _e( 'Es wird keine Warnung mehr im Administrationsbereich angezeigt, wenn aktiviert.', WPVGW_TEXT_DOMAIN ) ?></span>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" name="wpvgw_privacy" value="<?php _e( 'Einstellung speichern', WPVGW_TEXT_DOMAIN ); ?>" class="button-primary" / >
			</p>
		</form>
		<?php


		
		parent::end_render_base();
	}

	
	public function do_action() {
		
		if ( !parent::do_action_base() )
			
			return;


		
		$hidePrivacyWarning = isset( $_POST['wpvgw_privacy_hide_warning'] );

		$this->options->set_privacy_hide_warning( $hidePrivacyWarning );


		$this->add_admin_message( __( 'Einstellungen erfolgreich übernommen.', WPVGW_TEXT_DOMAIN ), WPVGW_ErrorType::Update );
	}

}