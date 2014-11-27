<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: 2014 Ronny Harbich
 * License: GPLv2 or later
 */


/**
 * The plugin’s main class.
 */
class WPVGW {

	/**
	 * @var WPVGW_Options The options.
	 */
	private $options;

	/**
	 * @var string The name of the Version Option in the WordPress database.
	 */
	private $versionOptionName;
	/**
	 * @var string The name of the Options object in the WordPress database.
	 */
	private $optionsName;

	/**
	 * @var string The slug for the plugin’s database tables. Each plugin table has to be prefixed with this slug.
	 */
	private $tableSlug;

	/**
	 * @var string The name of the markers table in the database.
	 */
	private $markersTableName;
	/**
	 * @var string The name of the posts extras table in the database.
	 */
	private $postsExtrasTableName;

	/**
	 * @var WPVGW_AdminViewsManger|null An admin view or null if no admin view is initialized.
	 */
	private $adminViewsManager = null;
	/**
	 * @var WPVGW_PostView|null An post view or null if no post view is initialized.
	 */
	private $postView = null;

	/**
	 * @var WPVGW_PostTableView|null An post table view or null if no past table view is initialized.
	 */
	private $postTableView = null;

	/**
	 * @var WPVGW_MarkersManager The markers manager.
	 */
	private $markersManager;
	/**
	 * @var WPVGW_PostsExtras The posts extras
	 */
	private $postsExtras;

	/**
	 * @var WPVGW The unique instance of the {@link WPVGW} class.
	 */
	private static $instance = null;


	/**
	 * @var int The filter priority for WordPress action "wp_footer".
	 */
	private $frontendDisplayFilterPriority = 1800;


