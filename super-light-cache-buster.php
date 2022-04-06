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
	public function __construct() {
    	// Hook into the admin menu
    	add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );
        // Add Settings and Fields
    	add_action( 'admin_init', array( $this, 'setup_sections' ) );
    	add_action( 'admin_init', array( $this, 'setup_fields' ) );
		/*add_filter( 'register', 'sll_register_link' );
		add_action('login_head', 'control_logo_settings');*/
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
        $fields = array(
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
                'default' => array()
        	),
			array(
        		'uid' => 'cache_header_one',
        		'label' => 'Enable/Disable No Cache Headers',
        		'section' => 'section_two',
        		'type' => 'select',
				'helper' => 'When enabled your pages will instruct browsers not to cache them.',
        		'options' => array(
        			'option1' => 'Enable',
        			'option2' => 'Disable',
        		),
                'default' => array()
        	)
        );
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
        /*if( $supplimental = $arguments['supplimental'] ){
            printf( '<p class="description">%s</p>', $supplimental );
        }*/
    }
}

new Super_Light_Cache_Buster();

$randomizer_control = get_option('randomizer_setting_one');

if ( 'option1' == $randomizer_control[0] ) {

    // Randomize asset version for styles	
    add_filter( 'style_loader_src', 'slcb_randomize_ver', 9999 );

    // Randomize asset version for scripts	
    add_filter( 'script_loader_src', 'slcb_randomize_ver', 9999 );

}

$cache_header_control = get_option('cache_header_one');

if ( 'option1' == $cache_header_control[0] ) {

    // NoCache Header
    add_action( 'send_headers', 'slcb_status_header', 9999  );

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
    header("Cache-Control: public, s-maxage=120");
    if ( !defined('WP_CACHE') ) {
        define('WP_CACHE', false);
    }
}

/*add_action( 'template_redirect', array( $this, 'donotcachepage' ), 9999 );

public function donotcachepage() {
	if ( headers_sent() || ! defined( 'DONOTCACHEPAGE' ) ) {
		return;
	}

	header( 'X-Cache-Enabled: False', true );
	header("Cache-Control: max-age=0, must-revalidate");
}*/

add_action ( 'wp_head', 'hook_inHeader' );
function hook_inHeader() {
        // Get the post id using the get_the_ID(); function:
        define('DONOTCACHEPAGE', true);
}

/* function wprdcv_param_redirect(){
if( !is_admin() && !isset($_GET['cache']) ){
    $location = "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    $location .= utf8_decode('?cache=bypass');
    wp_redirect( $location );
}
}

add_action('template_redirect', 'wprdcv_param_redirect');*/

/**
 * Redirect any items without query string
 * 
 * @return void
*/
/*function wpse375877_redirect_to_referrer() {
    
   if ( ! isset( $_GET, $_GET['rfd'], $_GET['dfr'] ) ) {
	   
	   $location = "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
        
        wp_safe_redirect(
            add_query_arg( array(
                'rfd'        => 'off',
                'dfr'  => 'none'
            ), $location )
        );
		
		// wp_safe_redirect( get_permalink().'?page=1'); exit;
        exit();
        
    }
    
}

add_action( 'template_redirect', 'wpse375877_redirect_to_referrer' ); */

/* add_filter( 'query_vars', 'addnew_query_vars', 10, 1 );
function addnew_query_vars($vars)
{   
    $vars[] = 'var1'; // var1 is the name of variable you want to add       
    return $vars;
}

var_dump($_GET['var1']);*/

/*function myplugin_query_vars( $qvars ) {
    $qvars[] = 'custom_query_var';
    return $qvars;
}
add_filter( 'query_vars', 'myplugin_query_vars' );

function wpd_append_query_string( $url ) {
    $url = add_query_arg( 'ngg_force_update', 1, $url );
    return $url;
}
add_filter( 'page_link', 'wpd_append_query_string', 10, 2 );*/

# Debugging

echo "Enable/Disable Cache Buster:", "<pre>", var_dump($randomizer_control), "</pre>";

echo "Enable/Disable No Cache Headers:", "<pre>", var_dump($cache_header_control), "</pre>";