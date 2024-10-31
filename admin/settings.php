<?php

/*
* Register settings 
*/
function tsspsv_admin_settings() {

    $args = array(
      'type' => 'string',
      'sanitize_callback' => 'tsspsv_plugin_validate_settings',
      'default' => NULL
    ); 

    register_setting('tsspsv_options', 'tsspsv_options', $args);

    add_settings_section(
        'tsspsv_settings',
        '', 
        'tsspsv_settings_text',
        'tsspsv_settings_page'
    );

    add_settings_field(
        'tsspsv_phone_setting',
        __('Telefónne číslo', 'spirit-registration'),
        'tsspsv_phone_setting',
        'tsspsv_settings_page',
        'tsspsv_settings'
    );	

    add_settings_field(
        'tsspsv_bonus_chb_setting',
        __('Potvrdenie (napr. o očkovaní)', 'spirit-registration'),
        'tsspsv_bonus_chb_setting',
        'tsspsv_settings_page',
        'tsspsv_settings'
    );		

    add_settings_field(
        'tsspsv_user_rights_setting',
        __('Prístupové práva', 'spirit-registration'),
        'tsspsv_user_rights_setting',
        'tsspsv_settings_page',
        'tsspsv_settings'
    );

    add_settings_field(
        'tsspsv_settings_reset_time',
        __('Čas resetovania registrácii (pre sv. omše slávené v daný deň)', 'spirit-registration'),
        'tsspsv_settings_reset_time',
        'tsspsv_settings_page',
        'tsspsv_settings'
    );
}

/*
* HTML to display for tsspsv_phone settings
*/
function tsspsv_phone_setting() {
	$options = get_option('tsspsv_options');

	$tsspsv_phone_on = (isset($options['tsspsv_phone_on']) ? $options['tsspsv_phone_on'] : '');
	$tsspsv_phone_req = (isset($options['tsspsv_phone_req']) ? $options['tsspsv_phone_req'] : '');

?>	
	<label for="tsspsv_phone_on">
		<input type="checkbox" value="1" name="tsspsv_options[tsspsv_phone_on]" <?php checked($tsspsv_phone_on,"1",true); ?>/>
		<?php _e('Zobraziť','spirit-eph') ?>
	</label>
	<label for="tsspsv_phone_req" style="margin-left: 20px;">
		<input type="checkbox" value="1" name="tsspsv_options[tsspsv_phone_req]" <?php checked($tsspsv_phone_req,"1",true); ?>/>
		<?php _e('Povinné','spirit-eph') ?>
	</label>	
<?php
}

/*
* HTML to display for bonus checkbox
*/
function tsspsv_bonus_chb_setting() {
	$options = get_option('tsspsv_options');

	$tsspsv_confirm_on = (isset($options['tsspsv_confirm_on']) ? $options['tsspsv_confirm_on'] : '');
	$tsspsv_confirm_text = (isset($options['tsspsv_confirm_text']) ? $options['tsspsv_confirm_text'] : '');

?>	
	<label for="tsspsv_confirm_on">
		<input type="checkbox" value="1" name="tsspsv_options[tsspsv_confirm_on]" <?php checked($tsspsv_confirm_on,"1",true); ?>/>
		<?php _e('Zobraziť, pole je povinné','spirit-eph') ?>
	</label>
	<label for="tsspsv_confirm_text" style="margin-left: 20px;">
		<input type="text" name="tsspsv_options[tsspsv_confirm_text]" placeholder="<?php _e('Text potvrdenia','spirit-eph') ?>" value="<?php echo $tsspsv_confirm_text; ?>"/>
	</label>	
<?php
}

/*
* HTML to display user privileges
*/
function tsspsv_user_rights_setting() {
	$options = get_option('tsspsv_options');

	$tsspsv_editor_on = (isset($options['tsspsv_editor_on']) ? $options['tsspsv_editor_on'] : '');
	
?>	
	<label for="tsspsv_admin_on">
		<input type="checkbox" name="tsspsv_options[tsspsv_admin_on]" value="1" checked="checked" disabled/>
		<?php _e('Administrátor','spirit-eph') ?>
	</label>
	<label for="tsspsv_editor_on" style="margin-left: 20px;">
		<input type="checkbox" value="1" name="tsspsv_options[tsspsv_editor_on]" <?php checked($tsspsv_editor_on,"1",true); ?>/>
		<?php _e('Editor','spirit-eph') ?>
	</label>	
<?php
}

