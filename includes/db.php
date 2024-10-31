<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*
* Create registration and services tables on plugin activation
*/
function tsspsv_db_install() { 

	global $wpdb;
	global $tsspsv_db_version, $tsspsv_serv_table, $tsspsv_table;

    $charset_collate = "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";  //$wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$wpdb->prefix}{$tsspsv_serv_table} (
        id mediumint(11) NOT NULL AUTO_INCREMENT,
        name varchar(100),
		serv_day int(11),
		serv_hour int(11),
		serv_minute int(11),
        capacity int(11),
		unlimited tinyint(1) DEFAULT 0,
		closing_day int(11),
		closing_hour int(11),
		active tinyint(1) DEFAULT 1,
		serv_order int(11),
        PRIMARY KEY  (id)
	 ) $charset_collate;
	 ";

	$sql .= " CREATE TABLE {$wpdb->prefix}{$tsspsv_table} (
		id mediumint(11) NOT NULL AUTO_INCREMENT,
		id_service int(11),
		name varchar(100),
		phone varchar(50),
		reg_date datetime, 
		reg_key varchar(6),
		PRIMARY KEY  (id)
	) $charset_collate;
	";

	$sql .= " CREATE TABLE {$wpdb->prefix}{$tsspsv_table}_history (
		id mediumint(11) NOT NULL AUTO_INCREMENT,
		id_service int(11),
		name varchar(100),
		phone varchar(50),
		serv_date date,
		PRIMARY KEY  (id)
	) $charset_collate;
	";	

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

    update_option('tsspsv_db_version', $tsspsv_db_version );
}

/*
* Check if event table update is required
*/
function tsspsv_update_db_check() { 
    global $tsspsv_db_version;

    if ( get_site_option( 'tsspsv_db_version' ) != $tsspsv_db_version ) {
        tsspsv_db_install();
    }
}
add_action( 'plugin_loaded', 'tsspsv_update_db_check');