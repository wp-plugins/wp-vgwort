<?php

/*
Plugin Name: WP VG WORT
Plugin URI: http://www.mywebcheck.de/wp-vgwort.zip
Description: Verwaltung der VG Wort Zählpixel
Version: 1.00
Author: Marcus Franke
Author URI: http://mywebcheck.de
*/

define(PLUGINNAME,'wp-vgwort');
define(VGWORTMETA, get_option('wp_vgwortmetaname', 'wp_vgwortmarke'));

new WP_VGWORT();

class WP_VGWORT {
      	  
	public function __construct(){
	
	// Init Methods

		add_action( 'edit_user_profile' , array( &$this , 'wpVGWortaddProfileData' ));
		add_action( 'show_user_profile' , array( &$this , 'wpVGWortaddProfileData' ));
		add_action( 'add_meta_boxes' , array( &$this , 'wpVGWortAddCustomMeta' ));
		add_action( 'save_post' , array( &$this , 'wpVGWortSavePost' ));
		add_action( 'the_content' , array( &$this , 'wpVGWortFrontendDisplay' ));
		add_action( 'manage_posts_custom_column' , array( &$this , 'wpVGWortCustomColumn' ));
		add_action( 'manage_pages_custom_column' , array( &$this , 'wpVGWortCustomColumn' ));
		add_action( 'admin_footer' ,  array( &$this , 'wpVGWortAdminFooter' ));
		add_filter( 'manage_posts_columns' , array( &$this , 'wpVGWortColumn' ));
		add_filter( 'manage_pages_columns' , array( &$this , 'wpVGWortColumn' ));
		add_action( 'admin_menu' , array( &$this , 'wpVGWortRegisterSettingsPage' ));
		
	}

	/**
	* 
	* register settingspage in wordpress
	* @param: none
	*
	*/
	
	function wpVGWortRegisterSettingsPage() {
		add_submenu_page( 'options-general.php' , 'VG WORT', 'VG WORT' , 'add_users', 'wpVGWortSettings',  array( &$this , 'wpVGWortSettingsPage' ));	
	}
	
	/**
	* 
	* Add Html for the settingspage
	* @param: none
	*
	*/
	
	function wpVGWortSettingsPage (){
		
		// REQUEST
		
		if(isset($_POST[ 'save' ])){
		
			update_option('wp_vgwortmetaname' , $_POST['wpvgwortmetaname']);
			
		}
		
		$vgWortMetaOption = get_option( 'wp_vgwortmetaname' , 'wp_vgwortmarke' );
		
		?>
		<div class="wrap">
		<?php screen_icon( 'plugins' ); ?> 
		<form method="POST" action="">
			<h2>Einstellungen VG-Wort Plugin</h2>
			
		<table class="form-table">
		<tr valign="top">
		<th scope="row"> <label for="Metaname">MetaName:</label> </th>
		<td>
			<input size="25" name="wpvgwortmetaname" value="<?php echo VGWORTMETA; ?>" /><br /><br />
			<span class="description">Dieses Feld kann genutzt werden um ein kompatible Lösung für andere Plugins zu erhalten
			<ul>
				<li>Default: "wp_vgworkmarke"</li>
				<li><a href="http://maheo.eu/355-vg-wort-plugin-fuer-wordpress.php" title="VG Wort Plugin -  Heiner Otterstedt">VG-Wort Krimskram</a>  -> "vgwpixel"</li>
				<li>...</li>
			</ul>
			</span>
		</td>
		</tr>
		<tr valign="top">
		<th scope="row"> <label for="speichern">Speichern:</label> </th>
		<td>
			<input type="submit" name="save" value="Einstellung speichern" class="button-primary" / >
		</td>
		</tr>
		</form>
		<tr valign="top">
		<th scope="row"> <label for="paypal">Cooles Plugin?:</label> </th>
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
		
		</div>
		<?php
	}
	
	/**
	* 
	* Add the value of counted Chars in the Footer of RTE
	* @param: none
	*
	*/
	
	function wpVGWortAdminFooter() {
		global $wpdb;
		global $post;

	  if(!empty( $post->post_content )) { 
		printf('<script language="javascript" type="text/javascript"> var div = document.getElementById("post-status-info"); if (div != undefined) { div.innerHTML = div.innerHTML + \'%s\'; } </script>', str_replace("'", "\'", sprintf('<span class="inside">Zeichen:'.' %d'.'</span> ', $this->getCharCount( $post->post_content ) )));
	  }
	}
	
	/**
	* 
	* Add heading in overview of posts/pages
	* @param: none
	*
	*/
	
	function wpVGWortColumn( $defaults )	{ 
		$defaults['vgwort'] = 'VG Wort';
		return $defaults;
	}
	
	/**
	* 
	* Add a custom row for displaying the WGWort status
	* @param: none
	*
	*/
	
	function wpVGWortCustomColumn( $column ) { 
		global $post;
		
		if($column == 'vgwort') { 
	  
			// VG vorhanden?
	  
			$vgwort = get_post_meta( $post->ID , VGWORTMETA , true );
		  
			if($vgwort)	{ 
				echo '<br/><span style="color:green">vorhanden</span>';
			}
			else { 
			  echo '<br/><span style="color:red">nicht vorhanden</span><br />';
			  echo sprintf('<a href="wp-admin/user-edit.php?user_id=%d">(überprüfen)</a>', $post->post_author );
			}
		}
	}
	
