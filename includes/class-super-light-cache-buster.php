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
		 * Setting that determines if assets are served with a random query string.
		 *
		 * @var array $randomizer_control The randomizer control setting.
		 */
		public $randomizer_control;

		/**
		 * Initializes object's properties upon creation of the object.
		 */
		public function __construct() {
			$this->randomizer_control = get_option( 'slcb_plugin_state', $this->get_slcb_fields( 0 ) );
			$this->adv_option_control = get_option( 'slcb_intensity_level', $this->get_slcb_fields( 1 ) );
			$this->ver_name_control   = get_option( 'slcb_version_name', $this->get_slcb_fields( 2 ) );
			$this->settings           = new Super_Light_Cache_Buster_Settings();
			// Randomize asset version for styles.
			add_filter( 'style_loader_src', array( $this, 'slcb_randomize_ver' ), 9999 );
			// Randomize asset version for scripts.
			add_filter( 'script_loader_src', array( $this, 'slcb_randomize_ver' ), 9999 );

			add_action( 'admin_bar_menu', array( $this, 'slcb_buster_button' ), 50 );
			add_action( 'template_redirect', array( $this, 'redirect_to_uncached_resource' ) );

			if ( 'option1' === $this->randomizer_control[0] && 'option2' === $this->adv_option_control[0] ) {

				add_action( 'send_headers', array( $this, 'slcb_status_header' ), 9999 );

				add_action( 'wp_head', array( $this, 'hook_in_header' ) );

				add_action( 'template_redirect', array( $this, 'donotcachepage' ), 9999 );

			}
		}

		/**
		 * Gets settings fields helper function.
		 *
		 * @param string $offset1 Position of field in array returned by all_fields function.
		 * @param string $offset2 Optional. Name of option to retrieve.
		 * @return array A particular option of the specificed field.
		 */
		public function get_slcb_fields( $offset1, $offset2 = 'default' ) {
			$fields_array = Super_Light_Cache_Buster_Settings::all_fields();
			return( $fields_array[ $offset1 ][ $offset2 ] );
		}
		/**
		 * Gets field uids.
		 *
		 * @return void
		 */
		public function get_slcb_uids() {
			$fields_array = Super_Light_Cache_Buster_Settings::all_fields();
			$uid          = $fields_arrays[0]['uid'];
		}

		/**
		 * Randomizes version numbers.
		 *
		 * @param string $src The source URL of the enqueued style/script.
		 * @return string The randomized version of the URL.
		 */
		public function slcb_randomize_ver( $src ) {
			$allow_in_backend = apply_filters( 'slcb_allow_in_backend', false );
			if ( ( ! is_admin() || $allow_in_backend ) && 'option1' === $this->randomizer_control[0] ) {
				$custom_ver_name = apply_filters( 'slcb_version_name', $this->ver_name_control );
				$random_number   = wp_rand( 1000, 520000000 );
				$version_name    = $random_number;
				// If string and not empty proceed.
				if ( is_string( $custom_ver_name ) && ! empty( $custom_ver_name ) ) {

					$custom_url_check = home_url( '/?ver=' . $custom_ver_name );
					// Only use custom version name if the URL is still valid.
					if ( filter_var( $custom_url_check, FILTER_VALIDATE_URL ) ) {
						$version_name = $custom_ver_name;
					}
				}
				$src = esc_url( add_query_arg( 'ver', $version_name, $src ) );
				return $src;
			}
			return $src;
		}

		/**
		 * Retrieves plugin settings.
		 *
		 * @param string $uid Setting's uid.
		 * @param int    $num Specifies position of option to retrieve.
		 * @return string Name of option.
		 */
		public function retrieve_option( $uid, $num ) {
			$retrieved = get_option( $uid, $this->get_slcb_fields( $num ) );
			return $retrieved[0];
		}

		/**
		 * Adds/removes wp-cache constant when the plugin is activated.
		 *
		 * @return void
		 */
		public function slcb_activation() {

		}
		/**
		 * Adds/removes wp-cache constant when the plugin is activated.
		 *
		 * @return void
		 */
		public function slcb_deactivation() {
			if ( file_exists( ABSPATH . 'wp-config.php' ) && is_writable( ABSPATH . 'wp-config.php' ) ) {
				add_cache_constant();
			} elseif ( file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && is_writable( dirname( ABSPATH ) . '/wp-config.php' ) ) {
				add_cache_constant( '/' );
			} else {
				$this->admin_error( $this->file_permissions_error() );
			}
		}

		/**
		 * Adds nocache_headers if enable in wp-admin options.
		 *
		 * @return void
		 */
		public function slcb_status_header() {
			nocache_headers();
			header( 'Cache-Control: public, s-maxage=0' );
			if ( ! defined( 'WP_CACHE' ) ) {
				define( 'WP_CACHE', false );
			}
		}
		/**
		 * Adds DONOTCACHEPAGE page directive.
		 *
		 * @return void
		 */
		public function hook_in_header() {
			if ( ! defined( 'DONOTCACHEPAGE' ) ) {
				define( 'DONOTCACHEPAGE', true );
			}
		}
		/**
		 * Adds DONOTCACHEPAGE page directive.
		 *
		 * @return void
		 */
		public function donotcachepage() {
			if ( headers_sent() || ! defined( 'DONOTCACHEPAGE' ) ) {
				return;
			}
			header( 'X-Cache-Enabled: False', true );
			header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
		}

		/**
		 * Builds the link used by the plugin to refresh the page.
		 *
		 * @param string  $uri The URI which was given in order to access the page.
		 * @param boolean $button Value determines the purpose of the link to build.
		 * @return string The link.
		 */
		private function build_refresh_link( $uri, $button = false ) {
			global $wp;
			$structure = get_option( 'permalink_structure' );
			$uri_parts = wp_parse_url( $uri );
			$uri_query = array();
			isset( $uri_parts['query'] ) && parse_str( $uri_parts['query'], $uri_query );
			// Remove slcb from the query string if it exists.
			if ( isset( $uri_query['slcb'] ) ) {
				unset( $uri_query['slcb'] );
			}
			$connector = false !== strpos( $uri, '?' ) && ( 0 < count( $uri_query ) ) ? '&' : '?';
			if ( '' === $structure ) {
				if ( isset( $uri_query['page_id'] ) ) {
					unset( $uri_query['page_id'] );
				}
				if ( isset( $uri_query['p'] ) ) {
					unset( $uri_query['p'] );
				}
				$url_suffix      = 0 < count( $uri_query ) ? '&' . http_build_query( $uri_query ) : http_build_query( $uri_query );
				$url_with_params = add_query_arg( $wp->query_vars, home_url( $wp->request ) ) . $url_suffix;
			} else {

				$url_suffix      = 1 > count( $uri_query ) ? http_build_query( $uri_query ) : '?' . http_build_query( $uri_query );
				$url_with_params = home_url( $wp->request ) . $url_suffix;

			}
			if ( $button ) {
				$new_uri = $url_with_params . $connector . 'slcb=randomize';
			} else {
				$new_uri = $url_with_params . $connector . 'slcb=' . wp_rand( 1000, 520000000 );
			}
			return $new_uri;
		}

		/**
		 * Creates a redirect in order to bypass caching.
		 *
		 * @return void
		 */
		public function redirect_to_uncached_resource() {

			$uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL );

			if ( str_contains( $uri, 'slcb=randomize' ) ) {
				wp_safe_redirect( $this->build_refresh_link( $uri ), 307, 'Super_Light_Cache_Buster' );
				exit;
			}
		}

		/**
		 * Displays cache status information in the admin bar.
		 *
		 * @param object $wp_admin_bar The WP_Admin_Bar instance, passed by reference.
		 * @return void
		 */
		public function slcb_buster_button( $wp_admin_bar ) {
			if ( ! is_admin() && current_user_can( 'manage_options' ) ) {
				global $wp;
				$intitial_args = array(
					'id'    => 'slcb-status',
					'title' => 'Cache Buster',
					'href'  => get_admin_url() . 'options-general.php?page=slcb_options',
					'meta'  => array(
						'class' => 'slcb-button',
					),
				);
				$request_uri   = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL );
				$refresh_args  = array(
					'id'     => 'slcb-refresh',
					'title'  => __( 'Refresh W/o Cache', 'super-light-cache-buster' ),
					'parent' => 'slcb-status',
					'href'   => $this->build_refresh_link( $request_uri, true ),
					'meta'   => array(
						'class' => 'slcb-button',
					),
				);
				if ( 'option1' === $this->randomizer_control[0] ) {
					$title = array(
						'title' => 'Cache Buster: On',
					);
				} else {
					$title = array(
						'title' => 'Cache Buster: Off',
					);
				}
				$args = array_insert( $intitial_args, $title, 1 );
				$wp_admin_bar->add_node( $args );
				$wp_admin_bar->add_node( $refresh_args );
			} else {
				return;
			}
		}
	}
}

if ( class_exists( 'Super_Light_Cache_Buster' ) ) {
	// Plugin uninstallation.
	register_uninstall_hook( __FILE__, 'Super_Light_Cache_Buster::uninstall_slcb' );
}
