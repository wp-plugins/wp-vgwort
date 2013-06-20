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
		
	if(isset( $action )) {
	
		switch($action){
	
		case"save":
			
			if(isset($_POST['cpt'])) {
				$ctypes = array('post','page');
				foreach($_POST['cpt'] as $key => $value){
					$ctypes[] = sanitize_title( $key );
				}
				
				update_option('wp_cpt' , $ctypes,array());
				
			}	

			$this->vgWortMeta = esc_html($_POST['wp_vgwort_meta']);
			
			$vgWortOptions = array(
				'showChars' => esc_attr($_POST['showchars'])
				
			);
			
			update_option('wp_vgwort_options' , $vgWortOptions);
			update_option('wp_vgwortmetaname' , $this->vgWortMeta);
			
		break;
		}
		
	}
		
	$this->vgWortMetaOption = get_option( 'wp_vgwortmetaname' , 'wp_vgwortmarke' );
	
	$vgWortOptions = get_option('wp_vgwort_options');
		
	?>
	<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

		
		<?php $section = sanitize_text_field( $_GET['section'] ); ?>
	
	
	<h2 class="nav-tab-wrapper">
		<a href="<?php echo admin_url('options-general.php?page='.$this->plugin_slug); ?>" class="nav-tab <?php if(empty($section)) { echo 'nav-tab-active'; } ?>">
			Konfiguration
		</a>
		<a href="<?php echo admin_url('options-general.php?page='.$this->plugin_slug.'&section=export'); ?>" class="nav-tab <?php if($section == 'export') { echo 'nav-tab-active'; } ?>">
		  Export              
		</a>
		<a href="<?php echo admin_url('options-general.php?page='.$this->plugin_slug.'&section=misc'); ?>" class="nav-tab <?php if($section == 'misc') { echo 'nav-tab-active'; } ?>">
		  Allgemein          
		</a>
		<a href="<?php echo admin_url('options-general.php?page='.$this->plugin_slug.'&section=overview'); ?>" class="nav-tab <?php if($section == 'overview') { echo 'nav-tab-active'; } ?>">
		  Übersicht       
		</a>
		<a href="<?php echo admin_url('options-general.php?page='.$this->plugin_slug.'&section=posts'); ?>" class="nav-tab <?php if($section == 'posts') { echo 'nav-tab-active'; } ?>">
		  Beiträge       
		</a>
	</h2>
       
        
	<?php switch($section){ 
	
		case "posts":
		
		global $wpdb;
		$results = $wpdb->get_results($wpdb->prepare("SELECT * , CHAR_LENGTH(`post_content`) as charlength , post_type FROM ".$wpdb->posts." WHERE post_status = 'publish' AND post_type NOT IN ('attachment','nav_menu_item','revison') HAVING charlength > '%d'",$this->requiredChars));
		
	?>
	
	<h3>Hier können Wortmarken eingefügt werden:</h3>
	
	<table class="widefat">
					<thead>
					<tr>
					<th>Titel</th>
					<th>Anzahl zeichen</th>
					<th>Type</th>
					<th>Aktion</th>
					</tr>
					</thead>
					<tfoot>
					<tr>
					<th>Titel</th>
					<th>Anzahl zeichen</th>
					<th>Type</th>
					<th>Aktion</th>
					</tr>
					</tfoot>
					<tbody>
					<?php
					
					foreach($results as $result){

						$vgwort = get_post_meta( $result->ID , $this->vgWortMeta , true );
						if(empty($vgwort)){
					
							// Just Text nothing more :)
							$clearContentCount = $this->get_char_count( $result->post_title.$result->post_content );
							if($clearContentCount > $this->requiredChars){			
								echo '<tr><td>'.$result->post_title.'</td><td>'.$clearContentCount.'</td><td>'.$result->post_type.'</td><td><a href="'.get_admin_url().'post.php?post='.$result->ID.'&action=edit" title="jetzt VG Wort einfügen">Wortmarken einfügen</a></td></tr>';

							}
						}
					}
										
					?>
					
					</tbody>
					</table>
	
		<?php 
		break;
	
		case "export":
		?>
		
			<table>
				<tr valign="top">
				<th scope="row"> <h3>VG Wort Marken Export:</h3> </th>
				<td>
					
				</td>
				</tr>
				
				<tr>
				<td>
				
				</td>
				<td>
					
					<p>Die Export Funktion dient zur Ausgabe der genutzten VG Wort Marken in eine CSV.<br /> Diese Datei kann mit einen Tabellen Programm wie Excel geöffnet werden.</p>
						<form action="<?php echo sprintf('%s?action=%s&amp;noheader=true',plugins_url( $this->plugin_name.'/export.php' , dirname(__FILE__) ),'export'); ?>" method="POST">
						<input type="hidden" name="action" value="export" />
						<input type="submit" name="export" value="exportieren" class="button-primary" />
								
					</form>
				</td>
				</tr>
			</table>
			
		<?php 
		break;
		
		case"misc":
		?>
			<table>
			<tr valign="top">
			<th scope="row"> <h3>Fehler und Bugs:</h3> </th>
			<td>
				
				<br />
				<br />
				<br />
			
				Wenn Fehler Ihr Fehler in unserem Plugin gefunden habt, dann wäre es sehr nett wenn Ihr uns diese mitteilt.<br />
				Dazu könnt Ihr auf unserer Plugin Seite kommentieren oder eine E-Mail an uns senden.
				
				<ul>
					<li>Kontakt</li>
					<li><a href="http://www.mywebcheck.de/vg-wort-plugin-wordpress/" title="MyWebcheck - Plugin Seite" target="_blank">Plugin Seite</a></li>
					<li><a href="wgwortplugin@mywebcheck.de">wgwortplugin@mywebcheck.de</li>
				</ul>
				
			</td>
			</tr>
			<tr valign="top">
			<th scope="row"> <h3>Cooles Plugin?:</h3> </th>
			<td>
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="2PJGA2XSNG8EQ">
				<input type="image" src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="Jetzt einfach, schnell und sicher online bezahlen – mit PayPal.">
				<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1">
				</form>
			</td>
			</tr>
			</table>
		
		<?php 
		break;
		
		case"overview":
		?>
			
		<?php
		
		global $wpdb;
		
		$exportDocument = "";
			$exportResult = $wpdb->get_results($wpdb->prepare('SELECT WPP.ID , WPP.post_title, WPPM.meta_value  FROM ' . $wpdb->posts . ' WPP INNER JOIN ' . $wpdb->postmeta . ' WPPM ON WPP.ID = WPPM.post_id WHERE WPPM.meta_key  = %s',$this->vgWortMeta),ARRAY_A);
			
			?>
			
			<table class="widefat">
			<thead>
			<tr>
			<th>ID</th>
			<th>Titel</th>
			<th>Url</th>
			<th>Wortmarke</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
			<th>ID</th>
			<th>Titel</th>
			<th>Url</th>
			<th>Wortmarke</th>
			</tr>
			</tfoot>
			<tbody>
			<?php
			
			foreach($exportResult as &$result){
				
				echo '<tr><td>'.$result['ID'].'</td><td>'.$result['post_title'].'</td><td>'.get_permalink($result['ID']).'</td><td>'.htmlspecialchars($result['meta_value']).'</td></tr>';

			}
			
			?>
			
			</tbody>
			</table>
			
			<?php 
		break;
		
		default:
			?>
			<form method="POST" action="">
				<h2>Einstellungen VG-Wort Plugin</h2>
				
			<table class="form-table">
			<tr valign="top">
			<th scope="row"> <h3>Konfiguration:</h3> </th>
			<td>
			
				<?php

				$types = get_post_types( array('public' => true,'show_ui' => true,'_builtin' => false) );
				$myTypes = get_option( 'wp_cpt');
				
				echo '<h4>Custom Post Types</h4>';
				echo'<p>Markierte Custom Post Types werden mit der VG Wort Funktion versehen</p>';
				
				if(count($types)>0){
				
					echo '<ul>';
					foreach($types as $type){
						if(in_array($type,$myTypes)){
							echo '<li><input checked="checked" type="checkbox" name="cpt['.$type.']"> '.$type.' </li>';
						} else {
							echo '<li><input type="checkbox" name="cpt['.$type.']"> '.$type.' </li>';
						}
					}
					
					echo '</ul>';
				}
				else {
					echo '<strong>Keine anderen Typen vorhanden!</strong>';
				}
				
				
				?>
				<br />
				<br />
				<span class="description">In Beiträge und Seiten sind immer vorhanden!</span>
				<br />
				<br />
			</td>
			</tr>
			<tr valign="top">
			<th scope="row"> <h3>MetaName:</h3> </th>
			<td>
				<input size="25" name="wp_vgwort_meta" value="<?php echo $this->vgWortMeta; ?>" /><br /><br />
				<span class="description">Dieses Feld kann genutzt werden um ein kompatible Lösung für andere Plugins zu erhalten</span>
				<ul>
					<li>Default: "wp_vgworkmarke"</li>
					<li><a href="http://maheo.eu/355-vg-wort-plugin-fuer-wordpress.php" title="VG Wort Plugin - Heiner Otterstedt" target="_blank">VG-Wort Krimskram</a>  -> "vgwpixel"</li>
				</ul>
				
			</td>
			</tr>
			<tr valign="top">
			<th scope="row"> <h3>Zeichen anzeigen:</h3> </th>
			<td>
				<?php 
				
				$showCharsInOverview = true;
				
				// Zeichen Anzeigen:
				if(isset($vgWortOptions['showChars'])) {
					if($vgWortOptions['showChars']!='') {
						$showCharsInOverview = $vgWortOptions['showChars'];
					}
				}
				
				?>
				<span class="description">Soll in den ausgewählten Beiträgen die Zeichen Anzahl angezeigt werden?</span>
				<br />
				<?php if($showCharsInOverview){  ?>
				<input type="radio" name="showchars" value="0" /> Nein<br />
				<input type="radio" name="showchars" checked="checked" value="1" /> Ja
				<br/>
				<?php } else { ?>
				<input type="radio" name="showchars" checked="checked" value="0" /> Nein<br />
				<input type="radio" name="showchars" value="1" /> Ja
				<br/>
				<?php } ?>
			</td>
			</tr>
			<tr valign="top">
			<th scope="row"> <h3>Speichern:</h3> </th>
			<td>
				<input type="hidden" name="action" value="save" />
				<input type="submit" name="save" value="Einstellung speichern" class="button-primary" / >
			</td>
			</tr>
			</table>
			
			</form>
		<?php 
		}
		?>

	</div>