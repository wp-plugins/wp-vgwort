<?php

/**
 *
 * @package   PluginName
 * @author    Marcus Franke <wgwortplugin@mywebcheck.de>
 * @license   GPL-2.0+
 * @link      http://mywebcheck.de
 * @copyright 2013 MyWebcheck
 *
 * @wordpress-plugin
 * Plugin Name: WP VG WORT
 * Plugin URI:  http://www.mywebcheck.de/vg-wort-plugin-wordpress/
 * Description: Verwaltung der VG Wort Zählpixel
 * Version:     2.0.3
 * Author:      Marcus Franke
 * Author URI:  http://mywebcheck.de
 * Text Domain: wp-vgwort-locale
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// TODO: replace `class-plugin-name.php` with the name of the actual plugin's class file
require_once( plugin_dir_path( __FILE__ ) . 'class-wp-vgwort.php' );

// TODO: replace PluginName with the name of the plugin defined in `class-plugin-name.php`
WP_VGWORT::get_instance();