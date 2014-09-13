<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   PluginName
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Your Name or Company Name
 */

$action = isset( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : null;

if ( $action !== null ) {

	switch ( $action ) {

		case "save":

			$ctypes = array( 'post', 'page' );

			if ( isset( $_POST['cpt'] ) ) {
				foreach ( $_POST['cpt'] as $key => $value ) {
					$ctypes[] = sanitize_title( $key );
				}
				update_option( 'wp_cpt', $ctypes, array() );
			}
			else {
				// None of the CPT are selected. reset the option.
				update_option( 'wp_cpt', $ctypes );
			}

			$this->vgWortMeta = esc_html( $_POST['wp_vgwort_meta'] );

			$vgWortOptions = get_option('wp_vgwort_options');
			
			$vgWortOptions['showChars'] = esc_attr( $_POST['showchars'] );
		
			update_option( 'wp_vgwort_options', $vgWortOptions );
			update_option( 'wp_vgwortmetaname', $this->vgWortMeta );

			break;
			
			case "save_datenschutz":

				
			$vgWortOptions = get_option('wp_vgwort_options');
			
			$vgWortOptions['datenschutz'] = esc_attr( $_POST['datenschutz'] );
						
			update_option( 'wp_vgwort_options', $vgWortOptions );
			
			break;
			
	}

}

// TODO: Wozu ist das hier gut?
$this->vgWortMetaOption = get_option( 'wp_vgwortmetaname', 'wp_vgwortmarke' );
$datenschutz = "";
$vgWortOptions = get_option( 'wp_vgwort_options' );

?>
<div class="wrap">

	<h2><?php echo( esc_html( get_admin_page_title() ) ); ?></h2>

	<?php $section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : null; ?>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo( admin_url( 'options-general.php?page=' . $this->plugin_slug ) ); ?>" class="nav-tab <?php if ( $section === null ) {
			echo( 'nav-tab-active' );
		} ?>">
			<?php _e( 'Konfiguration', 'wp-vgwort-locale' ); ?>
		</a>
		<a href="<?php echo( admin_url( 'options-general.php?page=' . $this->plugin_slug . '&section=overview' ) ); ?>" class="nav-tab <?php if ( $section == 'overview' ) {
			echo( 'nav-tab-active' );
		} ?>">
		  <?php _e( 'Eingefügte Zählmarken', 'wp-vgwort-locale' ); ?>
		</a>
		<a href="<?php echo( admin_url( 'options-general.php?page=' . $this->plugin_slug . '&section=posts' ) ); ?>" class="nav-tab <?php if ( $section == 'posts' ) {
			echo( 'nav-tab-active' );
		} ?>">
		  <?php _e( 'Mögliche Zählmarken', 'wp-vgwort-locale' ); ?>
		</a>
		<a href="<?php echo( admin_url( 'options-general.php?page=' . $this->plugin_slug . '&section=export' ) ); ?>" class="nav-tab <?php if ( $section == 'export' ) {
			echo( 'nav-tab-active' );
		} ?>">
		  <?php _e( 'Export', 'wp-vgwort-locale' ); ?>
		</a>
		<a href="<?php echo( admin_url( 'options-general.php?page=' . $this->plugin_slug . '&section=misc' ) ); ?>" class="nav-tab <?php if ( $section == 'misc' ) {
			echo( 'nav-tab-active' );
		} ?>">
		  <?php _e( 'Sonstiges', 'wp-vgwort-locale' ); ?>
		</a>
		<a href="<?php echo( admin_url( 'options-general.php?page=' . $this->plugin_slug . '&section=datenschutz' ) ); ?>" class="nav-tab <?php if ( $section == 'datenschutz' ) {
			echo( 'nav-tab-active' );
		} ?>">
		  <?php _e( 'Datenschutz', 'wp-vgwort-locale' ); ?>
		</a>
	</h2>

	<?php switch ( $section ) {

		case "posts":

			global $wpdb;
			$results = $wpdb->get_results(
			  $wpdb->prepare(
				"SELECT * , CHAR_LENGTH(`post_content`) as charlength , post_type FROM " . $wpdb->posts . " WHERE post_status = 'publish' AND post_type NOT IN ('attachment','nav_menu_item','revison') HAVING charlength > '%d'",
				$this->requiredChars
			  )
			);
			?>

			<p><?php _e( 'Für die folgenden Beiträge/Seiten können Zählmarken eingefügt werden', 'wp-vgwort-locale' ); ?>
				:</p>

			<table class="widefat">
				<thead>
					<tr>
						<th><?php _e( 'Titel', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Zeichenanzahl', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Seiten-Typ', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Aktion', 'wp-vgwort-locale' ); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th><?php _e( 'Titel', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Zeichenanzahl', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Seiten-Typ', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Aktion', 'wp-vgwort-locale' ); ?></th>					</tr>
				</tfoot>
				<tbody>
					<?php foreach ( $results as $result ) {
						$vgwort = get_post_meta( $result->ID, $this->vgWortMeta, true );
						if ( empty( $vgwort ) ) {
							// Just Text nothing more :)
							$clearContentCount = $this->get_char_count( $result->post_title . $result->post_content );
							if ( $clearContentCount > $this->requiredChars ) {
								echo( '<tr>' );
								echo( '<td>' . $result->post_title . '</td>' );
								echo( '<td>' . $clearContentCount . '</td>' );
								echo( '<td>' . $result->post_type . '</td>' );
								echo( '<td>' );
								echo( '<a href="' . get_admin_url() . 'post.php?post=' . $result->ID . '&action=edit" title="' . __( 'Jetzt VG Wort einfügen', 'wp-vgwort-locale' ) . '">' );
								echo( __( 'Zählmarke einfügen', 'wp-vgwort-locale' ) );
								echo( '</a>' );
								echo( '</td>' );
								echo( '</tr>' );
							}
						}
					} ?>
				</tbody>
			</table>

			<?php break;

		case "export":
			?>
			<p>
				<?php _e( 'Die Export-Funktion dient der Ausgabe der bereits verwendeten Zählmarken in eine CSV-Datei.', 'wp-vgwort-locale' ); ?>
			</p>
			<p>
				<?php _e( 'CSV-Dateien können mit Tabellen-Programme wie LibreOffice - Calc oder Microsoft Excel geöffnet werden.', 'wp-vgwort-locale' ); ?>
			</p>
			<form action="<?php echo( sprintf( '%s?action=%s&amp;noheader=true', plugins_url( '/export.php', dirname( __FILE__ ) ), 'export' ) ); ?>" method="POST">
				<input type="hidden" name="action" value="export"/>
				<input type="submit" name="export" value="<?php _e( 'Exportieren', 'wp-vgwort-locale' ); ?>" class="button-primary"/>
			</form>

			<?php break;

		case "misc":
			?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e( 'Fehler und neue Funktionen', 'wp-vgwort-locale' ); ?></th>
					<td>
						<p>
							<?php _e( 'Wenn Sie einen Fehler in unserem Plugin gefunden haben oder einen Wunsch für eine neue Funktion haben, bitten wir Sie hiermit, uns diesen mitzuteilen.', 'wp-vgwort-locale' ); ?>
						</p>
						<p>
							<?php _e( 'Hierzu können Sie auf unserer Plugin-Seite einen Kommentar hinterlassen oder eine E-Mail an uns senden.', 'wp-vgwort-locale' ); ?>
						</p>
						<p>
							<?php _e( 'Kontakt:', 'wp-vgwort-locale' ); ?>
						</p>
						<p>
							<a href="http://www.mywebcheck.de/vg-wort-plugin-wordpress/" title="MyWebcheck – <?php _e( 'Plugin-Seite', 'wp-vgwort-locale' ); ?>" target="_blank">
								<?php _e( 'Plugin-Seite', 'wp-vgwort-locale' ); ?>
							</a>
						</p>
						<p>
							<a href="mailto:wgwortplugin@mywebcheck.de" title="<?php _e( 'Eine E-Mail an uns schreiben.', 'wp-vgwort-locale' ); ?>">wgwortplugin@mywebcheck.de</a>
						</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Plugin unterstützen', 'wp-vgwort-locale' ); ?></th>
					<td>
						<p>
							Über einen kleinen monetären Beitrag ihrerseits freuten wir uns sehr!
							Wir haben viele Stunden Arbeit investiert, um Ihnen dieses Plugin kostenfrei zu Verfügung zu stellen.
						</p>
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_s-xclick">
							<input type="hidden" name="hosted_button_id" value="2PJGA2XSNG8EQ">
							<input type="image" src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="<?php _e( 'Jetzt einfach, schnell und sicher online spenden – mit PayPal.', 'wp-vgwort-locale' ); ?>">
							<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1">
						</form>
					</td>
				</tr>
			</table>

			<?php
			break;

		case "overview":
			?>

			<?php
			global $wpdb;

			$exportDocument = "";
			$exportResult = $wpdb->get_results( $wpdb->prepare( 'SELECT WPP.ID , WPP.post_title, WPPM.meta_value  FROM ' . $wpdb->posts . ' WPP INNER JOIN ' . $wpdb->postmeta . ' WPPM ON WPP.ID = WPPM.post_id WHERE WPPM.meta_key  = %s', $this->vgWortMeta ), ARRAY_A );

			?>
			<p><?php _e( 'Für die folgenden Beiträge/Seiten sind bereits Zählmarken eingefügt worden', 'wp-vgwort-locale' ); ?>
				:</p>
			<table class="widefat">
				<thead>
					<tr>
						<th><?php _e( 'ID', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Titel', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Link', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Zählmarke', 'wp-vgwort-locale' ); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th><?php _e( 'ID', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Titel', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Link', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Zählmarke', 'wp-vgwort-locale' ); ?></th>
					</tr>
				</tfoot>
				<tbody>
					<?php
					foreach ( $exportResult as &$result ) {
						echo( '<tr>' );
						echo( '<td>' . $result['ID'] . '</td>' );
						echo( '<td>' . $result['post_title'] . '</td>' );
						echo( '<td>' . get_permalink( $result['ID'] ) . '</td>' );
						echo( '<td>' . htmlspecialchars( $result['meta_value'] ) . '</td>' );
						echo( '</tr>' );
					}
					?>
				</tbody>
			</table>

			<?php
			break;

		case "datenschutz":
			?>
			
			<p>
				<b><?php _e( 'Wir empfehlen bei Einsatz der VG WORT Zählenmarken die Datenschutzerklärung zu erweitern.', 'wp-vgwort-locale' ); ?></b>
			</p>
			<p>
				<?php _e( 'Den entsprechenden Absatz zur Datenschutzerklärung finden Sie in diesem <a href="http://tom.vgwort.de/portal/showParticipationCondition">PDF</a>.', 'wp-vgwort-locale' ); ?>
			</p>
			
			<form method="POST" action="">
			
			<?php 
			
			if ( isset( $vgWortOptions['datenschutz'] ) ) {
				if ( $vgWortOptions['datenschutz'] != '' ) {
					$datenschutz = 'checked="checked"';
				}
			}
			?>
			
				<input type="checkbox" <?php echo $datenschutz; ?> name="datenschutz" value="1" /> Ich habe diesen Hinweis zur Kenntnis genommen!<br /><br />
				<input type="hidden" name="action" value="save_datenschutz"/>
				<input type="submit" name="save" value="<?php _e( 'Einstellungen speichern', 'wp-vgwort-locale' ); ?>" class="button-primary" / >
				
			</form>
			
			<?php break;	
			
		default:
			?>
				<form method="POST" action="">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<?php _e( 'Custom Post Types', 'wp-vgwort-locale' ); ?>
							</th>
							<td>
								<?php
								$types = get_post_types( array( 'public' => true, 'show_ui' => true, '_builtin' => false ) );
								$myTypes = get_option( 'wp_cpt' );

								echo( '<p>' . __( 'Die ausgewählten Custom Post Types werden mit der Zählmarken-Funktion versehen:', 'wp-vgwort-locale' ) . '</p>' );

								if ( count( $types ) > 0 ) {
									echo( '<ul>' );
									foreach ( $types as $type ) {
										if ( $myTypes ) {
											if ( in_array( $type, $myTypes ) ) {
												echo( '<li><input checked="checked" type="checkbox" name="cpt[' . $type . ']"> ' . $type . ' </li>' );
											}
											else {
												echo( '<li><input type="checkbox" name="cpt[' . $type . ']"> ' . $type . ' </li>' );
											}
										}
										else {
											echo( '<li><input type="checkbox" name="cpt[' . $type . ']"> ' . $type . ' </li>' );
										}

									}
									echo( '</ul>' );
								}
								else {
									echo( '<strong>' . __( 'Keine anderen Typen vorhanden.', 'wp-vgwort-locale' ) . '</strong>' );
								} ?>
								<p>
									<span class="description"><?php _e( 'Hinweis: Für Beiträge und Seiten ist die Zählmarken-Funktion immer vorhanden.', 'wp-vgwort-locale' ); ?></span>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="wp_vgwort_meta"><?php _e( 'Meta-Name', 'wp-vgwort-locale' ); ?></label></th>
							<td>
								<input type="text" size="25" id="wp_vgwort_meta" name="wp_vgwort_meta" value="<?php echo( $this->vgWortMeta ); ?>"/><br/><br/>
								<span class="description"><?php _e( 'Der Meta-Name kann genutzt werden, um Kompatibilität zu anderen Plugins zu erhalten.', 'wp-vgwort-locale' ); ?></span>
								<ul>
									<li><?php _e( 'Standard-Wert', 'wp-vgwort-locale' ); ?>
										: wp_vgworkmarke</li>
								</ul>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Zeichenanzahl in Übersicht anzeigen', 'wp-vgwort-locale' ); ?></th>
							<td>
								<?php

								$showCharsInOverview = true;

								// Zeichen Anzeigen:
								if ( isset( $vgWortOptions['showChars'] ) ) {
									if ( $vgWortOptions['showChars'] != '' ) {
										$showCharsInOverview = $vgWortOptions['showChars'];
									}
								}

								?>
								<p>
									<span class="description"><?php _e( 'Soll in der Übersicht aller Beiträge/Seiten die Zeichenanzahl der Beiträge/Seiten angezeigt werden?', 'wp-vgwort-locale' ); ?></span>
								</p>
								<?php if ( $showCharsInOverview ) { ?>
									<p>
										<input type="radio" name="showchars" value="0" /> <?php _e( 'Nein', 'wp-vgwort-locale' ); ?>
									</p>
									<p>
										<input type="radio" name="showchars" checked="checked" value="1" /> <?php _e( 'Ja', 'wp-vgwort-locale' ); ?>
									</p>
								<?php
								}
								else {
									?>
									<p>
										<input type="radio" name="showchars" checked="checked" value="0" /> <?php _e( 'Nein', 'wp-vgwort-locale' ); ?>
									</p>
									<p>
										<input type="radio" name="showchars" value="1" /> <?php _e( 'Ja', 'wp-vgwort-locale' ); ?>
								    </p>
								<?php } ?>
							</td>
						</tr>
					</tbody>
				</table>
				<p class="submit">
					<input type="hidden" name="action" value="save"/>
					<input type="submit" name="save" value="<?php _e( 'Einstellungen speichern', 'wp-vgwort-locale' ); ?>" class="button-primary" / >
				</p>
				</form>
			<?php
	} ?>
</div>