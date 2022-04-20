<?php
/*
 * @since             1.1.0
 * @package           Super_Light_Cache_Buster
 *
 * @wordpress-plugin
 * Plugin Name:       Super Light Cache Buster
 * Description:       With a compressed size of under 250KB, this simple plugin adds random version numbers to CSS & JS assets to vanquish browser caching. Clear your Site and Server-side caches, and this plugin will do the rest.
 * Version:           1.0.1
 * Author:            Mwale Kalenga
 * Author URI:        https://mwale.me
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.en.html
 */

class Super_Light_Cache_Buster {
    public $all_fields = array(
        array(
            'uid' => 'slcb_plugin_state',
            'label' => 'Cache Buster Status',
            'section' => 'section_one',
            'type' => 'select',
            'helper' => 'When \'Off\' your cache will work normally.',
            'supplimental' => '',
            'options' => array(
                'option1' => 'On',
                'option2' => 'Off',
            ),
            'default' => array('option1')
        ),
        array(
            'uid' => 'slcb_intensity_level',
            'label' => 'Cache Buster Intensity',
            'section' => 'section_two',
            'type' => 'select',
            'helper' => 'Intensity only works if the Cache Buster Status is \'On\'.',
            'supplimental' => '',
            'options' => array(
                'option1' => 'Normal',
                'option2' => 'Intense',
            ),
            'default' => array('option1')
        ),
        array(
            'uid' => 'slcb_wp_cache',
            'label' => 'WP_CACHE',
            'section' => 'section_two',
            'type' => 'select',
            'helper' => '\'false\' removes the WP_CACHE constant, \'true\' adds WP_CACHE back and sets it to \'true\'.',
            'supplimental' => 'This setting will only work if the Cache Buster Status is \'On\'.',
            'options' => array(
                'option1' => 'false',
                'option2' => 'true',
            ),
            'default' => array('option1')
        )
    );
    public $file_permissions_error = "Cache Buster failed to change the WP_CACHE setting. Make sure wp-config.php is <a href='https://wordpress.org/support/article/changing-file-permissions/'>writable</a>.";
	public function __construct() {
    	// Hook into the admin menu
    	add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );
        // Add Settings and Fields
    	add_action( 'admin_init', array( $this, 'setup_sections' ) );
    	add_action( 'admin_init', array( $this, 'setup_fields' ) );
        // plugin activation
        register_activation_hook( __FILE__, array( $this, 'slcb_activation') );
        // plugin deactivation
        register_deactivation_hook( __FILE__, array( $this, 'slcb_deactivation') );
        // plugin uninstallation
        register_uninstall_hook( __FILE__, 'slcb_uninstaller' );
    }
	public function create_plugin_settings_page() {
    	// Add the menu item and page
    	$page_title = 'Super Light Cache Buster';
    	$menu_title = 'Cache Buster';
    	$capability = 'manage_options';
    	$slug = 'slcb_options';
    	$callback = array( $this, 'plugin_settings_page_content' );
		add_submenu_page( 'options-general.php', $page_title, $menu_title, $capability, $slug, $callback );
    }
	public function plugin_settings_page_content() {?>
    	<div class="wrap">
            <diV class="main_content">
                <h2>Super Light Cache Buster Settings</h2><?php 
                if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ){
                    $this->setWpCache();
                } ?>
                <form method="POST" action="options.php">
                    <?php
                        settings_fields( 'slcb_fields' );
                        do_settings_sections( 'slcb_fields' );
                        submit_button();
                    ?>
                </form>
            <diV>
    	</div> <?php
    }
	public function admin_notice() { ?>
        <div class="notice notice-success is-dismissible">
            <p>Your settings have been updated!</p>
        </div><?php
    }
    public function admin_error($message = "An error occured.") { ?>
        <div class="notice notice-error">
            <p><?php echo "Error: ", $message; ?></p>
        </div><?php
    }
    public function setup_sections() {
        add_settings_section( 'section_one', 'Basic Settings', array( $this, 'section_callback' ), 'slcb_fields' );
        add_settings_section( 'section_two', 'Advanced Settings', array( $this, 'section_callback' ), 'slcb_fields' );
    }
	public function section_callback( $arguments ) {
    	switch( $arguments['id'] ){
    		case 'section_one':
    			echo "You can completely disable Cache Buster when you're not using it. Then it will be 100% idle.";
				echo '<hr>';
    			break;
    		case 'section_two':
    			echo 'Settings in this section add more ways to prevent caching. The default settings are recommended for most sites.';
				echo '<hr>';
    			break;
    	}
    }
	public function setup_fields() {
        $fields = $this->all_fields;
    	foreach( $fields as $field ){
        	add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'slcb_fields', $field['section'], $field );
            register_setting( 'slcb_fields', $field['uid'] );
    	}
    }
	public function field_callback( $arguments ) {
        $value = get_option( $arguments['uid'] );
        if( ! $value ) {
            $value = $arguments['default'];
        }
        switch( $arguments['type'] ){
            case 'text':
            case 'password':
            case 'number':
                printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
                break;
            case 'textarea':
                printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], $value );
                break;
            case 'select':
            case 'multiselect':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $attributes = '';
                    $options_markup = '';
                    foreach( $arguments['options'] as $key => $label ){
                        $options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $value[ array_search( $key, $value, true ) ], $key, false ), $label );
                    }
                    if( $arguments['type'] === 'multiselect' ){
                        $attributes = ' multiple="multiple" ';
                    }
                    printf( '<select name="%1$s[]" id="%1$s" %2$s>%3$s</select>', $arguments['uid'], $attributes, $options_markup );
                }
                break;
            case 'radio':
            case 'checkbox':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $options_markup = '';
                    $iterator = 0;
                    foreach( $arguments['options'] as $key => $label ){
                        $iterator++;
                        $options_markup .= sprintf( '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', $arguments['uid'], $arguments['type'], $key, checked( $value[ array_search( $key, $value, true ) ], $key, false ), $label, $iterator );
                    }
                    printf( '<fieldset>%s</fieldset>', $options_markup );
                }
                break;
        }
        if( $helper = $arguments['helper'] ){
            printf( '<span class="helper"> %s</span>', $helper );
        }
        if( $supplimental = $arguments['supplimental'] ){
            printf( '<p class="description">%s</p>', $supplimental );
        }
    }
    public function get_SLCB_fields($offset1, $offset2 = 'default') {
        return( $this->all_fields[$offset1][$offset2] );
    }
    public function get_SLCB_uids() {
        $uid = $this->all_fields[0]['uid'];
        print_r ($uid);
    }
    public function uninstall_SLCB () {
        $uids = array();
        foreach($this->all_fields as $array) {
            $uids[] = $array['uid'];
        } # Potential alternative to foreach - $options = implode(", ", $uids);
        $settingOptions = $uids;
        foreach ( $settingOptions as $settingName ) {
            delete_option( $settingName );
        }
    }
    public function retrieve_option($uid, $num) {
        $retrieved = get_option( $uid, $this->get_SLCB_fields($num));
        return $retrieved[0];
    }
    public function setWpCache() {
        if ( ('option1' == $this->retrieve_option('slcb_plugin_state', 0)) && ('option1' == $this->retrieve_option('slcb_wp_cache', 2)) ) {
            $this->slcb_activation();
        } else if ( 'option2' == $this->retrieve_option('slcb_plugin_state', 0) ) {
            $this->slcb_deactivation();
        } else if ( 'option2' == $this->retrieve_option('slcb_wp_cache', 2) ) {
            $this->slcb_deactivation();
        }
    }
    public function slcb_activation() {
        if ( 'option1' == $this->retrieve_option('slcb_wp_cache', 2) ) {        
            if (file_exists (ABSPATH . "wp-config.php") && is_writable (ABSPATH . "wp-config.php")) {
                remove_cache_constant();
            }
            else if (file_exists (dirname (ABSPATH) . "/wp-config.php") && is_writable (dirname (ABSPATH) . "/wp-config.php")) {
                remove_cache_constant('/');
            }
            else if (file_exists (ABSPATH . "wp-config.php") && !is_writable (ABSPATH . "wp-config.php")) {
                $this->admin_error($this->file_permissions_error);
            }
            else if (file_exists (dirname (ABSPATH) . "/wp-config.php") && !is_writable (dirname (ABSPATH) . "/wp-config.php")) {
                $this->admin_error($this->file_permissions_error);
            }
            else {
                $this->admin_error($this->file_permissions_error);
            }
        }
    }
    public function slcb_deactivation() {
        if ( file_exists (ABSPATH . "wp-config.php") && is_writable (ABSPATH . "wp-config.php") ){
            add_cache_constant();
        }
        else if (file_exists (dirname (ABSPATH) . "/wp-config.php") && is_writable (dirname (ABSPATH) . "/wp-config.php")){
            add_cache_constant('/');
        }
        else { 
            $this->admin_error($this->file_permissions_error);
        }
    }
    public function slcb_uninstaller() {
        $this->uninstall_SLCB();
    }
}

