<?php
/*
 * Plugin Name: AWcode Toolkit
 * Version: 1.0.15
 * Description: A collection of useful tools and functions for Wordpress site owners
 * Author: AWcode
 * Author URI: https://awcode.com/
 * Requires at least: 5.0
 * Tested up to: 6.5
 * License: GPL-3.0
 *
 */
 
 // If this file is called directly, abort.

if ( ! defined( 'WPINC' ) ) {
	die;
}

function activate_awtoolkit() {
	//activation code if needed
}

function deactivate_awtoolkit() {
	//deactivation code if needed
}
register_activation_hook( __FILE__, 'activate_awtoolkit' );
register_deactivation_hook( __FILE__, 'deactivate_awtoolkit' );


require_once plugin_dir_path( __FILE__ ) . 'includes/class.awtoolkit-general.php';
$awtoolkit = new AW_Toolkit();
if(isset($_SERVER['HTTP_CF_VISITOR'])){
    $awtoolkit->enableFlexibleSSL();
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class.awtoolkit-setting.php';

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class.awtoolkit-woocommerce.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/class.awtoolkit-woo-product-suppliers.php';
}


add_filter( 'init', 'aw_checkmaintenance' );
function aw_checkmaintenance( $content ) {
    if(!current_user_can('administrator') && !is_wplogin()){
        if(get_option('aw_maint_mode')){
            $exceptions = get_option('aw_maint_exceptions');
            if($exceptions){
                $url = strtolower(strtok($_SERVER['REQUEST_URI'], '?'));
                foreach(explode('\n', $exceptions) as $ex){
                    $ex = trim(strtolower($ex));
                    if(strpos($ex, $url) !== false){ return true;}
                }
            }
            echo('<html><head><title>');
            echo(get_option('aw_maint_title'));
            echo('</title></head><body style="text-align:center; padding-top:5em; font-family:arial;"><h1>');
            echo(get_option('aw_maint_title'));
            echo('</h1><p>');
            echo(stripslashes(get_option('aw_maint_message')));
            echo('</p></body></html>');
            die();
        }
    }
}

function is_wplogin(){
    $ABSPATH_MY = str_replace(array('\\','/'), DIRECTORY_SEPARATOR, ABSPATH);
    return ((in_array($ABSPATH_MY.'wp-login.php', get_included_files()) || in_array($ABSPATH_MY.'wp-register.php', get_included_files()) ) || (isset($_GLOBALS['pagenow']) && $GLOBALS['pagenow'] === 'wp-login.php') || $_SERVER['PHP_SELF']== '/wp-login.php'|| strpos($_SERVER['REQUEST_URI'], 'wp-admin') );
}

?>
