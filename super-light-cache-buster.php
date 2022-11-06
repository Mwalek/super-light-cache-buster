<?php
/**
 * Core plugin class
 *
 * @since             1.0.0
 * @package           Super_Light_Cache_Buster
 *
 * @wordpress-plugin
 * Plugin Name:       Super Light Cache Buster
 * Description:       With a compressed size of under 10KB, this simple plugin adds random version numbers to CSS & JS assets to vanquish browser caching. Clear your Site and Server-side caches, and this plugin will do the rest.
 * Version:           1.1.2
 * Author:            Mwale Kalenga
 * Author URI:        https://mwale.me
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.en.html
 */

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
	 * Contains all fields for the plugin settings form.
	 *
	 * @var array $all_fields
	 */
	public static $all_fields = array(
		array(
			'uid'          => 'slcb_plugin_state',
			'label'        => 'Cache Buster Status',
			'section'      => 'section_one',
			'type'         => 'select',
			'helper'       => '',
			'supplimental' => '<strong>Pro tip: </strong>You may need to clear your cache before Cache Buster can prevent future caching.',
			'options'      => array(
				'option1' => 'On',
				'option2' => 'Off',
			),
			'disabled'     => '',
			'default'      => array( 'option1' ),
			'ancillary'    => '',
		),
		array(
			'uid'          => 'slcb_intensity_level',
			'label'        => 'Cache Buster Intensity',
			'section'      => 'section_two',
			'type'         => 'select',
			'helper'       => '',
			'supplimental' => 'Add/remove no-cache directives to/from the Cache-Control HTTP header field. This setting will only work if the Cache Buster Status is \'On\'.',
			'options'      => array(
				'option1' => 'Normal',
				'option2' => 'Intense',
			),
			'disabled'     => '',
			'default'      => array( 'option1' ),
			'ancillary'    => '',
		),
		array(
			'uid'          => 'slcb_wp_cache',
			'label'        => 'WP_CACHE',
			'section'      => 'section_two',
			'type'         => 'select',
			'helper'       => '',
			'supplimental' => 'Coming Soon: \'false\' removes the WP_CACHE constant, \'true\' adds WP_CACHE back and sets it to \'true\'. This setting will only work if the Cache Buster Status is \'On\'.',
			'options'      => array(
				'option1' => 'true',
				'option2' => 'false/unset',
			),
			'disabled'     => 'disabled',
			'default'      => array( 'option1' ),
			'ancillary'    => '',
		),
	);
	/**
	 * Error message when wp-config.php can't be modified.
	 *
	 * @var string $file_permissions_error
	 */
	public $file_permissions_error = "Cache Buster failed to change the WP_CACHE setting. Make sure wp-config.php is <a href='https://wordpress.org/support/article/changing-file-permissions/'>writable</a>.";
	/**
	 * Initializes object's properties upon creation of the object.
	 */
	public function __construct() {
		// Hook into the admin menu.
		add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );
		// Add settings and fields.
		add_action( 'admin_init', array( $this, 'setup_sections' ) );
		add_action( 'admin_init', array( $this, 'setup_fields' ) );
	}
	/**
	 * Creates plugin settings page.
	 *
	 * @return void
	 */
	public function create_plugin_settings_page() {
		// Add the menu item and page.
		$page_title = 'Super Light Cache Buster';
		$menu_title = 'Cache Buster';
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
		<h2>Super Light Cache Buster Settings</h2>
		<form method="POST" action="options.php">
			<?php
						settings_fields( 'slcb_fields' );
						do_settings_sections( 'slcb_fields' );
						submit_button();
			?>
		</form>
		<div>
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
			<p>Your settings have been updated!</p>
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
		add_settings_section( 'section_one', 'Basic Settings', array( $this, 'section_callback' ), 'slcb_fields' );
		add_settings_section( 'section_two', 'Advanced Settings', array( $this, 'section_callback' ), 'slcb_fields' );
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
				echo "You can completely disable Cache Buster when you're not using it . Then it will be 100 % idle . ";
				echo '<hr>';
				break;
			case 'section_two':
				echo 'Settings in this section add more ways to prevent caching. The default settings are recommended for most sites.';
				echo '<hr>';
				break;
		}
	}
	/**
	 * Creates settings fields.
	 *
	 * @return void
	 */
	public function setup_fields() {
		$fields = self::$all_fields;
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
		$value        = get_option( $arguments['uid'] );
		$allowed_html = array(
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
		);
		if ( ! $value ) {
			$value = $arguments['default'];
		}
		switch ( $arguments['type'] ) {
			case 'text':
			case 'password':
			case 'number':
				$number_html = sprintf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
				echo wp_kses( $number_html, $allowed_html );
				break;
			case 'textarea':
				$textarea_html = sprintf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], $value );
				echo wp_kses( $textarea_html, $allowed_html );
				break;
			case 'select':
			case 'multiselect':
				if ( ! empty( $arguments['options'] ) && is_array( $arguments['options'] ) ) {
					$attributes     = '';
					$options_markup = '';
					foreach ( $arguments['options'] as $key => $label ) {
						$options_markup .= sprintf( '<option value=" % s" %s>%s</option>', $key, selected( $value[ array_search( $key, $value, true ) ], $key, false ), $label );
					}
					if ( 'multiselect' === $arguments['type'] ) {
						$attributes = ' multiple="multiple" ';
					}
					$multiselect_html = sprintf( '<select name="%1$s[]" id="%1$s" %2$s %3$s>%4$s</select>%5$s', $arguments['uid'], $attributes, $arguments['disabled'], $options_markup, $arguments['ancillary'] );
					echo wp_kses( $multiselect_html, $allowed_html );
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
					echo wp_kses( $checkbox_html, $allowed_html );
				}
				break;
		}
		if ( $helper = $arguments['helper'] ) {
			$helper_html = sprintf( '<span class="helper"> %s</span>', $helper );
			echo wp_kses( $helper_html, $allowed_html );
		}
		if ( $supplimental = $arguments['supplimental'] ) {
			$supplimental_html = sprintf( '<p class="description" style="font-style: italic; max-width: 300px;">%s</p>', $supplimental );
			echo wp_kses( $supplimental_html, $allowed_html );
		}
	}
	/**
	 * Gets settings fields helper function.
	 *
	 * @param string $offset1 Position of field in $all_fields array.
	 * @param string $offset2 Optional. Name of option to retrieve.
	 * @return array A particular option of the specificed field.
	 */
	public function get_slcb_fields( $offset1, $offset2 = 'default' ) {
		return( self::$all_fields[ $offset1 ][ $offset2 ] );
	}
	/**
	 * Gets field uids.
	 *
	 * @return void
	 */
	public function get_slcb_uids() {
		$uid = self::$all_fields[0]['uid'];
	}
	/**
	 * Deletes options when the plugin is uninstalled.
	 *
	 * @return void
	 */
	public static function uninstall_slcb() {
		$uids = array();
		foreach ( self::$all_fields as $array ) {
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
				$this->admin_error( $this->file_permissions_error );
			} elseif ( file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && ! is_writable( dirname( ABSPATH ) . '/wp-config.php' ) ) {
				$this->admin_error( $this->file_permissions_error );
			} else {
				$this->admin_error( $this->file_permissions_error );
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
			$this->admin_error( $this->file_permissions_error );
		}
	}
}