	/**
	 * Get the unique instance of the {@link WPVGW} class.
	 *
	 * @return WPVGW The unique instance of the {@link WPVGW} class.
	 */
	public static function get_instance() {
		// if the unique instance has not been set, set it.
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Creates a new instance of the {@link WPVGW} class.
	 */
	private function __construct() {
		// set slugs and names
		$this->versionOptionName = WPVGW . '_version';
		$this->optionsName = WPVGW . '_options_v1';
		$this->tableSlug = WPVGW;

		// register init action
		add_action( 'init', array( $this, 'init' ) );

		// hook triggered if the plugin will be activated
		register_activation_hook( WPVGW_PLUGIN_PATH . 'wp-vgwort.php', array( $this, 'on_activation' ) );
		// hook triggered if the plugin will be deactivated
		register_deactivation_hook( WPVGW_PLUGIN_PATH . 'wp-vgwort.php', array( $this, 'on_deactivation' ) );
	}

	/**
	 * Called by WordPress if the plugin is initialized.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 */
	public function init() {
		/** @var wpdb $wpdb */
		//global $wpdb;

		// load translations
		load_plugin_textdomain( WPVGW_TEXT_DOMAIN, false, WPVGW_PLUGIN_PATH_RELATIVE . '/languages' );

		// hook triggered when WordPress and all plugins as loaded
		add_action( 'wp_loaded', array( $this, 'on_wordpress_loaded' ) );

		// administration interface?
		if ( is_admin() ) {
			// hook triggered before any other hook when a user accesses the administration interface
			add_action( 'admin_init', array( $this, 'on_admin_init' ) );
			// hook for admin messages
			add_action( 'admin_notices', array( $this, 'on_admin_notice' ) );
			// hook to identify the current administration screen/page
			add_action( 'current_screen', array( $this, 'on_current_screen' ) );
			// hook to enqueue scripts and styles for the administration interface
			add_action( 'admin_enqueue_scripts', array( $this, 'on_enqueue_admin_css_and_scripts' ) );
			// hook to add an option page and menu item
			add_action( 'admin_menu', array( $this, 'on_add_plugin_admin_menu' ) );

			// hook triggered if a post is saved
			add_action( 'save_post', array( $this, 'on_post_saved' ) );
			// hook triggered if a post is deleted
			add_action( 'delete_post', array( $this, 'on_post_deleted' ) );

			//add_action( 'user_register', array( $this, 'on_user_register' ) );
			//add_action( 'wpmu_new_user', array( $this, 'on_wpmu_user_register' ) );
			//add_action( 'profile_update', array( $this, 'on_user_update' ), 10, 2 );
			//add_action( 'delete_user', array( $this, 'on_user_deleted' ) );
			//add_action( 'wpmu_delete_user', array( $this, 'on_wpmu_user_deleted' ) );
			// action hook if user was added to the blog (multisite): do_action( 'add_user_to_blog', $user_id, $role, $blog_id );
			//add_action( 'remove_user_from_blog', array( $this, 'on_user_remove' ), 10, 2 );

			// hook triggered the plugin will be deinitialized
			add_action( 'shutdown', array( $this, 'on_deinit' ) );

			//add_filter( 'network_admin_plugin_action_links_' . WPVGW_PLUGIN_NAME, array( $this, 'xyz' ), 10, 4 );
			// hook to add links for the plugin on the plugin overview table
			add_filter( 'plugin_action_links_' . WPVGW_PLUGIN_NAME, array( $this, 'on_plugin_action_links' ), 10, 4 );

			// hook to save screen options; not used currently
			// add_filter('set-screen-option', array($this,'on_set_screen_option'), 10, 3); // public function on_set_screen_option($status, $option, $value) { return $value }
		}
		else {
			// hook to output HTML before the body tag is closed
			add_action( 'wp_footer', array( $this, 'on_display_marker' ), $this->frontendDisplayFilterPriority );
		}
	}


	/**
	 * Gets the name of the markers table.
	 *
	 * @return string The markers table name.
	 */
	private function create_markers_table_name() {
		/** @var wpdb $wpdb */
		global $wpdb;

		return $wpdb->prefix . $this->tableSlug . '_markers';
	}

	/**
	 * Gets the name of the posts extras table.
	 *
	 * @return string The posts extras table name.
	 */
	private function create_posts_extras_table_name() {
		/** @var wpdb $wpdb */
		global $wpdb;

		return $wpdb->prefix . $this->tableSlug . '_posts_extras';
	}

	/*public function on_user_register( $user_id ) {
		$x = 1;
	}

	public function on_wpmu_user_register( $user_id ) {
		$x = 1;
	}*/

	/*public function on_user_update( $user_id, $old_user_data ) {
		$user_id = (int)$user_id;

		if ( !$this->markersManager->is_user_allowed( $user_id ) )
			return;


		$user = get_userdata( $user_id );

		if ( $user === false )
			return;


		if ( !$this->is_user_role_allowed( $user->roles ) )
			$this->markersManager->remove_allowed_user( $user_id );
	}*/

	/*function on_user_deleted( $user_id ) {
		$x = 1;
	}

	function on_wpmu_user_deleted( $user_id ) {
		$x = 1;
	}*/

	/*public function on_user_remove( $user_id, $blog_id ) {
		$user_id = (int)$user_id;
		$blog_id = (int)$blog_id;

		if ( $blog_id != get_current_blog_id() || !$this->markersManager->is_user_allowed( $user_id ) )
			return;

		$this->markersManager->remove_allowed_user( $user_id );
	}


	private function is_user_role_allowed( $roles ) {
		if ( is_array( $roles ) )
			return count( array_intersect( $this->options->get_allowed_user_roles(), $roles ) ) > 0;

		return in_array( $roles, $this->options->get_allowed_user_roles() );
	}*/


	/**
	 * Called by WordPress if WordPress and all plugins as loaded.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 */
	public function on_wordpress_loaded() {
		// set slugs and names
		$this->markersTableName = $this->create_markers_table_name();
		$this->postsExtrasTableName = $this->create_posts_extras_table_name();

		// get options and merge with default options
		$this->options = WPVGW_Options::get_instance();
		$this->options->init( $this->optionsName );

		// create markers manager
		$this->markersManager = new WPVGW_MarkersManager(
			$this->markersTableName,
			$this->options->get_allowed_user_roles(),
			$this->options->get_allowed_post_types(),
			$this->options->get_do_shortcodes_for_character_count_calculation()
		);

		// create post extras
		$this->postsExtras = new WPVGW_PostsExtras( $this->postsExtrasTableName, $this->markersManager );


		// administration interface?
		if ( is_admin() ) {
			// create views for the administration interface
			$this->adminViewsManager = new WPVGW_AdminViewsManger( $this->markersManager, $this->postsExtras, $this->options );
			$this->postView = new WPVGW_PostView( $this->markersManager, $this->options );
			$this->postTableView = new WPVGW_PostTableView( $this->markersManager, $this->postsExtras, $this->options );
		}
	}


	/**
	 * Called by WordPress if the plugin will be deinitialized
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 */
	public function on_deinit() {
		// delegate some settings back to the options object
		$this->options->set_allowed_post_types( $this->markersManager->get_allowed_post_types() );

		// store options in database if changed
		$this->options->store_in_db();
	}

	/**
	 * Called by WordPress if a user access the administration interface.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 */
	public function on_admin_init() {
		$this->upgrade_plugin();
	}

	/**
	 * Installs the plugin into WordPress.
	 */
	private function install_plugin() {
		// create database tables
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// create markers table SQL
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

		// create posts extras table SQL
		$sql = "CREATE TABLE IF NOT EXISTS $this->postsExtrasTableName (
					post_id bigint(20) unsigned NOT NULL,
					character_count bigint(20) unsigned NOT NULL,
					PRIMARY KEY (post_id),
					KEY character_count (character_count)
				);";
		dbDelta( $sql );
	}