/*
* HTML to display for tsspsv_settings_reset_time setting
*/
function tsspsv_settings_reset_time() {
    $options = get_option('tsspsv_options');
    $reset_hour = $options['reset_hour'];

    $calendar_hours = tsspsv_get_calendar_hours(true);
    ?>
    <select id="reset_hour" name="tsspsv_options[reset_hour]">
        <?php
        foreach($calendar_hours as $key=>$value) {
            echo "<option value=\"" .  $key  . "\" " . selected( $reset_hour, $key, false) . " >" . $value . "</option>";
        }
        ?>
    </select>
<?php   
}

/*
* Text to display in settings section
*/
function tsspsv_settings_text() {
    return false;
}

/*
* Fire when tsspsv_options are updated - update cron job as well
*/
function tsspsv_options_update($old_value, $value, $option) {
    $day_now = date('N', current_time('timestamp')) - 1;
    $hour_now = date('H', current_time('timestamp'));
    $min_now = intval(date('i', current_time('timestamp')));

    $event_start = strtotime(($value['reset_hour'] -  $hour_now) . ' hour -' . $min_now . ' minutes' . get_option('timezone_string'), current_time('timestamp'));


    $timestamp = wp_next_scheduled ('tsspsv_reset_forms');
    if ($timestamp) {
        wp_unschedule_event($timestamp,'tsspsv_reset_forms');
        wp_schedule_event($event_start,'daily','tsspsv_reset_forms');
    } 
    else {
       wp_schedule_event($event_start,'daily','tsspsv_reset_forms');
    }
}
add_action('update_option_tsspsv_options', 'tsspsv_options_update', 10, 3);

/*
* Validate settings before saving
*/
function tsspsv_plugin_validate_settings($input) {
	
    $valid['reset_hour'] = absint($input['reset_hour']);

	if (isset($input['tsspsv_phone_on'])) {$valid['tsspsv_phone_on'] = absint($input['tsspsv_phone_on']); } else $valid['tsspsv_phone_on'] = 0;
	if (isset($input['tsspsv_phone_req'])) {$valid['tsspsv_phone_req'] = absint($input['tsspsv_phone_req']); } else $valid['tsspsv_phone_req'] = 0;
	if (isset($input['tsspsv_editor_on'])) {$valid['tsspsv_editor_on'] = absint($input['tsspsv_editor_on']); } else $valid['tsspsv_editor_on'] = 0;

	if (isset($input['tsspsv_confirm_on'])) {$valid['tsspsv_confirm_on'] = absint($input['tsspsv_confirm_on']); } else $valid['tsspsv_confirm_on'] = 0;
	$valid['tsspsv_confirm_text'] = ( ! empty( $input['tsspsv_confirm_text'] ) ) ?sanitize_text_field( $input['tsspsv_confirm_text'] ) : '';

    return $valid;
}

/*
* Register callback function for cron job
*/
function tsspsv_reset_forms() {
	global $wpdb;
    global $tsspsv_table;	  
    global $tsspsv_serv_table; 
    
	//$wpdb->query("DELETE FROM " . $wpdb->prefix . $tsspsv_table);

	//Copy participants to history table (For Covid-19) purposes
	$wpdb->query("INSERT INTO " .  $wpdb->prefix . $tsspsv_table . "_history(id_service,name,phone,serv_date)
		SELECT id_service, name, phone, CURDATE() FROM  " .  $wpdb->prefix . $tsspsv_table . " WHERE id_service IN (
			SELECT id FROM " .  $wpdb->prefix . $tsspsv_serv_table . " WHERE (WEEKDAY(NOW()) = serv_day)
			)"
	);


	//Delete participants from registration table
    $wpdb->query("DELETE FROM " .  $wpdb->prefix . $tsspsv_table . " WHERE id_service IN (
                        SELECT id FROM " .  $wpdb->prefix . $tsspsv_serv_table . " WHERE (WEEKDAY(NOW()) = serv_day)
                )"
    );

	//Copy participants from history table after 14 days
    $wpdb->query("DELETE FROM " .  $wpdb->prefix . $tsspsv_table . "_history WHERE NOW() >  (DATE_ADD(serv_date, INTERVAL 14 DAY))");	
}
add_action('tsspsv_reset_forms','tsspsv_reset_forms');