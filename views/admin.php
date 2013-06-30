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

$action = sanitize_text_field( $_POST['action'] );
		
if( isset( $action ) ) {

	switch( $action ){

		case"save":

			if( isset( $_POST['cpt'] ) ) {
				$ctypes = array( 'post', 'page' );
				foreach( $_POST['cpt'] as $key => $value ){
					$ctypes[] = sanitize_title( $key );
				}
				update_option( 'wp_cpt', $ctypes, array() );
			} elseif( is_null( $_POST['cpt'] ) ) {
				// None of the CPT are selected. reset the option.
				$wp_cpt_old = get_option( 'wp_cpt' );
				update_option( 'wp_cpt', array( 'post', 'page' ), $wp_cpt_old );
			}

			$this->vgWortMeta = esc_html( $_POST['wp_vgwort_meta'] );

			$vgWortOptions = array(
				'showChars' => esc_attr( $_POST['showchars'] )
			);

			update_option( 'wp_vgwort_options', $vgWortOptions );
			update_option( 'wp_vgwortmetaname', $this->vgWortMeta );

		break;
	}

}
		
$this->vgWortMetaOption = get_option( 'wp_vgwortmetaname', 'wp_vgwortmarke' );
	
$vgWortOptions = get_option( 'wp_vgwort_options' );
		
