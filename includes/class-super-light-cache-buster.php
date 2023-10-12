<?php
/**
 * Core plugin class
 *
 * @since             1.0.0
 * @package           Super_Light_Cache_Buster
 *
 * @wordpress-plugin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'util/helpers.php';

/**
 * The class responsible for defining all actions that relate to plugin settings.
 */
require_once plugin_dir_path( dirname( __FILE__ ) ) . '/includes/class-super-light-cache-buster-settings.php';

if ( ! class_exists( 'Super_Light_Store_Hours' ) ) {

	/**
	 * The class that handles cache prevention and its various options.
	 *
	 * Cache prevention is achieved via this class in two main ways.
	 * (1) Randomizing CSS and JS asset version numbers.
	 * (2) Adding No-Cache directives.
	 *
	 * @since 1.0.0
	 */
	class Super_Light_Cache_Buster {
		/**
		 * The class responsible for the plugin's settings.
		 *
		 * @var Super_Light_Cache_Buster_Settings $settings The settings class that is included.
		 */
		private $settings;
		/**
		 * Initializes object's properties upon creation of the object.
		 */
		public function __construct() {
			$this->settings = new Super_Light_Cache_Buster_Settings();
		}
	}
}

if ( class_exists( 'Super_Light_Cache_Buster' ) ) {
	// Plugin uninstallation.
	register_uninstall_hook( __FILE__, 'Super_Light_Cache_Buster::uninstall_slcb' );
}
