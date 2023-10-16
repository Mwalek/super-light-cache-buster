<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           Super_Light_Cache_Buster
 *
 * @wordpress-plugin
 * Plugin Name:       Super Light Cache Buster
 * Description:       With a compressed size of under 30KB, this simple plugin adds random version numbers to CSS & JS assets to vanquish browser caching. Clear your Site and Server-side caches, and this plugin will do the rest.
 * Version:           1.4.0
 * Author:            Mwale Kalenga
 * Author URI:        https://mwale.me
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.en.html
 * Text Domain:       super-light-cache-buster
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-super-light-cache-buster.php';

/**
 * Begins execution of the plugin.
 *
 * @return void
 */
function run_super_light_cache_buster() {
	$plugin = new Super_Light_Cache_Buster();
}

run_super_light_cache_buster();
