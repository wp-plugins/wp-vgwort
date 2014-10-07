<?php
/**
 * WP VG Wort.
 *
 * @package   WP_VGWORT
 * @author    Marcus Franke <wgwortplugin@mywebcheck.de>, Ronny Harbich
 * @license   GPL-2.0+
 * @link      http://vgw-plugin.de
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
	protected $version = '2.1.6';

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

	protected $frontendDisplayFilterPriority = 1800;


	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Loads the plugin text domain for translation
		add_action( 'init', array( $this, 'wp_vgwort_load_plugin_textdomain' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		$this->vgWortMeta = get_option( 'wp_vgwortmetaname', 'wp_vgwortmarke' );
		if(empty($this->vgWortMeta)){
			$this->vgWortMeta = 'wp_vgwortmarke';
		}
			
		add_action( 'edit_user_profile', array( &$this, 'add_profile_data' ) );
		add_action( 'show_user_profile', array( &$this, 'add_profile_data' ) );

		add_action( 'personal_options_update', array( $this, 'save_extra_user_profile_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_extra_user_profile_fields' ) );

		add_action( 'admin_footer', array( $this, 'admin_footer' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_custom_meta' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_action( 'wp_footer', array( $this, 'display_marker' ), $this->frontendDisplayFilterPriority );

		add_filter( 'manage_posts_columns', array( $this, 'column' ) );
		add_filter( 'manage_pages_columns', array( $this, 'column' ) );

		add_action( 'manage_posts_custom_column', array( $this, 'custom_column' ) );
		add_action( 'manage_pages_custom_column', array( $this, 'custom_column' ) );

		add_action( 'admin_notices', array( $this, 'privacy_notification' ) );
		


		
	}

	
	/**
	 * Admin notification of privacy
	 *
	 * @since 2.1.3
	 */
	
	function privacy_notification() {
		
		$datenschutz = "";
		$vgWortOptions = get_option( 'wp_vgwort_options' );
		
		if ( !isset( $vgWortOptions['datenschutz'] ) OR empty($vgWortOptions['datenschutz']) ) {
			?>
				<div class="error">
					<p><b><?php _e( 'Bitte Datenschutzhinweis erweitern. Mehr unter <a href="' . admin_url('/options-general.php?page=wp-vgwort&amp;section=datenschutz') . '">Datenschutz</a> im VGW Plugin', 'wp-vgwort-locale' ); ?></b></p>
				</div>
			<?php
		}
		
	}
	
	
	/**
	 * Loads textdomain to translate
	 *
	 * @since 2.0.2
	 */
	public function wp_vgwort_load_plugin_textdomain() {

		load_plugin_textdomain( 'wp-vgwort-locale', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

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
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		$this->plugin_screen_hook_suffix = add_submenu_page(
			'options-general.php',
			__( 'VGW Plugin', 'wp-vgwort-locale' ),
			__( 'VGW Plugin', 'wp-vgwort-locale' ),
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
	 * Add the value of counted Chars in the Footer of RTE.
	 *
	 * @since    1.0.0
	 */
	public function admin_footer() {
		global $post;

		if ( !empty( $post->post_content ) ) {
			$charCount = $this->get_char_count( $post->post_title . $post->post_content );
			$missingCharCount = $this->requiredChars - $charCount;

			printf( '<script language="javascript" type="text/javascript"> var div = document.getElementById("wp-word-count"); if (div != undefined) { div.innerHTML = div.innerHTML + \'%s\'; } </script>',
				str_replace( "'", '\'', sprintf( '<span class="inside"> / Zeichen:  %d (nötig: %s)</span> ', $charCount, $missingCharCount > 0 ? $missingCharCount : 'keine' ) )
			);
		}
	}

	/**
	 *
	 * Add heading in overview of posts/pages.
	 *
	 * @param $defaults
	 *
	 */
	function column( $defaults ) {

		$vgWortOptions = get_option( 'wp_vgwort_options' );

		if ( !( isset( $vgWortOptions['showChars'] ) && !$vgWortOptions['showChars'] ) ) {

			$currentPostType = get_post_type();
			$allowedTypes = get_option( 'wp_cpt', array( 'post', 'page' ) );
			if ( in_array( $currentPostType, $allowedTypes ) ) {
				$defaults['vgwort-mark'] = __( 'Zählmarke', 'wp-vgwort-locale' );
				$defaults['vgwort-char-count'] = __( 'Zeichenanzahl', 'wp-vgwort-locale' );
			}

		}
		return $defaults;
	}

	/**
	 *
	 * Add a custom row for displaying the VG WORT status.
	 *
	 * @param string $column
	 *
	 */
	function custom_column( $column ) {
		global $post;

		if ( $column == 'vgwort-mark' || $column == 'vgwort-char-count' ) {

			// is in Config?
			$vgWortOptions = get_option( 'wp_vgwort_options' );

			// Zeichen Anzeigen:
			if ( !( isset( $vgWortOptions['showChars'] ) && !$vgWortOptions['showChars'] ) ) {

				$charCount = $this->get_char_count( $post->post_title . $post->post_content );

				if ( $column == 'vgwort-mark' ) {

					if ( $charCount > $this->requiredChars ) {

						// VG vorhanden?
						$vgwort = get_post_meta( $post->ID, $this->vgWortMeta, true );

						if ( $vgwort ) {
							echo( sprintf( '<span style="font-style: italic">%s</span>', __( 'vorhanden', 'wp-vgwort-locale' ) ) );
						}
						else {
							echo( sprintf( '<span style="font-weight:bold">%s</span>', __( 'nicht hinzugefügt', 'wp-vgwort-locale' ) ) );
						}
					}
					else {
						echo( sprintf( '<span>%s</span>', __( 'zu wenig Zeichen', 'wp-vgwort-locale' ) ) );
					}
				}
				elseif ( $column == 'vgwort-char-count' ) {
					if ( $charCount > $this->requiredChars ) {
						// output number of chars in post
						echo( $charCount );
					}
					else {
						// output number of missing chars in post
						echo( sprintf( __( '%s nötig' ), $this->requiredChars - $charCount ) );
					}
				}
			}
		}
	}

	/**
	 *
	 * Show the available posts/pages that could be used for WGWort.
	 *
	 * @param object $user
	 *
	 */
	public function add_profile_data( $user ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		if ( user_can( $user->ID, 'edit_posts' ) ) {
			?>
			<h3 id="vgwortanchor"><?php _e( 'VG Wort Zählmarken', 'wp-vgwort-locale' ); ?></h3>
			<table class="form-table">
			<tr>
			<th>
						<label for="vgwort"><?php _e( 'Beiträge/Seiten ohne Zählmarke', 'wp-vgwort-locale' ); ?>: <?php echo( $wpdb->get_var( $wpdb->prepare( "SELECT count(P.ID) as count FROM wp_postmeta PM INNER JOIN wp_posts P ON P.ID = PM.post_id WHERE PM.meta_key = 'wp_vgwortmarke' AND PM.meta_value != '' AND P.post_author = '%d'", $user->ID ) ) ); ?></label>
					</th>
			<td>
						<?php
			$currentFilter = get_user_meta( $user->ID, 'wp-wort-filter', true );

			if ( empty( $currentFilter ) || $currentFilter == 'all' ) {
				$currentFilter = 'all';
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT * , CHAR_LENGTH(`post_content`) as charlength , post_type FROM " . $wpdb->posts . " WHERE post_status = 'publish' AND post_type NOT IN ('attachment','nav_menu_item','revision') AND post_author = '%d' HAVING charlength > '%d'", $user->ID, $this->requiredChars ) );
			}
			else {
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT * , CHAR_LENGTH(`post_content`) as charlength , post_type FROM " . $wpdb->posts . " WHERE post_status = 'publish' AND post_type = %s AND post_author = '%d' HAVING charlength > '%d'", $currentFilter, $user->ID, $this->requiredChars ) );
			}

			$postTypes = $wpdb->get_results( "SELECT post_type  FROM " . $wpdb->posts . " WHERE post_type NOT IN ('attachment','nav_menu_item','revision') group by post_type  ORDER BY FIELD(post_type,'post','page') DESC " );

			echo( __( 'Nach Seiten-Typ filtern', 'wp-vgwort-locale' ) . ': <select name="wpvgwortcurrentposttype" size="1"><option value="all">' . __( 'Alle', 'wp-vgwort-locale' ) . '</option>' );

			foreach ( $postTypes as $postType ) {
				if ( $postType->post_type != $currentFilter ) {
					echo( '<option value="' . $postType->post_type . '">' . $postType->post_type . '</option>' );
				}
				else {
					echo( '<option selected="selected" value="' . $postType->post_type . '">' . $postType->post_type . '</option>' );
				}
			}
			echo( '</select> <input type="submit" class="button" name="Sender" value="Filtern" />' );

			if ( !empty( $results ) ) {
				?>
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
										<th><?php _e( 'Aktion', 'wp-vgwort-locale' ); ?></th>
									</tr>
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
												echo( '<a href="' . get_admin_url() . 'post.php?post=' . $result->ID . '&action=edit" title="' . __( 'Beitrag/Seite bearbeiten', 'wp-vgwort-locale' ) . '">' );
												echo( __( 'Zählmarke einfügen', 'wp-vgwort-locale' ) );
												echo( '</a>' );
												echo( '</td>' );
												echo( '</tr>' );
											}
										}
									} ?>
								</tbody>
							</table>
			<?php
			}
		} ?>
		<span class="description"><?php _e( 'Diesen Beiträgen/Seiten können Sie Zählmarken hinzufügen.', 'wp-vgwort-locale' ); ?></span>
		</td>
		</tr>
		</table>
	<?php
	}

	/**
	 *
	 * Save the current FilterOption.
	 *
	 * @param int $user_id
	 *
	 */
	public function save_extra_user_profile_fields( $user_id ) {

		if ( !current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		update_user_meta( $user_id, 'wp-wort-filter', $_POST['wpvgwortcurrentposttype'] );
	}

	/**
	 *
	 * Adds a box to the main column on the Post and Page edit screens.
	 *
	 */
	public function add_custom_meta() {

		$currentPostType = get_post_type();
		$allowedTypes = get_option( 'wp_cpt', array( 'post', 'page' ) );

		if ( in_array( $currentPostType, $allowedTypes ) ) {
			add_meta_box( 'CustomMeta', __( 'Zählmarke für VG WORT', 'wp-vgwort-locale' ), array( &$this, 'create_custom_meta' ), $currentPostType, 'advanced', 'high' );
		}
	}

	/**
	 *
	 * Displays the metabox in Posts and pages.
	 *
	 * @param object $post
	 *
	 */
	public function create_custom_meta( $post ) {

		// Use nonce for verification
		wp_nonce_field( plugin_basename( __FILE__ ), $this->plugin_slug );

		// The actual fields for data entry
		$marke = get_post_meta( $post->ID, $this->vgWortMeta, true );

		print_r($marke);
		
		if ( !empty( $marke ) ) {

			echo( sprintf( '<p><strong>%s</strong>: %s</p>', __( 'Vorhandene Zählmarke', 'wp-vgwort-locale' ), htmlspecialchars( $marke ) ) );
			echo( '<input type="hidden" name="markein" value="1" />' );
		} ?>
		<label for="wp_vgwortmarke"><?php _e( 'Zählmarke:', 'wp-vgwort-locale' ) ?></label>
		<input type="text" size="16" name="wp_vgwortmarke" id="wp_vgwortmarke" value="" class="form-input-tip"/>
		<input type="submit" class="button button-primary" name="sender" id="wp_vgwort_send_marker" value="<?php _e( 'Speichern', 'wp-vgwort-locale' ); ?>"/>
		<input type="submit" class="button button-small" name="delete" id="wp_vgwort_delete_marker" value="<?php _e( 'Löschen', 'wp-vgwort-locale' ); ?>"/>
		<p>
		<a href="http://www.vgwort.de/" target="_blank" title="<?php _e( 'VG WORT Marke erstellen', 'wp-vgwort-locale' ); ?>"><?php _e( 'Zählmarken bei VG WORT erhalten', 'wp-vgwort-locale' ); ?></a>
		</p>
	<?php
	}

	/**
	 *
	 * Save the values of VG Wort Meta.
	 *
	 * @param: int $post_id
	 *
	 */
	function save_post( $post_id ) {

		// Erweiterung bei Einstellungen
		$allowedTypes = get_option( 'wp_cpt', array( 'post', 'page' ) );

		// AutoSave Methode
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( !isset( $_POST[$this->plugin_slug] ) || !wp_verify_nonce( $_POST[$this->plugin_slug], plugin_basename( __FILE__ ) ) )
			return;

		// Check permissions
		if ( in_array( esc_attr( $_POST['post_type'] ), $allowedTypes ) ) {
			if ( !current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
		}
		else {
			return;
		}

		if ( !isset( $_POST['delete'] ) ) {
			// New/Update

			// TODO: Auskommentiert, da irgendwie nicht verwendet.
			//$markeIn = sanitize_text_field( $_POST['markein'] );
			$vgWortMarke = $_POST['wp_vgwortmarke'];

			if ( !empty( $_POST['wp_vgwortmarke'] ) ) {
				update_post_meta( $post_id, $this->vgWortMeta, $vgWortMarke );
			}
		}
		else {
			// Delete
			delete_post_meta( $post_id, $this->vgWortMeta );
		}
	}

	/**
	 *
	 * Calculate the Chars of the delivered content.
	 *
	 * @param: string $content
	 *
	 * @return int
	 *
	 */
	private function get_char_count( $content ) {
		return mb_strlen( preg_replace( "/\\015\\012|\\015|\\012| {2,}|\[[a-zA-Z0-9\_=\"\'\. \/]*\]/", "", strip_tags( html_entity_decode( $content ) ) ) );
	}

	/**
	 * Append the VG WORT marker to wp_footer.
	 *
	 * @param string $content
	 *
	 */
	public function display_marker( $content ) {
		echo( $this->get_marker() );
	}

	/**
	 * It is possible to filter the output:
	 * <code>
	 * add_filter( 'wp_vgwort_frontend_display', 'my_frontend_display_filter' );
	 * function my_frontend_display_filter( $vgwort ) {
	 *  return ('VG-Wort-Marke: ' . $vgwort);
	 * }
	 * </code>
	 *
	 * @return string The VG WORT marker.
	 */
	public function get_marker() {
		global $post;

		$vgwort = get_post_meta( $post->ID, $this->vgWortMeta, true );

		if ( is_single() || is_page() ) {
			if ( !empty( $vgwort ) ) {
				return apply_filters( 'wp_vgwort_frontend_display', $vgwort );
			}
		}

		return '';
	}

	public function remove_display_marker() {
		remove_action( 'wp_footer', array( $this, 'display_marker' ), $this->frontendDisplayFilterPriority );
	}

	public function get_vg_wort_meta() {
		return $this->vgWortMeta;
	}

}