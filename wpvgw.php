<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */



class WPVGW {

	
	private $options;

	
	private $versionOptionName;
	
	private $optionsName;

	
	private $tableSlug;

	
	private $markersTableName;
	
	private $postsExtrasTableName;

	
	private $adminViewsManager = null;
	
	private $postView = null;

	
	private $postTableView = null;

	
	private $markersManager;
	
	private $postsExtras;

	
	private static $instance = null;


	
	private $frontendDisplayFilterPriority = 1800;


	
	public static function get_instance() {
		
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	
	private function __construct() {
		
		$this->versionOptionName = WPVGW . '_version';
		$this->optionsName = WPVGW . '_options_v1';
		$this->tableSlug = WPVGW;

		
		add_action( 'init', array( $this, 'init' ) );

		
		register_activation_hook( WPVGW_PLUGIN_PATH . 'wp-vgwort.php', array( $this, 'on_activation' ) );
		
		register_deactivation_hook( WPVGW_PLUGIN_PATH . 'wp-vgwort.php', array( $this, 'on_deactivation' ) );
	}

	
	public function init() {
		
		

		
		load_plugin_textdomain( WPVGW_TEXT_DOMAIN, false, WPVGW_PLUGIN_PATH_RELATIVE . '/languages' );

		
		add_action( 'wp_loaded', array( $this, 'on_wordpress_loaded' ) );

		
		if ( is_admin() ) {
			
			add_action( 'admin_init', array( $this, 'on_admin_init' ) );
			
			add_action( 'admin_notices', array( $this, 'on_admin_notice' ) );
			
			add_action( 'current_screen', array( $this, 'on_current_screen' ) );
			
			add_action( 'admin_enqueue_scripts', array( $this, 'on_enqueue_admin_css_and_scripts' ) );
			
			add_action( 'admin_menu', array( $this, 'on_add_plugin_admin_menu' ) );

			
			add_action( 'save_post', array( $this, 'on_post_saved' ) );
			
			add_action( 'delete_post', array( $this, 'on_post_deleted' ) );

			
			
			
			
			
			
			

			
			add_action( 'shutdown', array( $this, 'on_deinit' ) );

			
			
			add_filter( 'plugin_action_links_' . WPVGW_PLUGIN_NAME, array( $this, 'on_plugin_action_links' ), 10, 4 );

			
			
		}
		else {
			
			add_action( 'wp_footer', array( $this, 'on_display_marker' ), $this->frontendDisplayFilterPriority );
		}
	}


	
	private function create_markers_table_name() {
		
		global $wpdb;

		return $wpdb->prefix . $this->tableSlug . '_markers';
	}

	
	private function create_posts_extras_table_name() {
		
		global $wpdb;

		return $wpdb->prefix . $this->tableSlug . '_posts_extras';
	}

	

	

	

	


	
	public function on_wordpress_loaded() {
		
		$this->markersTableName = $this->create_markers_table_name();
		$this->postsExtrasTableName = $this->create_posts_extras_table_name();

		
		$this->options = WPVGW_Options::get_instance();
		$this->options->init( $this->optionsName );

		
		$this->markersManager = new WPVGW_MarkersManager(
			$this->markersTableName,
			$this->options->get_allowed_user_roles(),
			$this->options->get_allowed_post_types(),
			$this->options->get_removed_post_types(),
			$this->options->get_do_shortcodes_for_character_count_calculation()
		);

		
		$this->postsExtras = new WPVGW_PostsExtras( $this->postsExtrasTableName, $this->markersManager );


		
		if ( is_admin() ) {
			
			$this->adminViewsManager = new WPVGW_AdminViewsManger( $this->markersManager, $this->postsExtras, $this->options );
			$this->postView = new WPVGW_PostView( $this->markersManager, $this->options );
			$this->postTableView = new WPVGW_PostTableView( $this->markersManager, $this->postsExtras, $this->options );
		}
	}


	
	public function on_deinit() {
		if ( $this->options !== null ) {
			
			$this->options->set_allowed_post_types( $this->markersManager->get_allowed_post_types() );
			$this->options->set_removed_post_types( $this->markersManager->get_removed_post_types() );

			
			$this->options->store_in_db();
		}
	}

	
	public function on_admin_init() {
		$this->upgrade_plugin();
	}

	
	private function install_plugin() {
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		
		$sql = "CREATE TABLE IF NOT EXISTS $this->markersTableName (
					id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					post_id bigint(20) unsigned DEFAULT NULL,
					user_id bigint(20) unsigned DEFAULT NULL,
					public_marker varchar(255) NOT NULL,
					private_marker varchar(255) DEFAULT NULL,
					server varchar(255) NOT NULL,
					is_marker_disabled tinyint(1) unsigned NOT NULL DEFAULT '0',
					is_post_deleted tinyint(1) unsigned NOT NULL DEFAULT '0',
					deleted_post_title text DEFAULT NULL,
					PRIMARY KEY (id),
					UNIQUE KEY public_marker (public_marker),
					UNIQUE KEY post_id (post_id),
					UNIQUE KEY private_marker (private_marker),
					KEY user_id (user_id)
				);";
		dbDelta( $sql );

		
		$sql = "CREATE TABLE IF NOT EXISTS $this->postsExtrasTableName (
					post_id bigint(20) unsigned NOT NULL,
					character_count bigint(20) unsigned NOT NULL,
					PRIMARY KEY (post_id),
					KEY character_count (character_count)
				);";
		dbDelta( $sql );
	}

	
	private function uninstall_plugin() {
		
		global $wpdb;

		
		$markersTableName = $this->create_markers_table_name();
		$postsExtrasTableName = $this->create_posts_extras_table_name();

		
		$wpdb->query( "DROP TABLE {$markersTableName}" );
		$wpdb->query( "DROP TABLE {$postsExtrasTableName}" );

		
		delete_option( $this->versionOptionName );
		delete_option( $this->optionsName );
	}

	
	private function upgrade_plugin() {
		
		$oldVersion = get_option( $this->versionOptionName, null );

		
		if ( $oldVersion === WPVGW_VERSION )
			return;


		
		if ( $oldVersion === null ) {

			
			$isVersion100 = (
				get_option( 'wp_cpt', false ) !== false ||
				get_option( 'wp_vgwort_options', false ) !== false ||
				get_option( 'wp_vgwortmetaname', false ) !== false
			);

			if ( $isVersion100 ) {
				
				$oldVersion = '1.0.0';
			}
			else {
				
				$this->install_plugin();

				
				$oldVersion = WPVGW_VERSION;
			}
		}


		
		global $wpdb;

		
		if ( version_compare( $oldVersion, '1.0.0', '<=' ) ) {
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$markersTableName = $wpdb->prefix . 'wpvgw_markers';
			
			$sql = "CREATE TABLE IF NOT EXISTS $markersTableName (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						post_id bigint(20) unsigned DEFAULT NULL,
						user_id bigint(20) unsigned DEFAULT NULL,
						public_marker varchar(255) NOT NULL,
						private_marker varchar(255) DEFAULT NULL,
						server varchar(255) NOT NULL,
						is_marker_disabled tinyint(1) unsigned NOT NULL DEFAULT '0',
						is_post_deleted tinyint(1) unsigned NOT NULL DEFAULT '0',
						deleted_post_title text DEFAULT NULL,
						PRIMARY KEY (id),
						UNIQUE KEY public_marker (public_marker),
						UNIQUE KEY post_id (post_id),
						UNIQUE KEY private_marker (private_marker),
						KEY user_id (user_id)
					);";
			dbDelta( $sql );

			$postsExtrasTableName = $wpdb->prefix . 'wpvgw_posts_extras';
			
			$sql = "CREATE TABLE IF NOT EXISTS $postsExtrasTableName (
						post_id bigint(20) unsigned NOT NULL,
						character_count bigint(20) unsigned NOT NULL,
						PRIMARY KEY (post_id),
						KEY character_count (character_count)
					);";
			dbDelta( $sql );

			
			$oldAllowedPostTypes = get_option( 'wp_cpt', array() );
			
			$this->markersManager->set_allowed_post_types( array_unique( array_merge( $oldAllowedPostTypes, array( 'post', 'page' ) ) ) );

			
			$metaName = get_option( 'wp_vgwortmetaname', 'wp_vgwortmarke' );
			$this->options->set_meta_name( $metaName == '' ? 'wp_vgwortmarke' : $metaName );


			
			
			
			

			
			$this->options->set_operations_post_character_count_recalculations_necessary( true );

			
			$this->options->set_operation_old_plugin_import_necessary( true );
		}


		
		if ( version_compare( $oldVersion, '3.1.1', '<' ) ) {
			
			
			try {
				$this->options->set_meta_name( $this->options->get_meta_name() );
			} catch ( Exception $e ) {
				
				$this->options->set_meta_name( 'wp_vgwortmarke' );
			}
		}


		
		if ( version_compare( $oldVersion, '3.4.5', '<' ) ) {
			
			if ( $this->options->get_import_from_post_regex() == '%<img.*?src\s*=\s*"http://vg[0-9]+\.met\.vgwort.de/na/[a-z0-9]+".*?>%si' )
				$this->options->set_import_from_post_regex( '%<img\s[^<>]*?src\s*=\s*"http://vg[0-9]+\.met\.vgwort\.de/na/[a-z0-9]+"[^<>]*?>%im' );
		}


		
		


		
		update_option( $this->versionOptionName, WPVGW_VERSION );
	}

	
	public function on_activation( $sitewide ) {
		
		if ( !current_user_can( 'activate_plugins' ) )
			return;

		
		$options = WPVGW_Options::get_instance();
		$options->init( $this->optionsName );

		
		$options->set_operations_post_character_count_recalculations_necessary( true );

		
		$options->store_in_db();
	}

	
	public function on_deactivation( $sitewide ) {
		
		if ( !current_user_can( 'activate_plugins' ) )
			return;

		
		if ( $sitewide ) {
			
			
		}
		
		else {
			
			if ( get_option( $this->versionOptionName, null ) !== null && $this->options->get_remove_data_on_uninstall() )
				$this->uninstall_plugin();
		}

	}

	
	public function on_plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
		
		$actions[] = sprintf( '<a href="%s">%s</a>',
			esc_attr( WPVGW_AdminViewsManger::create_admin_view_url() ),
			__( 'Einstellungen', WPVGW_TEXT_DOMAIN )
		);

		return $actions;
	}

	
	public function on_admin_notice() {
		
		if ( !current_user_can( 'manage_options' ) )
			return;

		
		$this->render_other_vg_wort_plugins_enabled_notice();
		$this->render_operations_post_character_count_recalculations_notice();
		$this->render_operations_old_plugin_import_necessary_notice();
		$this->render_data_privacy_warning_notice();
	}

	
	private function render_data_privacy_warning_notice() {
		
		if ( !$this->options->get_privacy_hide_warning() )
			WPVGW_Helper::render_admin_message(
				sprintf(
					__( 'Der Datenschutz-Hinweis der VG WORT sollte in die Website eingefügt werden. %s',
						WPVGW_TEXT_DOMAIN
					),
					sprintf( '<a href="%s">%s</a>',
						esc_attr( WPVGW_AdminViewsManger::create_admin_view_url( WPVGW_DataPrivacyAdminView::get_slug_static() ) ),
						__( 'Datenschutz-Hinweis hier zur Kenntnis nehmen.', WPVGW_TEXT_DOMAIN )
					)
				),
				WPVGW_ErrorType::Error,
				false
			);
	}

	
	private function render_operations_post_character_count_recalculations_notice() {
		
		if ( $this->options->get_operations_post_character_count_recalculations_necessary() )
			WPVGW_Helper::render_admin_message(
				sprintf(
					__( 'Die Zeichenanzahlen der Beiträge müssen neuberechnet werden. %s',
						WPVGW_TEXT_DOMAIN
					),
					sprintf( '<a href="%s">%s</a>',
						esc_attr( WPVGW_AdminViewsManger::create_admin_view_url( WPVGW_OperationsAdminView::get_slug_static() ) ),
						__( 'Zeichenanzahl hier neuberechnen.', WPVGW_TEXT_DOMAIN )
					)
				),
				WPVGW_ErrorType::Error,
				false
			);
	}

	
	private function render_operations_old_plugin_import_necessary_notice() {
		
		if ( $this->options->get_operation_old_plugin_import_necessary() )
			WPVGW_Helper::render_admin_message(
				sprintf(
					__( 'Die Zählmarken aus einer früheren Version des Plugins sollten importiert werden, da sie sonst fehlen. %s',
						WPVGW_TEXT_DOMAIN
					),
					sprintf( '<a href="%s">%s</a>',
						esc_attr( WPVGW_AdminViewsManger::create_admin_view_url( WPVGW_OperationsAdminView::get_slug_static() ) ),
						__( 'Zählmarken hier importieren.', WPVGW_TEXT_DOMAIN )
					)
				),
				WPVGW_ErrorType::Error,
				false
			);
	}

	
	private function render_other_vg_wort_plugins_enabled_notice() {
		
		if ( !$this->options->get_show_other_active_vg_wort_plugins_warning() )
			return;

		
		$activeVgWortPlugins = WPVGW_Helper::get_other_active_vg_wort_plugins();

		
		if ( $activeVgWortPlugins === array() )
			return;


		$otherActivePluginsText = '';
		$separator = '';

		
		foreach ( $activeVgWortPlugins as $activeVgWortPlugin ) {
			
			$pluginData = get_plugin_data( WP_PLUGIN_DIR . '/' . $activeVgWortPlugin, false, true );

			
			$otherActivePluginsText .= $separator . sprintf( __( '„%s“ (%s)', WPVGW_TEXT_DOMAIN ),
					$pluginData['Name'],
					$pluginData['Version']
				);

			$separator = __( ', ', WPVGW_TEXT_DOMAIN );
		}


		
		WPVGW_Helper::render_admin_message(
			sprintf(
				__( 'Es sind folgende, andere Plugins zur Integration von Zählmarken der VG WORT aktiviert: %s. Diese sollten besser deaktiviert werden, um Zählmarken nicht mehrfach auszugeben. %s', WPVGW_TEXT_DOMAIN ),
				esc_html( $otherActivePluginsText ),
				sprintf( '<a href="%s">%s</a>',
					esc_attr( admin_url( 'plugins.php' ) ),
					__( 'Plugins hier deaktivieren.', WPVGW_TEXT_DOMAIN )
				)
			),
			WPVGW_ErrorType::Error,
			false
		);
	}

	
	public function on_current_screen( $current_screen ) {
		
		$postType = $current_screen->post_type;

		
		$adminViewSlug = WPVGW_Helper::remove_prefix( $current_screen->id, get_plugin_page_hookname( '', WPVGW . '-' . WPVGW_AdminViewsManger::get_default_view_slug() ) . WPVGW . '-', $adminViewSlugFound );

		
		if ( !$adminViewSlugFound ) {
			
			if ( $current_screen->id == 'toplevel_page_' . WPVGW . '-' . WPVGW_AdminViewsManger::get_default_view_slug() ) {
				$adminViewSlug = WPVGW_AdminViewsManger::get_default_view_slug();
				$adminViewSlugFound = true;
			}
		}

		
		if ( $adminViewSlugFound ) {
			$this->adminViewsManager->init( $adminViewSlug );
			$this->adminViewsManager->get_current_view()->do_action();
		}

		
		if ( $this->markersManager->is_post_type_allowed( $postType ) ) {
			
			if ( $current_screen->base == 'post' )
				$this->postView->init();

			
			if ( $current_screen->base == 'edit' ) {
				$this->postTableView->set_post_type( $postType );
				$this->postTableView->init();
			}
		}
	}

	
	public function on_enqueue_admin_css_and_scripts() {
		
		$styleSlug = WPVGW . '-admin';

		
		wp_register_style( $styleSlug, WPVGW_PLUGIN_URL . '/css/admin.css', array(), WPVGW_VERSION );
		wp_enqueue_style( $styleSlug );


		
		$javaScripts = array(
			array(
				'file'         => 'main.js',
				'slug'         => 'main',
				'dependencies' => array( 'jquery' )
			)
		);

		
		if ( $this->adminViewsManager->is_init() )
			$javaScripts = array_merge( $javaScripts, $this->adminViewsManager->get_current_view()->get_javascripts() );

		
		if ( $this->postView->is_init() )
			$javaScripts = array_merge( $javaScripts, $this->postView->get_javascripts() );

		
		if ( $this->postTableView->is_init() )
			$javaScripts = array_merge( $javaScripts, $this->postTableView->get_javascripts() );

		
		foreach ( $javaScripts as $javaScript ) {
			
			$jsSlug = WPVGW . '-' . $javaScript['slug'];
			
			wp_register_script( $jsSlug, WPVGW_PLUGIN_URL . '/js/' . $javaScript['file'], $javaScript['dependencies'], WPVGW_VERSION, true );
			
			wp_enqueue_script( $jsSlug );
			
			if ( array_key_exists( 'localize', $javaScript ) )
				
				wp_localize_script(
					$jsSlug,
					WPVGW . '_' . $javaScript['localize']['object_name'],
					$javaScript['localize']['data']
				);
		}
	}

	
	public function on_add_plugin_admin_menu() {
		$adminViews = $this->adminViewsManager->get_views();
		$adminDefaultViewSlug = $this->adminViewsManager->get_default_view_slug();

		
		add_object_page(
			__( 'Prosodia VGW OS', WPVGW_TEXT_DOMAIN ), 
			__( 'Prosodia VGW OS', WPVGW_TEXT_DOMAIN ), 
			'manage_options', 
			WPVGW . '-' . $adminDefaultViewSlug, 
			array( $adminViews[$adminDefaultViewSlug], 'render' ), 
			'dashicons-admin-wpvgw' 
		);

		
		foreach ( $this->adminViewsManager->get_views() as $viewSlug => $view ) {
			
			add_submenu_page(
				WPVGW . '-' . $adminDefaultViewSlug, 
				$view->get_long_name(), 
				$view->get_short_name(), 
				'manage_options', 
				WPVGW . '-' . $view->get_slug(), 
				array( $view, 'render' ) 
			);
		}
	}

	
	function on_post_saved( $post_id ) {

		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		
		if ( wp_is_post_revision( $post_id ) !== false )
			return;


		
		$post = get_post( $post_id );

		if ( $post === null )
			return;

		
		if ( $this->postView->is_init() )
			$this->postView->do_action( $post );

		
		if ( $this->markersManager->is_post_type_allowed( $post->post_type ) ) {
			$this->postsExtras->insert_update_post_extras_in_db(
				array(
					'post_id'         => $post->ID,
					'character_count' => $this->markersManager->calculate_character_count( $post->post_title, $post->post_content ),
				)
			);
		}

	}

	
	public function on_post_deleted( $post_id ) {
		
		$this->markersManager->update_marker_in_db(
			$post_id,
			'post_id',
			array(
				'is_post_deleted'    => true,
				'deleted_post_title' => get_the_title( $post_id )
			)
		);

		
		$this->postsExtras->delete_post_extra( $post_id );
	}


	
	public function on_display_marker() {
		echo( $this->get_marker() );
	}

	
	public function get_marker() {
		
		$marker = $this->get_marker_data();

		
		if ( $marker === false || $marker['is_marker_disabled'] )
			return '';

		
		return apply_filters(
			'wp_vgwort_frontend_display', 
			sprintf( $this->options->get_output_format(),
				esc_attr( $marker['server'] ),
				esc_attr( $marker['public_marker'] )
			),
			$marker
		);
	}

	
	public function get_marker_data() {
		
		if ( !is_single() && !is_page() )
			return false;

		
		$post = get_post();

		if ( $post === null )
			return false;

		
		if ( !$this->markersManager->is_post_type_allowed( $post->post_type ) ||
			!$this->markersManager->is_user_allowed( intval( $post->post_author ) )
		)
			return false;


		
		return $this->markersManager->get_marker_from_db( $post->ID, 'post_id' );
	}

	
	public function remove_display_marker() {
		remove_action( 'wp_footer', array( $this, 'on_display_marker' ), $this->frontendDisplayFilterPriority );
	}

}
