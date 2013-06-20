<?php
/**
 * WP VG Wort.
 *
 * @package   PluginName
 * @author    Marcus Franke <wgwortplugin@mywebcheck.de>
 * @license   GPL-2.0+
 * @link      http://mywebcheck.de
 * @copyright 2013 MyWebcheck
 *
 */

/**
 * Plugin class.
 * @package WP_VGWORT
 */
class WP_VGWORT {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $version = '2.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    2.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'wp-vgwort';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	
	/**
	 * Name of VG Wort Meta 
	 *
	 * @since     1.0.0
	 */
	protected $vgWortMeta = '';
	
	/**
	 * Count of required Chars
	 *
	 * @since     1.0.0
	 */
	protected $requiredChars = 1800;
	
	
	
	
	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		$this->vgWortMeta = get_option('wp_vgwortmetaname', 'wp_vgwortmarke');
		
		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'edit_user_profile' , array( &$this , 'add_profile_data' ));
		add_action( 'show_user_profile' , array( &$this , 'add_profile_data' ));
		
		add_action( 'personal_options_update', array( $this,'save_extra_user_profile_fields' ));
		add_action( 'edit_user_profile_update', array( $this,'save_extra_user_profile_fields' ));
		
		add_action( 'admin_footer' ,  array( $this , 'admin_footer' ));
		
		add_action( 'add_meta_boxes' , array( $this , 'add_custom_meta' ));
		add_action( 'save_post' , array( $this , 'save_post' ));
		add_action( 'the_content' , array( $this , 'frontend_display' ));
		
		
		add_filter( 'manage_posts_columns' , array( $this , 'column' ));
		add_filter( 'manage_pages_columns' , array( $this , 'column' ));
		
		add_action( 'manage_posts_custom_column' , array( $this , 'custom_column' ));
		add_action( 'manage_pages_custom_column' , array( $this , 'custom_column' ));
		
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), $this->version );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $screen->id == $this->plugin_screen_hook_suffix ) {
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), $this->version );
		}

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'css/public.css', __FILE__ ), $this->version );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'js/public.js', __FILE__ ), array( 'jquery' ), $this->version );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		$this->plugin_screen_hook_suffix = add_submenu_page(
			'options-general.php' ,
			'VG WORT',
			'VG WORT',
			'add_users',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add the value of counted Chars in the Footer of RTE
	 * @param: none
	 *
	 * @since    1.0.0
	 */
	public function admin_footer() {
		global $post;

		if(!empty( $post->post_content )) { 
		printf('<script language="javascript" type="text/javascript"> var div = document.getElementById("wp-word-count"); if (div != undefined) { div.innerHTML = div.innerHTML + \'%s\'; } </script>', str_replace("'", "\'", sprintf('<span class="inside"> / Zeichen:'.' %d'.'</span> ', $this->get_char_count( $post->post_title.$post->post_content ) )));
		}
	}
	
	
	/**
	* 
	* Add heading in overview of posts/pages
	* @param: none
	*
	*/
	
	function column( $defaults )	{ 
			
		$vgWortOptions = get_option('wp_vgwort_options');
			
		if(!(isset($vgWortOptions['showChars']) AND !$vgWortOptions['showChars'])) {
				
			$currentPostType = get_post_type();
			$allowedTypes = get_option( 'wp_cpt',array('post','page'));
			if(in_array($currentPostType,$allowedTypes)){
				$defaults['vgwort'] = 'VG Wort';
			}
			
		}
		return $defaults;
	}
	
	/**
	* 
	* Add a custom row for displaying the WGWort status
	* @param: none
	*
	*/
	
	function custom_column( $column ) { 
		global $post;

		if($column == 'vgwort') { 
	  
			// is in Config?
			
			$vgWortOptions = get_option('wp_vgwort_options');
							
			// Zeichen Anzeigen:
			if(!(isset($vgWortOptions['showChars']) AND !$vgWortOptions['showChars'])) {
			
				$charCount = $this->get_char_count( $post->post_title.$post->post_content );
				
				if($charCount > $this->requiredChars ){
				
					// VG vorhanden?
			  
					$vgwort = get_post_meta( $post->ID , $this->vgWortMeta , true );
				  
					if($vgwort)	{ 
						echo '<span style="color:green"> '.$charCount.' Zeichen - vorhanden</span>';
					}
					else { 
					  echo '<span style="color:red"> '.$charCount.' Zeichen - nicht vorhanden</span>';
					}
				}else{
					echo '<span style="color:blue"> '.$charCount.' Zeichen - Limit nicht erreicht</span>';
				}
			}	
		}
	}	
	
	/**
	* 
	* show the available posts/pages that could be used for WGWort
	* @param: object $user;
	*
	*/
	
	public function add_profile_data( $user ) { 
		
		global $wpdb;

		if( user_can( $user->ID , 'edit_posts' ) ) {

			?>
			<h3 id="vgwortanchor">VG Wort</h3>
			<table class="form-table">
			<tr>
			<th><label for="vgwort">bisher eingebunden Wortmarken: <?php echo $wpdb->get_var($wpdb->prepare("SELECT count(P.ID) as count FROM wp_postmeta PM INNER JOIN wp_posts P ON P.ID = PM.post_id WHERE PM.meta_key = 'wp_vgwortmarke' AND PM.meta_value != '' AND P.post_author = '%d'",$user->ID)); ?></label></th>
			<td>
				
			<?php
			
			$currentFilter = get_user_meta( $user->ID, 'wp-wort-filter', true);
			
			if(empty($currentFilter) OR $currentFilter == 'all'){
				$currentFilter = "all";
				$results = $wpdb->get_results($wpdb->prepare("SELECT * , CHAR_LENGTH(`post_content`) as charlength , post_type FROM ".$wpdb->posts." WHERE post_status = 'publish' AND post_type NOT IN ('attachment','nav_menu_item','revision') AND post_author = '%d' HAVING charlength > '%d'",$user->ID,$this->requiredChars));
			}else{
				$results = $wpdb->get_results($wpdb->prepare("SELECT * , CHAR_LENGTH(`post_content`) as charlength , post_type FROM ".$wpdb->posts." WHERE post_status = 'publish' AND post_type = %s AND post_author = '%d' HAVING charlength > '%d'",$currentFilter,$user->ID,$this->requiredChars));
			}

			$postTypes = $wpdb->get_results("SELECT post_type  FROM ".$wpdb->posts." WHERE post_type NOT IN ('attachment','nav_menu_item','revision') group by post_type  ORDER BY FIELD(post_type,'post','page') DESC ");
		
			echo 'Filtern nach Posttype:<select name="wpvgwortcurrentposttype" size="1"><option value="all">Alle</option>';
		
			foreach($postTypes as $postType) {
			
				if($postType->post_type != $currentFilter){
					echo '<option value="'.$postType->post_type.'">'.$postType->post_type.'</option>';
				}else{
					echo '<option selected="selected" value="'.$postType->post_type.'">'.$postType->post_type.'</option>';
				}
			
				
			}
			echo '</select><input type="submit" name="Sender" value="filtern" />';
			
			if(!empty($results)) {
			
			?>	
				<h4>Mögliche Beiträge</h4>
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
			
			}
		}
		?>
			
		<span class="description">Diesen Beiträge sollten VG Wortmarken hinzugefügt werden</span>
		</td>
		</tr>
		</table>
		
<?php }
	
	/**
	* 
	* Save the current FilterOption
	* @param: int $user_id
	* @return int
	*
	*/

	public function save_extra_user_profile_fields( $user_id ) {
 
		if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
		 
		update_user_meta( $user_id, 'wp-wort-filter', $_POST['wpvgwortcurrentposttype'] );
		
	}
	
	/**
	* 
	* Adds a box to the main column on the Post and Page edit screens
	* @param: none
	*
	*/
	
	public function add_custom_meta() {
		$currentPostType = get_post_type();
		$allowedTypes = get_option( 'wp_cpt',array('post','page'));
		if(in_array($currentPostType,$allowedTypes)){
			add_meta_box( 'CustomMeta', __( 'VG Wort', 'VG Wort' ), array( &$this , 'create_custom_meta' ), $currentPostType , 'advanced','high' );
		}
	} 	
	
/**
	* 
	* displays the metabox in Posts and pages
	* @param: object $post
	*
	*/
	public function create_custom_meta( $post ) {

		// Use nonce for verification
		wp_nonce_field( plugin_basename(__FILE__) , $this->plugin_slug );

		// The actual fields for data entry
		$marke = get_post_meta( $post->ID , $this->vgWortMeta , true );
		
		if(!empty($marke))
		{
			echo '<strong>Vorhandene Zählmarke</strong>: '.htmlspecialchars($marke);
			echo '<input type="hidden" name="markein" value="1" />';
		}
		
		echo '<input type="input" size="150" name="wp_vgwortmarke" value="" />';
		echo '<input type="submit" name="Sender" value="speichern" /><br />';
		echo '<a href="http://www.vgwort.de/" target="_blank">VG WORT Marke erstellen</a>';

	}
	
/**
	* 
	* save the values of VGWort Meta
	* @param: int $post_id
	*
	*/
	
	function save_post( $post_id ) {
	
		// Erweiterung bei Einstellungen 
		$allowedTypes = get_option( 'wp_cpt',array('post','page'));
	
		// AutoSave Methode
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ){ 
			return;
		}
		
		if ( $post_object->post_type == 'revision' )
        return;
		
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		
		if ( !wp_verify_nonce( $_POST[$this->plugin_slug], plugin_basename( __FILE__ ) ) )
		return;

		// Check permissions

		if ( in_array(esc_attr($_POST['post_type']),$allowedTypes)) {
			if ( !current_user_can( 'edit_page', $post_id ) ) {
				return;
			}	
			
		}
		else{
			
			return;
		}
		
		// vars übergeben
	
		$markeIn = sanitize_text_field($_POST['markein']);
		$vgWortMarke = $_POST['wp_vgwortmarke'];
		
		if(!isset($markeIn)){
		
			if(!empty($vgWortMarke)){
				update_post_meta($post_id , $this->vgWortMeta , $vgWortMarke );
			}else{
				delete_post_meta($post_id , $this->vgWortMeta , $vgWortMarke);
			}
		} else {

			if(!empty($_POST['wp_vgwortmarke'])){
				update_post_meta($post_id , $this->vgWortMeta , $vgWortMarke );
			}
		}
	
	}
	
		/**
	* 
	* Calculate the Chars of the delivered content
	* @param: string $content
	* @return int
	*
	*/

	private function get_char_count( $content ) {
		return mb_strlen(preg_replace("/\\015\\012|\\015|\\012| {2,}|\[[a-zA-Z0-9\_=\"\'\. \/]*\]/", "", strip_tags(html_entity_decode($content ))));
	}
		
/**
	* 
	* append the Value of $this->vgWortMeta on the end of content
	* just insert $this->vgWortMeta on page.php and single.php
	* @param: string $content
	* @return string $content
	*
	*/
	
	public function frontend_display( $content ) {

		global $post; 
	
		$vgwort = get_post_meta( $post->ID , $this->vgWortMeta , true );
		
		if(is_single() OR is_page()){
			if(!empty( $vgwort )){
				$content .= $vgwort;
			}
		}
	
		return $content;
	}
	
	public function get_vg_wort_meta(){
		return $this->vgWortMeta;
	}

}