if ( class_exists( 'Super_Light_Cache_Buster' ) ) {
	// Plugin uninstallation.
	register_uninstall_hook( __FILE__, 'Super_Light_Cache_Buster::uninstall_slcb' );
}

$slcb_fields = new Super_Light_Cache_Buster();

$randomizer_control = get_option( 'slcb_plugin_state', $slcb_fields->get_slcb_fields( 0 ) );

if ( ! is_admin() && 'option1' === $randomizer_control[0] ) {

	// Randomize asset version for styles.
	add_filter( 'style_loader_src', 'slcb_randomize_ver', 9999 );

	// Randomize asset version for scripts.
	add_filter( 'script_loader_src', 'slcb_randomize_ver', 9999 );

}

$adv_option_control = get_option( 'slcb_intensity_level', $slcb_fields->get_slcb_fields( 1 ) );

if ( 'option1' === $randomizer_control[0] && 'option2' === $adv_option_control[0] ) {

	add_action( 'send_headers', 'slcb_status_header', 9999 );

	add_action( 'wp_head', 'hook_in_header' );

	add_action( 'template_redirect', 'donotcachepage', 9999 );

}

/**
 * Randomizes version numbers.
 *
 * @param string $src The source URL of the enqueued style/script.
 * @return string The randomized version of the URL.
 */