$slcb_fields = new Super_Light_Cache_Buster();

$randomizer_control = get_option('slcb_plugin_state', $slcb_fields->get_SLCB_fields(0));

if ( 'option1' == $randomizer_control[0] ) {

    // Randomize asset version for styles	
    add_filter( 'style_loader_src', 'slcb_randomize_ver', 9999 );

    // Randomize asset version for scripts	
    add_filter( 'script_loader_src', 'slcb_randomize_ver', 9999 );

}

$adv_option_control = get_option('slcb_intensity_level', $slcb_fields->get_SLCB_fields(1));

if ( 'option1' == $randomizer_control[0] && 'option2' == $adv_option_control[0] ) {

    // NoCache Header
    add_action( 'send_headers', 'slcb_status_header', 9999  );

    add_action ( 'wp_head', 'hook_inHeader' );

    add_action( 'template_redirect', 'donotcachepage', 9999 );

}

// Randomize version numbers
function slcb_randomize_ver( $src ) {
    $random_number = wp_rand( 1000, 520000000 );
    $src = esc_url( add_query_arg( 'ver', $random_number, $src ) );
    return $src;
}

// Add nocache_headers if enable in wp-admin options

function slcb_status_header() {
    nocache_headers();
    header("Cache-Control: public, s-maxage=0");
    if ( !defined('WP_CACHE') ) {
        define('WP_CACHE', false);
    }
}

