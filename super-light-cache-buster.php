<?php
/*
 * @since             1.0.0
 * @package           Super_Light_Cache_Buster
 *
 * @wordpress-plugin
 * Plugin Name:       Super Light Cache Buster
 * Description:       Using less than 10 lines of code, this simple plugin adds random version numbers to CSS & JS assets to vanquish browser caching. Clear your Site and Server-side caches, and this plugin will do the rest.
 * Version:           1.0.1
 * Author:            Mwale Kalenga
 * Author URI:        https://mwale.me
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

class Super_Light_Cache_Buster {
    public $all_fields = array(
        array(
            'uid' => 'randomizer_setting_one',
            'label' => 'Enable/Disable Cache Buster',
            'section' => 'section_one',
            'type' => 'select',
            'helper' => 'When disabled your cache will work normally.',
            'options' => array(
                'option1' => 'Enable',
                'option2' => 'Disable',
            ),
            'default' => array('option1')
        ),
        array(
            'uid' => 'advanced_option',
            'label' => 'Enable/Disable No Cache Headers',
            'section' => 'section_two',
            'type' => 'select',
            'helper' => 'When enabled your pages will instruct browsers not to cache them.',
            'options' => array(
                'option1' => 'Enable',
                'option2' => 'Disable',
            ),
            'default' => array('option2')
        )
    );
	public function __construct() {
    	// Hook into the admin menu
    	add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );
        // Add Settings and Fields
    	add_action( 'admin_init', array( $this, 'setup_sections' ) );
    	add_action( 'admin_init', array( $this, 'setup_fields' ) );

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
                <h2>Super Light Cache Buster Settings</h2><?php /*
                if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ){
                    $this->admin_notice();
                }*/ ?>
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
    public function setup_sections() {
        add_settings_section( 'section_one', 'Set Cache Buster Status', array( $this, 'section_callback' ), 'slcb_fields' );
        add_settings_section( 'section_two', 'No Cache Header Setting', array( $this, 'section_callback' ), 'slcb_fields' );
    }
	public function section_callback( $arguments ) {
    	switch( $arguments['id'] ){
    		case 'section_one':
    			echo "You can completely disable Cache Buster when you're not using it. Then it will be 100% idle.";
				echo '<hr>';
    			break;
    		case 'section_two':
    			echo 'This will stop caching on the page in general.';
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
                        //echo var_dump($key), '</br>';
                        echo '<strong>$Value:</strong> ', var_dump($value);
                        //echo '<strong>$Key:</strong> ', var_dump($key);
                        $debug = array_search( $key, $value, true );
                        #echo '<strong>Array Search Result:</strong> ', var_dump($debug);
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
        /*if( $supplimental = $arguments['supplimental'] ){
            printf( '<p class="description">%s</p>', $supplimental );
        }*/
    }
    public function get_SLCB_fields($offset1, $offset2) {
        return( $this->all_fields[$offset1][$offset2] );
    }
}

new Super_Light_Cache_Buster();

$slcb_fields = new Super_Light_Cache_Buster();

$defaults = array (
    'randomizer_setting_one' => 'option2',
    'advanced_option' => 'option2'
);

$randomizer_control = get_option('randomizer_setting_one', $slcb_fields->get_SLCB_fields(0, 'default'));
$randomizer = get_option('randomizer_setting_one');
print_r($randomizer_control[0]);

if ( 'option1' == $randomizer_control[0] ) {

    // Randomize asset version for styles	
    add_filter( 'style_loader_src', 'slcb_randomize_ver', 9999 );

    // Randomize asset version for scripts	
    add_filter( 'script_loader_src', 'slcb_randomize_ver', 9999 );

}

$adv_option_control = get_option('advanced_option', $slcb_fields->get_SLCB_fields(0, 'default'));

if ( 'option1' == $adv_option_control[0] ) {

    // NoCache Header
    add_action( 'send_headers', 'slcb_status_header', 9999  );

    add_action ( 'wp_head', 'hook_inHeader' );

    add_action( 'template_redirect', 'donotcachepage', 9999 );

    add_action( 'template_redirect', 'slcb_redirect_to_referrer' );

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

/**
 * Redirect any items without query string
 * 
 * @return void
*/
function slcb_redirect_to_referrer() {
    
    if ( ! isset( $_GET, $_GET['cache'] ) ) {
        
        $location = "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
         
         wp_safe_redirect(
             add_query_arg( array(
                 'cache'        => 'bypass'
             ), get_permalink() )
         );
         
         exit();
         
     }
     
}

# Add button that allows users to turn cache buster on/off and also refresh the page w/o cache.
# A status button is also required to show whether or not a cached page has been served.

function slcb_buster_button($wp_admin_bar){
    $defaults = array (
        'randomizer_setting_one' => 'option2',
        'advanced_option' => 'option2'
    );
    if(! is_admin()) {
        $randomizer_control = wp_parse_args(get_option('randomizer_setting_one'), $defaults);
        $intitial_args = array(
            'id' => 'custom-button',
            'title' => 'Cache Buster',
            'href' => get_site_url() . '/wp-admin/options-general.php?page=slcb_options',
            'meta' => array(
                'class' => 'slcb-button'
            )
        );
        if ( 'option1' == $randomizer_control['randomizer_setting_one'] ) {
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
        print_r($randomizer_control['randomizer_setting_one']);
    }
    else {
        return;
    }
}
    
add_action('admin_bar_menu', 'slcb_buster_button', 50);

function array_insert($array,$values,$offset) {
    return array_slice($array, 0, $offset, true) + $values + array_slice($array, $offset+1, NULL, true);  
}

# Debugging

#echo "Enable/Disable Cache Buster:", "<pre>", var_dump($randomizer_control[0]), "</pre>";

#echo "Enable/Disable No Cache Headers:", "<pre>", var_dump($cache_header_control), "</pre>";

#var_dump($adv_option_control['randomizer_setting_one']);

#print_r($randomizer_control['randomizer_setting_one']);