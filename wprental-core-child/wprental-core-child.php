<?php
/*
 *  Plugin Name: wprental-core-child -override bookings menu
 *  Plugin URI:  https://themeforest.net/user/wpestate
 *  Description: Adds functionality to WpRentals
 *  Version:     3.2
 *  Author:      significdigital
 *  Author URI:  https://significdigial.com
 *  License:     GPL2
 *  Text Domain: wprentals-core-child
 *  Domain Path: /languages
 *
*/

define('WPESTATE_CHILD_PLUGIN_URL',  plugins_url() );
define('WPESTATE_CHILD_PLUGIN_DIR_URL',  plugin_dir_url(__FILE__) );
define('WPESTATE_CHILD_PLUGIN_PATH',  plugin_dir_path(__FILE__) );
define('WPESTATE_CHILD_PLUGIN_BASE',  plugin_basename(__FILE__) );

add_action( 'plugins_loaded', 'wpestate_rentals_child_functionality_loaded' );

function wpestate_rentals_child_functionality_loaded(){
    $my_theme   =   wp_get_theme();
    $version    =   floatval( $my_theme->get( 'Version' ));
    $theme_name =   $my_theme->name;
    $deactivate =   false;

    if($version< 2 && $version!=1){
        $deactivate=true;
    }
    if (strpos(strtolower($theme_name), 'wprentals') === false) {
         $deactivate=true;
    }

    if($deactivate){
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( 'WpRentals Core plugin requires  WpRentals 2.01 or higher.','wprentals-core' );
    }
    //load_plugin_textdomain( 'wprentals-core', false, dirname( WPESTATE_PLUGIN_BASE ) . '/languages' );
    //wpestate_shortcodes();
   // add_action('widgets_init', 'register_wpestate_widgets' );
    //add_action('wp_footer', 'wpestate_core_add_to_footer');



}

function wpestate_rentals_child_functionality_plugin_activated(){

}

function wpestate_rentals_child_deactivate(){
}


function wpestate_rentals_child_enqueue_styles() {
}


function wpestate_rentals_child_enqueue_styles_admin(){
}


require_once(WPESTATE_CHILD_PLUGIN_PATH . 'post-types/booking.php');
?>