function hook_inHeader() {
    if ( !defined('DONOTCACHEPAGE') )
        define('DONOTCACHEPAGE', true);
}

function donotcachepage() {
	if ( headers_sent() || ! defined( 'DONOTCACHEPAGE' ) ) {
		return;
	}
	header( 'X-Cache-Enabled: False', true );
	header("Cache-Control: max-age=0, must-revalidate");
}

function slcb_buster_button($wp_admin_bar){
    $slcb_fields = new Super_Light_Cache_Buster();
    $randomizer_control = get_option('slcb_plugin_state', $slcb_fields->get_SLCB_fields(0));
    if(! is_admin()) {
        $intitial_args = array(
            'id' => 'custom-button',
            'title' => 'Cache Buster',
            'href' => get_admin_url() . 'options-general.php?page=slcb_options',
            'meta' => array(
                'class' => 'slcb-button'
            )
        );
        if ( 'option1' == $randomizer_control[0] ) {
            $title = array(
                'title' => 'Cache Buster: On'
            );
        } else {
            $title = array(
                'title' => 'Cache Buster: Off'
            );
        }
        $args = array_insert($intitial_args, $title, 1);
        $wp_admin_bar->add_node($args);
    }
    else {
        return;
    }
}
    
add_action('admin_bar_menu', 'slcb_buster_button', 50);

function array_insert($array,$values,$offset) {
    return array_slice($array, 0, $offset, true) + $values + array_slice($array, $offset+1, NULL, true);  
}

if ( !function_exists( 'add_cache_constant' ) ) {
    function add_cache_constant( $slash = '' ) {
        $config = file_get_contents (ABSPATH . "wp-config.php");
        if( strstr($config, "<?php define('WP_CACHE', true)") ) {
            return;
        } else {
            $config = preg_replace ("/^([\r\n\t ]*)(\<\?)(php)?/i", "<?php define('WP_CACHE', true);", $config);
        }
        file_put_contents (ABSPATH . $slash . "wp-config.php", $config);
    }
}

if ( !function_exists( 'remove_cache_constant' ) ) {
    function remove_cache_constant( $slash = '' ) {
        $config = file_get_contents (ABSPATH . "wp-config.php");
        $config = preg_replace ("/( ?)(define)( ?)(\()( ?)(['\"])WP_CACHE(['\"])( ?)(,)( ?)(0|1|true|false)( ?)(\))( ?);/i", "", $config);
        file_put_contents (ABSPATH . $slash . "wp-config.php", $config);
    }
}