	/**
	* 
	* show the available posts/pages that could be used for WGWort
	* @param: object $user;
	*
	*/
	
	public function wpVGWortaddProfileData( $user ) { 
		
		global $wpdb;

		if( user_can( $user->ID , 'edit_posts' ) ) {

			?>
			<h3 id="vgwortanchor">VG Wort</h3>
			<table class="form-table">
			<tr>
			<th><label for="vgwort">bisher eingebunden Wortmarken: <?php echo $wpdb->get_var($wpdb->prepare("SELECT count(P.ID) as count FROM wp_postmeta PM INNER JOIN wp_posts P ON P.ID = PM.post_id WHERE PM.meta_key = 'wp_vgwortmarke' AND PM.meta_value != '' AND P.post_author = '%d'",$user->ID)); ?></label></th>
			<td>
				
			<?php
			$requiredChars = 1800;
		
			$results = $wpdb->get_results($wpdb->prepare("SELECT * , CHAR_LENGTH(`post_content`) as charlength FROM ".$wpdb->posts." WHERE post_status = 'publish' AND post_type IN ('post','page') AND post_author = '%d' HAVING charlength > '%d'",$user->ID,$requiredChars));

		
			if(!empty($results)) {
				?>
					<ul>
						<li><h4>Mögliche Beiträge</h4></li>
				<?php 
			
				$clearContentCount = "";
				
				foreach($results as $result){

					$vgwort = get_post_meta( $result->ID , VGWORTMETA , true );
					if(empty($vgwort)){
				
						// Just Text nothing more :)
						$clearContentCount = $this->getCharCount( $result->post_content );
						if($clearContentCount > $requiredChars){			
							echo '<li><a href="'.get_admin_url().'post.php?post='.$result->ID.'&action=edit" title="jetzt VG Wort einfügen">'.$result->post_title.' ('.$clearContentCount.' Zeichen)</a></li>';
						}
					}
				}
			}
		}
		?>
			</ul>
		<span class="description">Diesen Beiträge sollten VG Wortmarken hinzugefügt werden</span>
		</td>
		</tr>
		</table>
		
<?php }

	/**
	* 
	* Calculate the Chars of the delivered content
	* @param: string $content
	* @return int
	*
	*/

	private function getCharCount( $content ) {
		return mb_strlen(preg_replace("/\\015\\012|\\015|\\012| {2,}|\[[a-zA-Z0-9\_=\"\' \/]*\]/", "", strip_tags(html_entity_decode($result->post_title . "" . $content ))));
	}

	/**
	* 
	* append the Value of VGWORTMETA on the end of content, and will send back
	* @param: string $content
	* @return string $content
	*
	*/
	
	public function wpVGWortFrontendDisplay( $content ) {

		$vgwort = get_post_meta( $post->ID , VGWORTMETA , true );
		
		if(!empty( $vgwort )){
			$content .= $vgwort;
		}
		
		return $content;
	}

	/**
	* 
	* Adds a box to the main column on the Post and Page edit screens
	* @param: none
	*
	*/
	
	public function wpVGWortAddCustomMeta() {
		add_meta_box( 'VGWortCustomMeta', __( 'VG Wort', 'VG Wort' ), array( &$this , 'createVGWortCustomMeta' ), 'post' , 'advanced','high' );
	} 
	
	/**
	* 
	* displays the metabox in Posts and pages
	* @param: object $post
	*
	*/
	public function createVGWortCustomMeta( $post ) {

		// Use nonce for verification
		wp_nonce_field( plugin_basename(__FILE__) , PLUGINNAME );

		// The actual fields for data entry
		
		echo '<input type="input" size="150" name="wp_vgwortmarke" value="'.get_post_meta( $post->ID , VGWORTMETA , true ).'" /><br />';
		echo '<a href="http://www.vgwort.de/" target="_blank">VG WORT Marke erstellen</a>';

	}

	/**
	* 
	* save the values of VGWort Meta
	* @param: int $post_id
	*
	*/
	
	function wpVGWortSavePost( $post_id ) {

		
		// Erweiterung bei Einstellungen 
		$available_post = array( 'page' , 'post' );
	
		// AutoSave Methode
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){ 
			return;
		}
		
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times

		if ( !wp_verify_nonce( $_POST[PLUGINNAME], plugin_basename( __FILE__ ) ) )
		return;

		// Check permissions

		if ( in_array($_POST['post_type'],$available_post)) {
			if ( !current_user_can( 'edit_page', $post_id ) )
				return;
		}
		else{
			return;
		}

		// vars übergeben

		if(!empty($_POST['wp_vgwortmarke'])){
			update_post_meta($post_id , VGWORTMETA , $_POST['wp_vgwortmarke'] );
		}else{
			delete_post_meta($post_id , VGWORTMETA , $_POST['wp_vgwortmarke'] );
		}
	}
}	

?>