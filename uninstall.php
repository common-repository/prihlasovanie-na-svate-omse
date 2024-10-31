<?php
/*
 * Fired when the plugin is uninstalled.
*/

global $wpdb;
$tsspsv_serv_table = 'spirit_registration_services';
$tsspsv_table = 'spirit_registration';

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }

// Remove event table
$wpdb->query('DROP TABLE IF EXISTS ' .  $wpdb->prefix . $tsspsv_serv_table); //Drop plugin table
$wpdb->query('DROP TABLE IF EXISTS ' .  $wpdb->prefix . $tsspsv_table); //Drop plugin table

//Delete db version option and plugin option
delete_option('tsspsv_db_version');
delete_option('tsspsv_options');
