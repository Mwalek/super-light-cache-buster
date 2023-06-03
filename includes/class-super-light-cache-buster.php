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
	 * Setting that determines if assets are served with a random query string.
	 *
	 * @var array $randomizer_control The randomizer control setting.
	 */
	private $randomizer_control;

	/**
	 * Setting that determines if advanced options should be in effect.
	 *
	 * @var array $adv_option_control The advanced options setting.
	 */
	private $adv_option_control;

	/**
	 * Returns all fields for the plugin settings form.
	 */
	public static function all_fields() {
		return array(
			array(
				'uid'       => 'slcb_plugin_state',
				'label'     => __( 'Cache Buster Status', 'super-light-cache-buster' ),
				'section'   => 'section_one',
				'type'      => 'select',
				'options'   => array(
					'option1' => __( 'On', 'super-light-cache-buster' ),
					'option2' => __( 'Off', 'super-light-cache-buster' ),
				),
				'disabled'  => '',
				'default'   => array( 'option1' ),
				'ancillary' => '',
			),
			array(
				'uid'       => 'slcb_intensity_level',
				'label'     => __( 'Cache Buster Intensity', 'super-light-cache-buster' ),
				'section'   => 'section_two',
				'type'      => 'select',
				'options'   => array(
					'option1' => __( 'Normal', 'super-light-cache-buster' ),
					'option2' => __( 'Intense', 'super-light-cache-buster' ),
				),
				'disabled'  => '',
				'default'   => array( 'option2' ),
				'ancillary' => '',
			),
			array(
				'uid'       => 'slcb_wp_cache',
				'label'     => __( 'WP_CACHE', 'super-light-cache-buster' ),
				'section'   => 'section_two',
				'type'      => 'select',
				'options'   => array(
					'option1' => __( 'true', 'super-light-cache-buster' ),
					'option2' => __( 'false/unset', 'super-light-cache-buster' ),
				),
				'disabled'  => 'disabled',
				'default'   => array( 'option1' ),
				'ancillary' => '',
			),
		);
	}

	/**
	 * An array of allowed HTML elements and attributes.
	 *
	 * @var array
	 */
	public $allowed_html = array(
		'input'    => array(
			'name'        => array(),
			'id'          => array(),
			'type'        => array(),
			'placeholder' => array(),
			'value'       => array(),
		),
		'select'   => array(
			'name'     => array(),
			'id'       => array(),
			'disabled' => array(),
		),
		'option'   => array(
			'value'    => array(),
			'selected' => array(),

		),
		'textarea' => array(
			'name'        => array(),
			'id'          => array(),
			'placeholder' => array(),
		),
		'span'     => array(
			'class' => array(),
			'style' => array(),
		),
		'p'        => array(
			'class' => array(),
			'style' => array(),
		),
		'br'       => array(),
		'em'       => array(),
		'strong'   => array(),
		'fieldset' => array(),
		'hr'       => array(),

	);

	/**
	 * Houses error message shown when wp-config.php can't be modified.
	 */
	public function file_permissions_error() {
		return __( "Cache Buster failed to change the WP_CACHE setting. Make sure wp-config.php is <a href='https://wordpress.org/support/article/changing-file-permissions/'>writable</a>.", 'super-light-cache-buster' );
	}

	/**
	 * Initializes object's properties upon creation of the object.
	 */
	public function __construct() {
		$this->randomizer_control = get_option( 'slcb_plugin_state', $this->get_slcb_fields( 0 ) );
		$this->adv_option_control = get_option( 'slcb_intensity_level', $this->get_slcb_fields( 1 ) );
		// Hook into the admin menu.
		add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );
		// Add settings and fields.
		add_action( 'admin_init', array( $this, 'setup_sections' ) );
		add_action( 'admin_init', array( $this, 'setup_fields' ) );
		// Randomize asset version for styles.
		add_filter( 'style_loader_src', array( $this, 'slcb_randomize_ver' ), 9999 );

		add_action( 'template_redirect', array( $this, 'redirect_to_uncached_resource' ) );

		// Randomize asset version for scripts.
		add_filter( 'script_loader_src', array( $this, 'slcb_randomize_ver' ), 9999 );

		if ( 'option1' === $this->randomizer_control[0] && 'option2' === $this->adv_option_control[0] ) {

			add_action( 'send_headers', array( $this, 'slcb_status_header' ), 9999 );

			add_action( 'wp_head', array( $this, 'hook_in_header' ) );

			add_action( 'template_redirect', array( $this, 'donotcachepage' ), 9999 );

		}
		add_action( 'admin_bar_menu', array( $this, 'slcb_buster_button' ), 50 );

		add_action( 'plugins_loaded', array( $this, 'super_light_cache_buster_load_textdomain' ) );

		add_action( 'admin_notices', array( $this, 'slcb_admin_notice' ), -1 );

	}

	private function build_refresh_link( $uri, $button = false ) {
		global $wp;
		$structure = get_option( 'permalink_structure' );
		$uri_parts = wp_parse_url( $uri );
		$uri_query = array();
		isset( $uri_parts['query'] ) && parse_str( $uri_parts['query'], $uri_query );
		// Remove slcb from the query string if it exists.
		if ( isset( $uri_query['slcb'] ) ) {
			ray( $uri_query )->orange();
			unset( $uri_query['slcb'] );
		}
		ray( $uri_query )->orange();
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

	public function redirect_to_uncached_resource() {

		$uri = filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL );

		if ( str_contains( $uri, 'slcb=randomize' ) ) {
			wp_safe_redirect( $this->build_refresh_link( $uri ), 307, 'Super_Light_Cache_Buster' );
			exit;
		}
	}
	/**
	 * Creates plugin settings page.
	 *
	 * @return void
	 */
	public function create_plugin_settings_page() {
		// Add the menu item and page.
		$page_title = __( 'Super Light Cache Buster', 'super-light-cache-buster' );
		$menu_title = __( 'Cache Buster', 'super-light-cache-buster' );
		$capability = 'manage_options';
		$slug       = 'slcb_options';
		$callback   = array( $this, 'plugin_settings_page_content' );
		add_submenu_page( 'options-general.php', $page_title, $menu_title, $capability, $slug, $callback );
	}
	/**
	 * Creates plugin settings page content.
	 *
	 * @return void
	 */
	public function plugin_settings_page_content() {
		?>
		<div class="wrap">
			<div class="main_content">
				<h2><?php esc_html_e( 'Super Light Cache Buster Settings', 'super-light-cache-buster' ); ?></h2>
				<form method="POST" action="options.php">
					<?php
								settings_fields( 'slcb_fields' );
								do_settings_sections( 'slcb_fields' );
								submit_button();
					?>
				</form>
			</div>
		</div>
		<?php
	}
	/**
	 * Shows a success message when settings are saved.
	 *
	 * @return void
	 */
	public function admin_notice() {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Your settings have been updated!', 'super-light-cache-buster' ); ?></p>
		</div>
		<?php
	}
	/**
	 * Shows an error message when settings are not successfully saved.
	 *
	 * @param string $message Optional. Value used as error message. Default: 'An error occured'.
	 * @return void
	 */
	public function admin_error( $message = 'An error occured.' ) {
		?>
		<div class="notice notice-error">
			<p>
			<?php
			/* translators: Generic error message */
			$message = __( 'An error occured', 'super-light-cache-buster' );
			echo esc_html(
				sprintf(
				/* translators: %s: Error prefix */
					__( 'Error: %s', 'super-light-cache-buster' ),
					$message
				)
			);
			?>
			</p>
		</div>
		<?php
	}
	/**
	 * Sets up plugin settings sections.
	 *
	 * @return void
	 */
	public function setup_sections() {
		add_settings_section( 'section_one', __( 'Basic Settings', 'super-light-cache-buster' ), array( $this, 'section_callback' ), 'slcb_fields' );
		add_settings_section( 'section_two', __( 'Advanced Settings', 'super-light-cache-buster' ), array( $this, 'section_callback' ), 'slcb_fields' );
	}
	/**
	 * Echos out content at the top of sections.
	 *
	 * @param array $arguments Display arguments.
	 * @return void
	 */
	public function section_callback( $arguments ) {
		switch ( $arguments['id'] ) {
			case 'section_one':
				esc_html_e( "You can completely disable Cache Buster when you're not using it. Then it will be 100% idle.", 'super-light-cache-buster' );
				echo '<hr>';
				break;
			case 'section_two':
				esc_html_e( 'Settings in this section add more ways to prevent caching. The default settings are recommended for most sites.', 'super-light-cache-buster' );
				echo wp_kses( '<hr>', $this->allowed_html );
				break;
		}
	}
	/**
	 * Creates settings fields.
	 *
	 * @return void
	 */
	public function setup_fields() {
		$fields = self::all_fields();
		foreach ( $fields as $field ) {
			add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'slcb_fields', $field['section'], $field );
			register_setting( 'slcb_fields', $field['uid'] );
		}
	}
	/**
	 * Sets up settings fields
	 *
	 * @param array $arguments Display arguments.
	 * @return void
	 */
	public function field_callback( $arguments ) {
		$value = get_option( $arguments['uid'] );
		if ( ! $value ) {
			$value = $arguments['default'];
		}
		switch ( $arguments['type'] ) {
			case 'text':
			case 'password':
			case 'number':
				$number_html = sprintf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
				echo wp_kses( $number_html, $this->allowed_html );
				break;
			case 'textarea':
				$textarea_html = sprintf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], $value );
				echo wp_kses( $textarea_html, $this->allowed_html );
				break;
			case 'select':
			case 'multiselect':
				if ( ! empty( $arguments['options'] ) && is_array( $arguments['options'] ) ) {
					$attributes     = '';
					$options_markup = '';
					foreach ( $arguments['options'] as $key => $label ) {
						$options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $value[ array_search( $key, $value, true ) ], $key, false ), $label );
					}
					if ( 'multiselect' === $arguments['type'] ) {
						$attributes = ' multiple="multiple" ';
					}
					$multiselect_html = sprintf( '<select name="%1$s[]" id="%1$s" %2$s %3$s>%4$s</select>%5$s', $arguments['uid'], $attributes, $arguments['disabled'], $options_markup, $arguments['ancillary'] );
					echo wp_kses( $multiselect_html, $this->allowed_html );
				}
				break;
			case 'radio':
			case 'checkbox':
				if ( ! empty( $arguments['options'] ) && is_array( $arguments['options'] ) ) {
					$options_markup = '';
					$iterator       = 0;
					foreach ( $arguments['options'] as $key => $label ) {
						$iterator++;
						$options_markup .= sprintf( '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', $arguments['uid'], $arguments['type'], $key, checked( $value[ array_search( $key, $value, true ) ], $key, false ), $label, $iterator );
					}
					$checkbox_html = sprintf( '<fieldset>%s</fieldset>', $options_markup );
					echo wp_kses( $checkbox_html, $this->allowed_html );
				}
				break;
		}

		/**
		 * Add localized settings text.
		 */
		switch ( $arguments['uid'] ) {
			case 'slcb_plugin_state':
				$supplimental = __( '<strong>Pro tip: </strong>You may need to clear your cache before Cache Buster can prevent future caching.', 'super-light-cache-buster' );
				$helper       = '';
				break;
			case 'slcb_intensity_level':
				$supplimental = __( 'Add/remove no-cache directives to/from the Cache-Control HTTP header field. This setting will only work if the Cache Buster Status is \'On\'.', 'super-light-cache-buster' );
				$helper       = '';
				break;
			case 'slcb_wp_cache':
				$supplimental = __( 'Coming Soon: \'false\' removes the WP_CACHE constant, \'true\' adds WP_CACHE back and sets it to \'true\'. This setting will only work if the Cache Buster Status is \'On\'.', 'super-light-cache-buster' );
				$helper       = '';
				break;

		}
		$supplimental_html = sprintf( '<p class="description" style="font-style: italic; max-width: 300px;">%s</p>', $supplimental );
		echo wp_kses( $supplimental_html, $this->allowed_html );
		$helper_html = sprintf( '<span class="helper"> %s</span>', $helper );
		echo wp_kses( $helper_html, $this->allowed_html );
	}
	/**
	 * Gets settings fields helper function.
	 *
	 * @param string $offset1 Position of field in array returned by all_fields function.
	 * @param string $offset2 Optional. Name of option to retrieve.
	 * @return array A particular option of the specificed field.
	 */
	public function get_slcb_fields( $offset1, $offset2 = 'default' ) {
		$fields_array = self::all_fields();
		return( $fields_array[ $offset1 ][ $offset2 ] );
	}
	/**
	 * Gets field uids.
	 *
	 * @return void
	 */
	public function get_slcb_uids() {
		$fields_array = self::all_fields();
		$uid          = $fields_arrays[0]['uid'];
	}
	/**
	 * Deletes options when the plugin is uninstalled.
	 *
	 * @return void
	 */
	public static function uninstall_slcb() {
		$uids         = array();
		$fields_array = self::all_fields();
		foreach ( $fields_array as $array ) {
			$uids[] = $array['uid'];
		}
		$setting_options = $uids;
		foreach ( $setting_options as $setting_name ) {
			delete_option( $setting_name );
		}
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
	 * Sets cache prevention status.
	 *
	 * @return void
	 */
	public function set_wp_cache() {
		if ( ( 'option1' === $this->retrieve_option( 'slcb_plugin_state', 0 ) ) && ( 'option2' === $this->retrieve_option( 'slcb_wp_cache', 2 ) ) ) {
			$this->slcb_activation();
		} elseif ( 'option2' === $this->retrieve_option( 'slcb_plugin_state', 0 ) ) {
			$this->slcb_deactivation();
		} elseif ( 'option1' === $this->retrieve_option( 'slcb_wp_cache', 2 ) ) {
			$this->slcb_deactivation();
		}
	}
	/**
	 * Adds/removes wp-cache constant when the plugin is activated.
	 *
	 * @return void
	 */
	public function slcb_activation() {
		if ( 'option2' === $this->retrieve_option( 'slcb_wp_cache', 2 ) ) {
			if ( file_exists( ABSPATH . 'wp-config.php' ) && is_writable( ABSPATH . 'wp-config.php' ) ) {
				remove_cache_constant();
			} elseif ( file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && is_writable( dirname( ABSPATH ) . '/wp-config.php' ) ) {
				remove_cache_constant( '/' );
			} elseif ( file_exists( ABSPATH . 'wp-config.php' ) && ! is_writable( ABSPATH . 'wp-config.php' ) ) {
				$this->admin_error( $this->file_permissions_error() );
			} elseif ( file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && ! is_writable( dirname( ABSPATH ) . '/wp-config.php' ) ) {
				$this->admin_error( $this->file_permissions_error() );
			} else {
				$this->admin_error( $this->file_permissions_error() );
			}
		}
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
	 * Randomizes version numbers.
	 *
	 * @param string $src The source URL of the enqueued style/script.
	 * @return string The randomized version of the URL.
	 */
	public function slcb_randomize_ver( $src ) {
		$allow_in_backend = apply_filters( 'slcb_allow_in_backend', false );
		if ( ( ! is_admin() || $allow_in_backend ) && 'option1' === $this->randomizer_control[0] ) {
			$random_number = wp_rand( 1000, 520000000 );
			$src           = esc_url( add_query_arg( 'ver', $random_number, $src ) );
			return $src;
		}
		return $src;
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
				'title'  => 'Refresh W/o Cache',
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


	/**
	 * Adds cache constant.
	 *
	 * @param string $slash Optional.
	 * @return void
	 */
	public function add_cache_constant( $slash = '' ) {
		$config = file_get_contents( ABSPATH . 'wp-config.php' );
		if ( strstr( $config, " < ? php define( 'WP_CACHE', true )" ) ) {
			return;
		} else {
			$config = preg_replace( " / ^ ( array( \r\n\t ) * )( \ < \ ? )( php ) ? / i', ' < ? php define( 'WP_CACHE', true );", $config );
		}
		file_put_contents( ABSPATH . $slash . 'wp-config.php', $config );
	}

	/**
	 * Removes cache constant.
	 *
	 * @param string $slash Optional.
	 * @return void
	 */
	public function remove_cache_constant( $slash = '' ) {
		$config = file_get_contents( ABSPATH . 'wp-config.php' );
		$config = preg_replace( "/( ?)(define)( ?)(\()( ?)(['\"])WP_CACHE(['\"])( ?)(,)( ?)(0|1|true|false)( ?)(\))( ?);/i", '', $config );
		file_put_contents( ABSPATH . $slash . 'wp-config.php', $config );
	}

	/**
	 * Declares the plugin text domain and languages directory.
	 *
	 * @return void
	 */
	public function super_light_cache_buster_load_textdomain() {
		load_plugin_textdomain( 'super-light-cache-buster', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Urges users to clear their cache after updating settings.
	 *
	 * @return void
	 */
	public function slcb_admin_notice() {

		$screen = get_current_screen();

		if ( 'settings_page_slcb_options' === $screen->id ) {
			// phpcs:ignore
			if ( isset( $_GET['settings-updated'] ) ) {
				// phpcs:ignore
				if ( 'true' === $_GET['settings-updated'] ) {
					?>
				<div class="notice notice-info notice-slcb is-dismissible">
					<p><?php esc_html_e( 'Please clear your cache to make sure your new settings take effect.', 'super-light-cache-buster' ); ?></p>
				</div>
					<?php
				}
			}
		}
	}
}

if ( class_exists( 'Super_Light_Cache_Buster' ) ) {
	// Plugin uninstallation.
	register_uninstall_hook( __FILE__, 'Super_Light_Cache_Buster::uninstall_slcb' );
}