?>
<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<?php $section = sanitize_text_field( $_GET['section'] ); ?>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo admin_url( 'options-general.php?page=' . $this->plugin_slug ); ?>" class="nav-tab <?php if( empty( $section ) ) { echo 'nav-tab-active'; } ?>">
			<?php _e( 'Konfiguration', 'wp-vgwort-locale' ); ?>
		</a>
		<a href="<?php echo admin_url( 'options-general.php?page=' . $this->plugin_slug . '&section=export' ); ?>" class="nav-tab <?php if( $section == 'export' ) { echo 'nav-tab-active'; } ?>">
		  <?php _e( 'Export', 'wp-vgwort-locale' ); ?>
		</a>
		<a href="<?php echo admin_url( 'options-general.php?page=' . $this->plugin_slug . '&section=misc' ); ?>" class="nav-tab <?php if( $section == 'misc' ) { echo 'nav-tab-active'; } ?>">
		  <?php _e( 'Allgemein', 'wp-vgwort-locale' ); ?>
		</a>
		<a href="<?php echo admin_url( 'options-general.php?page=' . $this->plugin_slug . '&section=overview' ); ?>" class="nav-tab <?php if( $section == 'overview' ) { echo 'nav-tab-active'; } ?>">
		  <?php _e( 'Übersicht', 'wp-vgwort-locale' ); ?>
		</a>
		<a href="<?php echo admin_url( 'options-general.php?page=' . $this->plugin_slug . '&section=posts' ); ?>" class="nav-tab <?php if( $section == 'posts' ) { echo 'nav-tab-active'; } ?>">
		  <?php _e( 'Beiträge', 'wp-vgwort-locale' ); ?>
		</a>
	</h2>

	<?php switch( $section ) {
	
		case "posts":
		
			global $wpdb;
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * , CHAR_LENGTH(`post_content`) as charlength , post_type FROM ".$wpdb->posts." WHERE post_status = 'publish' AND post_type NOT IN ('attachment','nav_menu_item','revison') HAVING charlength > '%d'",
					$this->requiredChars
				)
			);
			?>
	
			<h3><?php _e( 'Hier können Wortmarken eingefügt werden', 'wp-vgwort-locale' ); ?>:</h3>
	
			<table class="widefat">
				<thead>
					<tr>
						<th><?php _e( 'Titel', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Anzahl Zeichen', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Type', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Aktion', 'wp-vgwort-locale' ); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th><?php _e( 'Titel', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Anzahl Zeichen', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Type', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Aktion', 'wp-vgwort-locale' ); ?></th>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach( $results as $result ) {
						$vgwort = get_post_meta( $result->ID, $this->vgWortMeta, true );
						if( empty( $vgwort ) ) {
							// Just Text nothing more :)
							$clearContentCount = $this->get_char_count( $result->post_title . $result->post_content );
							if( $clearContentCount > $this->requiredChars ) {
								echo '<tr>';
									echo '<td>'.$result->post_title.'</td>';
									echo '<td>'.$clearContentCount.'</td>';
									echo '<td>'.$result->post_type.'</td>';
									echo '<td>';
										echo '<a href="'.get_admin_url().'post.php?post='.$result->ID.'&action=edit" title="' . __( 'Jetzt VG Wort einfügen', 'wp-vgwort-locale' ) . '">';
											echo __( 'Wortmarken einfügen', 'wp-vgwort-locale' );
										echo '</a>';
									echo '</td>';
								echo '</tr>';
							}
						}
					} ?>
				</tbody>
			</table>

			<?php break;

		case "export": ?>
		
			<table>
				<tr valign="top">
					<th scope="row"><h3><?php _e( 'VG Wort Marken Export', 'wp-vgwort-locale' ); ?>:</h3></th>
					<td></td>
				</tr>
				<tr>
					<td></td>
					<td>
						<p>
							<?php _e( 'Die Export Funktion dient zur Ausgabe der genutzten VG Wort Marken in eine CSV.', 'wp-vgwort-locale' ); ?><br />
							<?php _e( 'Diese Datei kann mit einen Tabellen Programm wie Excel geöffnet werden.', 'wp-vgwort-locale' ); ?>
						</p>
						<form action="<?php echo sprintf( '%s?action=%s&amp;noheader=true', plugins_url( $this->plugin_name . '/export.php', dirname(__FILE__) ), 'export' ); ?>" method="POST">
							<input type="hidden" name="action" value="export" />
							<input type="submit" name="export" value="<?php _e( 'exportieren', 'wp-vgwort-locale' ); ?>" class="button-primary" />
						</form>
					</td>
				</tr>
			</table>

			<?php
			break;

		case"misc": ?>
			<table>
				<tr valign="top">
					<th scope="row"><h3><?php _e( 'Fehler und Bugs', 'wp-vgwort-locale' ); ?>:</h3></th>
					<td>
						<p>
							<?php _e( 'Wenn Fehler Ihr Fehler in unserem Plugin gefunden habt, dann wäre es sehr nett wenn Ihr uns diese mitteilt.', 'wp-vgwort-locale' ); ?><br />
							<?php _e( 'Dazu könnt Ihr auf unserer Plugin Seite kommentieren oder eine E-Mail an uns senden.', 'wp-vgwort-locale' ); ?>
						</p>
						<ul>
							<li>
								<?php _e( 'Kontakt', 'wp-vgwort-locale' ); ?>
							</li>
							<li>
								<a href="http://www.mywebcheck.de/vg-wort-plugin-wordpress/" title="MyWebcheck - <?php _e( 'Plugin Seite', 'wp-vgwort-locale' ); ?>" target="_blank">
									<?php _e( 'Plugin Seite', 'wp-vgwort-locale' ); ?>
								</a>
							</li>
							<li>
								<a href="mailto:wgwortplugin@mywebcheck.de" title="<?php _e( 'Schreib mir eine Mail', 'wp-vgwort-locale' ); ?>">wgwortplugin@mywebcheck.de</a>
							</li>
						</ul>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><h3><?php _e( 'Cooles Plugin?', 'wp-vgwort-locale' ); ?>:</h3></th>
					<td>
						<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
						<input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="hosted_button_id" value="2PJGA2XSNG8EQ">
						<input
							type="image"
							src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donateCC_LG.gif"
							border="0"
							name="submit"
							alt="<?php _e( 'Jetzt einfach, schnell und sicher online bezahlen – mit PayPal.', 'wp-vgwort-locale' ); ?>"
						>
						<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1">
						</form>
					</td>
				</tr>
			</table>

			<?php
			break;

		case"overview": ?>
			
			<?php
			global $wpdb;

			$exportDocument = "";
			$exportResult = $wpdb->get_results( $wpdb->prepare( 'SELECT WPP.ID , WPP.post_title, WPPM.meta_value  FROM ' . $wpdb->posts . ' WPP INNER JOIN ' . $wpdb->postmeta . ' WPPM ON WPP.ID = WPPM.post_id WHERE WPPM.meta_key  = %s', $this->vgWortMeta ), ARRAY_A );

			?>

			<table class="widefat">
				<thead>
					<tr>
						<th><?php _e( 'ID', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Titel', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Url', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Wortmarke', 'wp-vgwort-locale' ); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th><?php _e( 'ID', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Titel', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Url', 'wp-vgwort-locale' ); ?></th>
						<th><?php _e( 'Wortmarke', 'wp-vgwort-locale' ); ?></th>
					</tr>
				</tfoot>
				<tbody>
					<?php
					foreach( $exportResult as &$result ) {
						echo '<tr>';
							echo '<td>'.$result['ID'].'</td>';
							echo '<td>'.$result['post_title'].'</td>';
							echo '<td>'.get_permalink( $result['ID'] ).'</td>';
							echo '<td>'.htmlspecialchars( $result['meta_value'] ).'</td>';
						echo '</tr>';
					}
					?>
				</tbody>
			</table>

			<?php
			break;
		
		default: ?>
			<form method="POST" action="">
				<h2><?php _e( 'Einstellungen VG-Wort Plugin', 'wp-vgwort-locale' ); ?></h2>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<h3><?php _e( 'Konfiguration', 'wp-vgwort-locale' ); ?>:</h3>
						</th>
						<td>
							<?php
							$types = get_post_types( array( 'public' => true, 'show_ui' => true, '_builtin' => false ) );
							$myTypes = get_option( 'wp_cpt' );

							echo '<h4>' . __( 'Custom Post Types', 'wp-vgwort-locale' ) . '</h4>';
							echo'<p>' . __( 'Markierte Custom Post Types werden mit der VG Wort Funktion versehen', 'wp-vgwort-locale' ) . '</p>';

							if( count( $types ) > 0 ) {
								echo '<ul>';
									foreach( $types as $type ) {
										if( $myTypes ) {
											if( in_array( $type, $myTypes ) ) {
												echo '<li><input checked="checked" type="checkbox" name="cpt['.$type.']"> '.$type.' </li>';
											} else {
												echo '<li><input type="checkbox" name="cpt['.$type.']"> '.$type.' </li>';
											}
										} else {
											echo '<li><input type="checkbox" name="cpt['.$type.']"> '.$type.' </li>';
										}

									}
								echo '</ul>';
							} else {
								echo '<strong>' . __( 'Keine anderen Typen vorhanden!', 'wp-vgwort-locale' ) . '</strong>';
							} ?>
							<p>
								<span class="description"><?php _e( 'In Beiträge und Seiten sind immer vorhanden!', 'wp-vgwort-locale' ); ?></span>
							</p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><h3><?php _e( 'MetaName', 'wp-vgwort-locale' ); ?>:</h3></th>
						<td>
							<input size="25" name="wp_vgwort_meta" value="<?php echo $this->vgWortMeta; ?>" /><br /><br />
							<span class="description"><?php _e( 'Dieses Feld kann genutzt werden um ein kompatible Lösung für andere Plugins zu erhalten', 'wp-vgwort-locale' ); ?></span>
							<ul>
								<li><?php _e( 'Default', 'wp-vgwort-locale' ); ?>: "wp_vgworkmarke"</li>
								<li>
									<a href="http://maheo.eu/355-vg-wort-plugin-fuer-wordpress.php" title="VG Wort Plugin - Heiner Otterstedt" target="_blank">
										<?php _e( 'VG-Wort Krimskram', 'wp-vgwort-locale' ); ?>
									</a>  -> "vgwpixel"
								</li>
							</ul>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><h3><?php _e( 'Zeichen anzeigen', 'wp-vgwort-locale' ); ?>:</h3></th>
						<td>
							<?php

							$showCharsInOverview = true;

							// Zeichen Anzeigen:
							if( isset( $vgWortOptions['showChars'] ) ) {
								if( $vgWortOptions['showChars'] != '' ) {
									$showCharsInOverview = $vgWortOptions['showChars'];
								}
							}

							?>
							<span class="description"><?php _e( 'Soll in den ausgewählten Beiträgen die Zeichen Anzahl angezeigt werden?', 'wp-vgwort-locale' ); ?></span>
							<br />
							<?php if( $showCharsInOverview ) {  ?>
								<input type="radio" name="showchars" value="0" /> <?php _e( 'Nein', 'wp-vgwort-locale' ); ?><br />
								<input type="radio" name="showchars" checked="checked" value="1" /> <?php _e( 'Ja', 'wp-vgwort-locale' ); ?><br/>
							<?php } else { ?>
								<input type="radio" name="showchars" checked="checked" value="0" /> <?php _e( 'Nein', 'wp-vgwort-locale' ); ?><br />
								<input type="radio" name="showchars" value="1" /> <?php _e( 'Ja', 'wp-vgwort-locale' ); ?><br/>
							<?php } ?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"> <h3><?php _e( 'Speichern', 'wp-vgwort-locale' ); ?>:</h3> </th>
						<td>
							<input type="hidden" name="action" value="save" />
							<input type="submit" name="save" value="<?php _e( 'Einstellung speichern', 'wp-vgwort-locale' ); ?>" class="button-primary" / >
						</td>
					</tr>
				</table>
			</form>
	<?php } ?>
</div>