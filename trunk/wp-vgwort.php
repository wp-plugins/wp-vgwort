<?php
/**
 * Plugin Name: Prosodia VGW OS für Zählmarken (VG WORT)
 * Plugin URI: https://wordpress.org/plugins/wp-vgwort/
 * Description: Verdienen Sie mit Ihren Beiträgen/Texten Geld durch die Integration von Zählmarken der VG WORT.
 * Version: 3.1.0
 * Author: Prosodia – Verlag für Musik und Literatur
 * Author URI: http://prosodia.de/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpvgw
 * Domain Path: /languages
 *
 * @author    Ronny Harbich <ronny@developer.falconiform.de>, Marcus Franke <wgwortplugin@mywebcheck.de>
 * @license   GPLv2 or later
 * @link      http://prosodia.de/
 * @copyright 2014 Ronny Harbich, Marcus Franke
 */


// exit if file is accessed directly (outside from wordpress)
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * The global plugin slug.
 */
define( 'WPVGW', 'wpvgw' );

/**
 * The global plugin version.
 */
define( 'WPVGW_VERSION', '3.1.0' );

/**
 * The global plugin path (without trailing slash).
 */
define( 'WPVGW_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The global relative plugin path (without trailing slash).
 */
define( 'WPVGW_PLUGIN_PATH_RELATIVE', dirname( plugin_basename( __FILE__ ) ) );

/**
 * The plugin URL. The URL has no trailing slash.
 */
define( 'WPVGW_PLUGIN_URL', plugins_url( '', __FILE__ ) );

/**
 * The name of the plugin. Something like "my-plugin/my-plugin.php"
 */
define( 'WPVGW_PLUGIN_NAME', plugin_basename( __FILE__ ) );

/**
 * The global plugin text domain (need for translation).
 */
define( 'WPVGW_TEXT_DOMAIN', WPVGW );


// include all plugin files
require_once( WPVGW_PLUGIN_PATH . 'wpvgw.php' );

require_once( WPVGW_PLUGIN_PATH . 'includes/options.php' );
require_once( WPVGW_PLUGIN_PATH . 'includes/markers-manager.php' );
require_once( WPVGW_PLUGIN_PATH . 'includes/helper.php' );
require_once( WPVGW_PLUGIN_PATH . 'includes/posts-extras.php' );
require_once( WPVGW_PLUGIN_PATH . 'includes/admin-views-manager.php' );
require_once( WPVGW_PLUGIN_PATH . 'includes/uncached-wp-query.php' );

require_once( WPVGW_PLUGIN_PATH . 'views/view-base.php' );
require_once( WPVGW_PLUGIN_PATH . 'views/markers-table.php' );
require_once( WPVGW_PLUGIN_PATH . 'views/post-view.php' );
require_once( WPVGW_PLUGIN_PATH . 'views/post-table-view.php' );
require_once( WPVGW_PLUGIN_PATH . 'views/admin/admin-view-base.php' );
require_once( WPVGW_PLUGIN_PATH . 'views/admin/markers-admin-view.php' );
require_once( WPVGW_PLUGIN_PATH . 'views/admin/import-admin-view.php' );
require_once( WPVGW_PLUGIN_PATH . 'views/admin/configuration-admin-view.php' );
require_once( WPVGW_PLUGIN_PATH . 'views/admin/operations-admin-view.php' );
require_once( WPVGW_PLUGIN_PATH . 'views/admin/data-privacy-admin-view.php' );
require_once( WPVGW_PLUGIN_PATH . 'views/admin/support-admin-view.php' );
require_once( WPVGW_PLUGIN_PATH . 'views/admin/about-admin-view.php' );


// run the plugin
WPVGW::get_instance();
