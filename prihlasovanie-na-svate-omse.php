<?php
/*
Plugin Name: Prihlasovanie na sväté omše
Plugin URI: https://thespirit.studio/spirit-registration/
Description: Prihlasovanie na sväté omše pomocou jednoduchého formuláru
Version: 1.9.1
Author: The Spirit Studio
Author URI: https://thespirit.studio
Text Domain: spirit-registration
Domain Path: /languages
License: GPL2

This plugin serves as a simple registration form to manage number of participants attending Sunday services. 
Due to coronavirus, capacity of each church service is limited. 
Parishioner can easily register and you as a parish administrator know who is registered -> who to let in.
As of now, this plugin is only available in Slovak language, but can be easily translated. 
Please let us know and we will gladly do that.
Team TheSpirit.studio

Spirit Prihlasovanie na sv. omše is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version

Spirit Prihlasovanie na sv. omše is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {License URI}.
*/


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
define ('TSSPSV_PLUGIN_PATH', plugin_dir_path( __FILE__));
define('TSSPSV_VERSION', '1.9.1');

include (TSSPSV_PLUGIN_PATH . 'admin/admin.php');
include (TSSPSV_PLUGIN_PATH . 'admin/settings.php');
include (TSSPSV_PLUGIN_PATH . 'admin/registration_table.php');
include (TSSPSV_PLUGIN_PATH . 'admin/registration_history_table.php');
include (TSSPSV_PLUGIN_PATH . 'admin/services_table.php');
include (TSSPSV_PLUGIN_PATH . 'includes/db.php');
include (TSSPSV_PLUGIN_PATH . 'includes/functions.php');
include (TSSPSV_PLUGIN_PATH . 'includes/shortcode.php');

global $tsspsv_db_version, $tsspsv_serv_table, $tsspsv_table;
$tsspsv_db_version = '1.5';
$tsspsv_serv_table = 'spirit_registration_services';
$tsspsv_table = 'spirit_registration';

/*
* Activate plugin
*/
function tsspsv_activate() {

    //Install plugin DB tables
    tsspsv_db_install();
    
    //Set up plugin options
	$options = array(
		'reset_day' => 0,
		'reset_hour' => 21
	);
    update_option( 'tsspsv_options', $options );     
    
    //Register cron event for reseting registration forms.
	if (! wp_next_scheduled ( 'tsspsv_reset_forms' )) {
		wp_schedule_event(strtotime("next Monday"), 'tsspsv_daily', 'tsspsv_reset_forms');
	}    
    
}
register_activation_hook( __FILE__, 'tsspsv_activate' );

/*
* Deactivate plugin
*/
function tsspsv_deactivate() {

    // Deactivation code here...
}
register_deactivation_hook( __FILE__, 'tsspsv_deactivate' );

/*
* Load text domain
*/
function tsspsv_load_plugin_textdomain() {

    $domain = 'spirit-registration';
    load_plugin_textdomain(
        $domain, false, basename(dirname(__FILE__)) . '/languages/'
    );
}
add_action('init', 'tsspsv_load_plugin_textdomain');

/*
* Enqueue styles for frontend
*/
function tsspsv_enqueue_styles() {
    wp_enqueue_style('spirit-registration-css', plugins_url( 'css/spirit-registration.css',__FILE__ ), array(), TSSPSV_VERSION);
}
add_action( 'wp_enqueue_scripts', 'tsspsv_enqueue_styles' );

/*
* Enqueue scripts for frontend
*/
function tsspsv_enqueue_scripts() {
    wp_enqueue_script('spirit-registration-js', plugins_url( 'js/spirit-registration.js',__FILE__ ), array('jquery'), TSSPSV_VERSION);
    wp_localize_script( 'spirit-registration-js', 'my_ajax_object', 
        array( 'ajax_url' => admin_url( 'admin-ajax.php' ),
               'field_required' => __('Toto pole je povinné.','spirit-registration'),
               'select_service' => __('Prosím vyberte sv. omšu','spirit-registration'),
               'privacy_consent' => __('Pred odoslaním formuláru, potrebujeme Váš súhlas so spracovaním osobných údajov.','spirit-registration'),
               'service' => __('Svätá omša','spirit-registration'),
               'name' => __('Meno','spirit-registration'),
               'email_incorrect_format' => __('Nesprávny formát emailu','spirit-registration'),
               'sign_up_text' => __('Zapíšte sa tu','spirit-registration')

               
        ) 
    );
}
add_action( 'wp_enqueue_scripts', 'tsspsv_enqueue_scripts' );

/*
* Add custom daily interval for cron job
*/
function tsspsv_add_cron_interval( $schedules ) { 
    $schedules['tsspsv_daily'] = array(
        'interval' => 86400,
        'display'  => esc_html__('Every day') );
    return $schedules;
}
add_filter('cron_schedules','tsspsv_add_cron_interval');
?>