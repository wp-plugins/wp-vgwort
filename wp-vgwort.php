<?php
/**
 *
 * @package   WP_VGWORT
 * @author    Marcus Franke <wgwortplugin@mywebcheck.de>, Ronny Harbich
 * @license   GPL-2.0+
 * @link      http://mywebcheck.de
 * @copyright 2014 Marcus Franke, Ronny Harbich
 *
 * @wordpress-plugin
 * Plugin Name: VG WORT Zählmarken
 * Plugin URI:  http://www.mywebcheck.de/vg-wort-plugin-wordpress/
 * Description: Integrieren Sie Zählmarken der VG WORT in Wordpress.
 * Version:     2.1.1
 * Author:      Marcus Franke, Ronny Harbich
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