	/**
	 * Uninstalls the plugin form WordPress.
	 * Warning: This method is called for different sites in multisite WordPress environments. Therefore names and options have to be reinitialized.
	 */
	private function uninstall_plugin() {
		/** @var wpdb $wpdb */
		global $wpdb;

		// get table names for the current site
		$markersTableName = $this->create_markers_table_name();
		$postsExtrasTableName = $this->create_posts_extras_table_name();

		// drop tables for the current site
		$wpdb->query( "DROP TABLE {$markersTableName}" );
		$wpdb->query( "DROP TABLE {$postsExtrasTableName}" );

		// delete options for the current site
		delete_option( $this->versionOptionName );
		delete_option( $this->optionsName );
	}

	/**
	 * Upgrades the plugin from previous versions. It upgrades step by step for each version between the old and the current version.
	 * If the plugin was not installed before the plugin will be installed.
	 */
	private function upgrade_plugin() {
		// get old version number or null if not available
		$oldVersion = get_option( $this->versionOptionName, null );

		// do not upgrade if old version is current version
		if ( $oldVersion === WPVGW_VERSION )
			return;


		// plugin version not found
		if ( $oldVersion === null ) {

			// try to determinate if we have an old plugin version
			$isVersion100 = (
				get_option( 'wp_cpt', false ) !== false ||
				get_option( 'wp_vgwort_options', false ) !== false ||
				get_option( 'wp_vgwortmetaname', false ) !== false
			);

			if ( $isVersion100 ) {
				// old version is version 1.0.0
				$oldVersion = '1.0.0';
			}
			else {
				// plugin was not installed before
				$this->install_plugin();

				// set old version to current version
				$oldVersion = WPVGW_VERSION;
			}
		}


		/** @var wpdb $wpdb */
		global $wpdb;

		// upgrade from old versions
		if ( version_compare( $oldVersion, '1.0.0', '<=' ) ) {
			// create database tables
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$markersTableName = $wpdb->prefix . 'wpvgw_markers';
			// markers table
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
			// posts extras table
			$sql = "CREATE TABLE IF NOT EXISTS $postsExtrasTableName (
						post_id bigint(20) unsigned NOT NULL,
						character_count bigint(20) unsigned NOT NULL,
						PRIMARY KEY (post_id),
						KEY character_count (character_count)
					);";
			dbDelta( $sql );

			// get old frontend options and store them in new options
			$oldAllowedPostTypes = get_option( 'wp_cpt', array() );
			// add 'post' and 'page' from custom post types
			$this->markersManager->set_allowed_post_types( array_unique( array_merge( $oldAllowedPostTypes, array( 'post', 'page' ) ) ) );

			// get old admin options and store them in new options
			$metaName = get_option( 'wp_vgwortmetaname', 'wp_vgwortmarke' );
			$this->options->set_meta_name( $metaName == '' ? 'wp_vgwortmarke' : $metaName );


			// delete old options
			//delete_option( 'wp_cpt' );
			//delete_option( 'wp_vgwort_options' );
			//delete_option( 'wp_vgwortmetaname' );

			// recalculation of the post character counts is necessary because prior version have this not done before
			$this->options->set_operations_post_character_count_recalculations_necessary( true );

			// import of markers from old plugin is necessary
			$this->options->set_operation_old_plugin_import_necessary( true );
		}