function slcb_randomize_ver( $src ) {
	$random_number = wp_rand( 1000, 520000000 );
	$src           = esc_url( add_query_arg( 'ver', $random_number, $src ) );
	return $src;
}

/**
 * Adds nocache_headers if enable in wp-admin options.
 *
 * @return void
 */
function slcb_status_header() {
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
function hook_in_header() {
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}
}
/**
 * Adds DONOTCACHEPAGE page directive.
 *
 * @return void
 */
function donotcachepage() {
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
function slcb_buster_button( $wp_admin_bar ) {
	$slcb_fields        = new Super_Light_Cache_Buster();
	$randomizer_control = get_option( 'slcb_plugin_state', $slcb_fields->get_slcb_fields( 0 ) );
	if ( ! is_admin() && current_user_can( 'manage_options' ) ) {
		$intitial_args = array(
			'id'    => 'custom-button',
			'title' => 'Cache Buster',
			'href'  => get_admin_url() . 'options-general.php?page=slcb_options',
			'meta'  => array(
				'class' => 'slcb-button',
			),
		);
		if ( 'option1' === $randomizer_control[0] ) {
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
	} else {
		return;
	}
}

add_action( 'admin_bar_menu', 'slcb_buster_button', 50 );
/**
 * Array insertion helper function.
 *
 * @param array $array The array in which to insert.
 * @param array $values The values to insert into the array.
 * @param int   $offset Specifies array offset position.
 * @return array The modified array.
 */
function array_insert( $array, $values, $offset ) {
	return array_slice( $array, 0, $offset, true ) + $values + array_slice( $array, $offset + 1, null, true );
}

if ( ! function_exists( 'add_cache_constant' ) ) {
	/**
	 * Adds cache constant.
	 *
	 * @param string $slash Optional.
	 * @return void
	 */
	function add_cache_constant( $slash = '' ) {
		$config = file_get_contents( ABSPATH . 'wp-config.php' );
		if ( strstr( $config, " < ? php define( 'WP_CACHE', true )" ) ) {
			return;
		} else {
			$config = preg_replace( " / ^ ( array( \r\n\t ) * )( \ < \ ? )( php ) ? / i', ' < ? php define( 'WP_CACHE', true );", $config );
		}
		file_put_contents( ABSPATH . $slash . 'wp-config.php', $config );
	}
}

if ( ! function_exists( 'remove_cache_constant' ) ) {
	/**
	 * Removes cache constant.
	 *
	 * @param string $slash Optional.
	 * @return void
	 */
	function remove_cache_constant( $slash = '' ) {
		$config = file_get_contents( ABSPATH . 'wp-config.php' );
		$config = preg_replace( "/( ?)(define)( ?)(\()( ?)(['\"])WP_CACHE(['\"])( ?)(,)( ?)(0|1|true|false)( ?)(\))( ?);/i", '', $config );
		file_put_contents( ABSPATH . $slash . 'wp-config.php', $config );
	}
}
