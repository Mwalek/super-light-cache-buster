<?php
/**
 * Extends the main plugin class.
 *
 * @link https://github.com/Mwalek/super-light-cache-buster
 *
 * @package    WordPress
 * @subpackage Plugins
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The class responsible for the plugin's settings.
 */
class Super_Light_Cache_Buster_Settings {


	/**
	 * Setting that determines if advanced options should be in effect.
	 *
	 * @var array $adv_option_control The advanced options setting.
	 */
	public $adv_option_control;

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
				'uid'         => 'slcb_version_name',
				'label'       => __( 'Version Name', 'super-light-cache-buster' ),
				'section'     => 'section_two',
				'type'        => 'text',
				'placeholder' => '',
				'disabled'    => '',
				'default'     => '',
				'ancillary'   => '',
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
		// Hook into the admin menu.
		add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );
		// Add settings and fields.
		add_action( 'admin_init', array( $this, 'setup_sections' ) );
		add_action( 'admin_init', array( $this, 'setup_fields' ) );

		add_action( 'plugins_loaded', array( $this, 'super_light_cache_buster_load_textdomain' ) );

		add_action( 'admin_notices', array( $this, 'slcb_admin_notice' ), -1 );

		add_filter( 'plugin_action_links_super-light-cache-buster/super-light-cache-buster.php', array( $this, 'add_settings_page_link' ) );

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
				$text_html = sprintf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], sanitize_text_field( $value ) );
				echo wp_kses( $text_html, $this->allowed_html );
				break;
			case 'password':
			case 'number':
				$number_html = sprintf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], sanitize_text_field( $value ) );
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
			case 'slcb_version_name':
				$supplimental = __( 'When activated, this option will update your filenames only if you change the version name yourself, for instance, from 1.0.4 to 2.0.0. To have filenames updated automatically, just leave this field empty.', 'super-light-cache-buster' );
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

	/**
	 * Adds a link to SLCB settings page from the installed plugins page.
	 *
	 * @param array $links Existing SLCB plugin management links.
	 * @return array
	 */
	public function add_settings_page_link( array $links ) {
		$url           = get_admin_url() . 'options-general.php?page=slcb_options';
		$settings_link = '<a href="' . $url . '">' . __( 'Settings', 'super-light-cache-buster' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
}