		// upgrade to version 3.1.1
		if ( version_compare( $oldVersion, '3.1.1', '<' ) ) {
			// fix empty meta name options
			// try to validate current meta name value
			try {
				$this->options->set_meta_name( $this->options->get_meta_name() );
			} catch ( Exception $e ) {
				// if not valid set default value
				$this->options->set_meta_name( 'wp_vgwortmarke' );
			}
		}


		// this is an example for futur versions
		/*if (version_compare($oldVersion , '2.0.0' ,'<'))
		{

		}*/


		// update version options
		update_option( $this->versionOptionName, WPVGW_VERSION );
	}

	/**
	 * Called by WordPress if a user activates the plugin in the administration interface.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 *
	 * @param bool $sitewide If true the deactivation is for all sites (multisite), otherwise it’s for a single site only.
	 *
	 * @throws Exception Thrown if internal error occurred.
	 */
	public function on_activation( $sitewide ) {
		// check user permissions
		if ( !current_user_can( 'activate_plugins' ) )
			return;

		// get options and merge with default options
		$options = WPVGW_Options::get_instance();
		$options->init( $this->optionsName );

		// post character count recalculations operations are necessary
		$options->set_operations_post_character_count_recalculations_necessary( true );

		// store options in database if changed
		$options->store_in_db();
	}

	/**
	 * Called by WordPress if a user deactivates the plugin in the administration interface.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 *
	 * @param bool $sitewide If true the deactivation is for all sites (multisite), otherwise it’s for a single site only.
	 */
	public function on_deactivation( $sitewide ) {
		// check user permissions
		if ( !current_user_can( 'activate_plugins' ) )
			return;

		// TODO: Sitewide deinstallation is not supported currently.
		if ( $sitewide ) {
			/** @var wpdb $wpdb */
			/*global $wpdb;


			$sites = wp_get_sites( array( 'limit' => null ) );

			foreach ( $sites as $site ) {
				$siteId = (int)$site['blog_id'];

				switch_to_blog( $siteId );

				// uninstall if plugin shall be uninstalled (but only if plugin is installed)
				if ( get_option( $this->versionOptionName, null ) !== null )
					$this->uninstall_plugin();

				restore_current_blog();
			}*/
		}
		// deactivate for a single site
		else {
			// uninstall if plugin should be uninstalled on deactivation (but only if the plugin is installed)
			if ( get_option( $this->versionOptionName, null ) !== null && $this->options->get_remove_data_on_uninstall() )
				$this->uninstall_plugin();
		}

	}

	/**
	 * Adds action links to the plugin’s entry in the plugin overview table in the administration interface.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 *
	 * @param array $actions An array of HTML links which are the actions.
	 * @param $plugin_file
	 * @param $plugin_data
	 * @param $context
	 *
	 * @return array An array of HTML links which are the new actions.
	 */
	public function on_plugin_action_links( $actions, $plugin_file, $plugin_data, $context ) {
		// add "go to VG WORT page" action
		$actions[] = sprintf( '<a href="%s">%s</a>',
			esc_attr( WPVGW_AdminViewsManger::create_admin_view_url() ),
			__( 'Einstellungen', WPVGW_TEXT_DOMAIN )
		);

		return $actions;
	}

	/**
	 * Renders admin messages.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 */
	public function on_admin_notice() {
		// allow admin users only
		if ( !current_user_can( 'manage_options' ) )
			return;

		// render admin notices
		$this->render_other_vg_wort_plugins_enabled_notice();
		$this->render_operations_post_character_count_recalculations_notice();
		$this->render_operations_old_plugin_import_necessary_notice();
		$this->render_data_privacy_warning_notice();
	}

	/**
	 * Renders an admin message if data privacy warning is not hidden.
	 */
	private function render_data_privacy_warning_notice() {
		// show data privacy warning?
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

	/**
	 * Renders an admin message if post character count recalculations are necessary.
	 */
	private function render_operations_post_character_count_recalculations_notice() {
		// show data privacy warning?
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

	/**
	 * Renders an admin message if import markers from old plugin is necessary.
	 */
	private function render_operations_old_plugin_import_necessary_notice() {
		// show import markers from old plugin warning?
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

	/**
	 * Renders an admin message if other VG WORT plugins were found.
	 */
	private function render_other_vg_wort_plugins_enabled_notice() {
		// show warning if other VG WORT plugin are active?
		if ( !$this->options->get_show_other_active_vg_wort_plugins_warning() )
			return;

		// get other active VG WORT plugins
		$activeVgWortPlugins = WPVGW_Helper::get_other_active_vg_wort_plugins();

		// no other active VG WORT plugins found?
		if ( $activeVgWortPlugins === array() )
			return;


		$otherActivePluginsText = '';
		$separator = '';

		// iterate other VG WORT plugins
		foreach ( $activeVgWortPlugins as $activeVgWortPlugin ) {
			// get plugin data (name etc.)
			$pluginData = get_plugin_data( WP_PLUGIN_DIR . '/' . $activeVgWortPlugin, false, true );

			// build plugin list text
			$otherActivePluginsText .= $separator . sprintf( __( '„%s“ (%s)', WPVGW_TEXT_DOMAIN ),
					$pluginData['Name'],
					$pluginData['Version']
				);

			$separator = __( ', ', WPVGW_TEXT_DOMAIN );
		}


		// render other VG WORT plugins admin message
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

	/**
	 * Called by WordPress if the administration page/screen changed.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 *
	 * @param WP_Screen $current_screen The current administration screen.
	 *
	 * @throws Exception
	 */
	public function on_current_screen( $current_screen ) {
		// get the post type used in the current screen
		$postType = $current_screen->post_type;

		// find the admin view if possible
		$adminViewSlug = WPVGW_Helper::remove_prefix( $current_screen->id, get_plugin_page_hookname( '', WPVGW . '-' . WPVGW_AdminViewsManger::get_default_view_slug() ) . WPVGW . '-', $adminViewSlugFound );

		// admin view not found
		if ( !$adminViewSlugFound ) {
			// is top level admin view?
			if ( $current_screen->id == 'toplevel_page_' . WPVGW . '-' . WPVGW_AdminViewsManger::get_default_view_slug() ) {
				$adminViewSlug = WPVGW_AdminViewsManger::get_default_view_slug();
				$adminViewSlugFound = true;
			}
		}

		// if current admin screen is an admin view
		if ( $adminViewSlugFound ) {
			$this->adminViewsManager->init( $adminViewSlug );
			$this->adminViewsManager->get_current_view()->do_action();
		}

		// post type allowed?
		if ( $this->markersManager->is_post_type_allowed( $postType ) ) {
			// TODO: $current_screen->base might not be good to distinguish the post, page and custom post type edit/new pages.
			if ( $current_screen->base == 'post' )
				$this->postView->init();

			// TODO: Possible bad because WP_Query will be manipulated if table of all post is generated.
			if ( $current_screen->base == 'edit' ) {
				$this->postTableView->set_post_type( $postType );
				$this->postTableView->init();
			}
		}
	}

	/**
	 * Enqueues CSS and JS for the WordPress’ administration interface.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 */
	public function on_enqueue_admin_css_and_scripts() {
		// create style slug
		$styleSlug = WPVGW . '-admin';

		// register and enqueue styles
		wp_register_style( $styleSlug, WPVGW_PLUGIN_URL . '/css/admin.css', array(), WPVGW_VERSION );
		wp_enqueue_style( $styleSlug );


		// JavaScripts; add main JavaScripts
		$javaScripts = array(
			array(
				'file'         => 'main.js',
				'slug'         => 'main',
				'dependencies' => array( 'jquery' )
			)
		);

		// add admin view JavaScripts
		if ( $this->adminViewsManager->is_init() )
			$javaScripts = array_merge( $javaScripts, $this->adminViewsManager->get_current_view()->get_javascripts() );

		// add post view JavaScripts
		if ( $this->postView->is_init() )
			$javaScripts = array_merge( $javaScripts, $this->postView->get_javascripts() );

		// add post table view JavaScripts
		if ( $this->postTableView->is_init() )
			$javaScripts = array_merge( $javaScripts, $this->postTableView->get_javascripts() );

		// register and enqueue JavaScripts
		foreach ( $javaScripts as $javaScript ) {
			// create slug
			$jsSlug = WPVGW . '-' . $javaScript['slug'];
			// register script
			wp_register_script( $jsSlug, WPVGW_PLUGIN_URL . '/js/' . $javaScript['file'], $javaScript['dependencies'], WPVGW_VERSION, true );
			// enqueue script
			wp_enqueue_script( $jsSlug );
			// localize script? (need for AJAX)
			if ( array_key_exists( 'localize', $javaScript ) )
				// localize script
				wp_localize_script(
					$jsSlug,
					WPVGW . '_' . $javaScript['localize']['object_name'],
					$javaScript['localize']['data']
				);
		}
	}

	/**
	 * Registers the administration menu the plugin into the WordPress administration interface.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 */
	public function on_add_plugin_admin_menu() {
		$adminViews = $this->adminViewsManager->get_views();
		$adminDefaultViewSlug = $this->adminViewsManager->get_default_view_slug();

		// add VG WORT default view to the admin menu
		add_object_page(
			__( 'Prosodia VGW OS', WPVGW_TEXT_DOMAIN ), // page title
			__( 'Prosodia VGW OS', WPVGW_TEXT_DOMAIN ), // menu title
			'manage_options', // permission
			WPVGW . '-' . $adminDefaultViewSlug, // slug
			array( $adminViews[$adminDefaultViewSlug], 'render' ), // callback
			'dashicons-admin-wpvgw' // icon
		);

		// add admin views
		foreach ( $this->adminViewsManager->get_views() as $viewSlug => $view ) {
			/* @var WPVGW_AdminViewBase $view */
			add_submenu_page(
				WPVGW . '-' . $adminDefaultViewSlug, // parent slug
				$view->get_long_name(), // page title
				$view->get_short_name(), // menu title
				'manage_options', // permission
				WPVGW . '-' . $view->get_slug(), // slug
				array( $view, 'render' ) // callback
			);
		}
	}

	/**
	 * Saves information for a post.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 *
	 * @throws Exception Thrown if a Regex error occurred.
	 *
	 * @param: int $post_id The post ID of the post that was saved.
	 */
	function on_post_saved( $post_id ) {

		// return if AutoSave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// do not handle revisions
		if ( wp_is_post_revision( $post_id ) !== false )
			return;


		// get the post object
		$post = get_post( $post_id );

		if ( $post === null )
			return;

		// do post view action if post view was initialized
		if ( $this->postView->is_init() )
			$this->postView->do_action( $post );

		// recount character count of the post
		if ( $this->markersManager->is_post_type_allowed( $post->post_type ) ) {
			$this->postsExtras->insert_update_post_extras_in_db(
				array(
					'post_id'         => $post->ID,
					'character_count' => $this->markersManager->calculate_character_count( $post->post_title, $post->post_content ),
				)
			);
		}

	}

	/**
	 * Deletes information for a post.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 *
	 * @throws Exception Thrown if a database error occurred.
	 *
	 * @param int $post_id The post ID of the post that will be deleted.
	 */
	public function on_post_deleted( $post_id ) {
		// tell marker manager that the post is deleted
		$this->markersManager->update_marker_in_db(
			$post_id,
			'post_id',
			array(
				'is_post_deleted'    => true,
				'deleted_post_title' => get_the_title( $post_id )
			)
		);

		// remove post extras
		$this->postsExtras->delete_post_extra( $post_id );
	}


	/**
	 * Renders the VG WORT marker of the current post into the page.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 * Call {@link remove_display_marker} to disable the automatic rendering.

	 */
	public function on_display_marker() {
		echo( $this->get_marker() );
	}

	/**
	 * Gets the VG WORT marker string for the current post (if available, post type allowed, post author allowed and not disabled).
	 * It is possible to filter the output:
	 * <code>
	 * add_filter( 'wp_vgwort_frontend_display', 'my_frontend_display_filter' );
	 * function my_frontend_display_filter( $html, $marker ) {
	 *  return ('VG-Wort-Marke: ' . $html);
	 * }
	 * </code>
	 *
	 * @return string The VG WORT marker as defined in the output format setting (at settings page).
	 */
	public function get_marker() {
		// get marker for the current post
		$marker = $this->get_marker_data();

		// marker exists and enabled?
		if ( $marker === false || $marker['is_marker_disabled'] )
			return '';

		// TODO: Attribute escaping maybe bad because we don’t know if the output format use server and marker in a HTML attribute.
		return apply_filters(
			'wp_vgwort_frontend_display', // for compatibility the old plugin slug is used
			sprintf( $this->options->get_output_format(),
				esc_attr( $marker['server'] ),
				esc_attr( $marker['public_marker'] )
			),
			$marker
		);
	}

	/**
	 * Returns the VG WORT marker data for the current post (only if available, post type allowed and post author allowed).
	 *
	 * @return array|bool An array with marker data or false if no marker was found or the current query is not a single post or page.
	 */
	public function get_marker_data() {
		// single posts or pages only
		if ( !is_single() && !is_page() )
			return false;

		// get current post object
		$post = get_post();

		if ( $post === null )
			return false;

		// post type and user allowed?
		if ( !$this->markersManager->is_post_type_allowed( $post->post_type ) ||
			!$this->markersManager->is_user_allowed( intval( $post->post_author ) )
		)
			return false;


		// get marker for current post
		return $this->markersManager->get_marker_from_db( $post->ID, 'post_id' );
	}

	/**
	 * Removes the automatic rendering of a post’s marker into the post page.
	 * Useful if you want to place the marker in your WordPress theme manually.
	 */
	public function remove_display_marker() {
		remove_action( 'wp_footer', array( $this, 'on_display_marker' ), $this->frontendDisplayFilterPriority );
	}

}
