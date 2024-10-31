<?php
/*
* Services table
*/
function tsspsv_get_services_table() {
	global $wpdb;
	global $tsspsv_serv_table;

	$services = $wpdb->get_results( 
		"
			SELECT spo.id, spo.name, spo.serv_day, spo.serv_hour, spo.serv_minute, spo.capacity, spo.unlimited, spo.closing_day, spo.closing_hour, spo.active
			FROM " . $wpdb->prefix . $tsspsv_serv_table . " spo
			ORDER BY spo.serv_order
		"
	);   
	
	$calendar_days = tsspsv_get_calendar_days();

	?>
	<div class="tablenav top no-print">
	<div class="alignleft bulkactions">
		<input type="submit" id="add_service" class="button action" value="Pridať" onclick="tsspsv_add_service('#tsspsv-services-table')">
		<input type="submit" id="edit_service" class="button action" style="display: none;" value="Upraviť" onclick="tsspsv_edit_service('#tsspsv-services-table')">
		<input type="submit" id="deletereg" class="button delete action" style="display: none;" value="Odstrániť" onclick="tsspsv_delete_reg('#tsspsv-services-table')">
		<input type="submit" id="save_edit" class="button edit" style="display: none; float:right;" value="Uložiť" onclick="tsspsv_save_edit('#tsspsv-services-table')">
		<input type="submit" id="cancel_edit" class="button edit" style="display: none; float:right;" value="Zrušiť" onclick="tsspsv_cancel_edit('#tsspsv-services-table')">                            
	</div>
</div>
<table class="wp-list-table widefat fixed striped table-view-list posts">
	<thead>
		<tr>
			<td class="manage-column column-cb check-column"><input id="cb-select-all" type="checkbox" value="0"></td>
			<th class="manage-column order-column"><label><?php _e('Poradie', 'spirit-registration'); ?></label></th>
			<th class="manage-column narrow-column"><label title="Aktívna"><?php _e('A.', 'spirit-registration'); ?></label></th>
			<th class="manage-column"><label><?php _e('Čas', 'spirit-registration'); ?></label></th>
			<th class="manage-column"><label><?php _e('Názov', 'spirit-registration'); ?></label></th>
			<th class="manage-column capacity-column"><label><?php _e('Kapacita', 'spirit-registration'); ?></label></th>
			<th class="manage-column narrow-column"><label>∞</label></th>
			<th class="manage-column"><label><?php _e('Uzatvorenie', 'spirit-registration'); ?></label></th>
			<th class="manage-column"><label><?php _e('Shortcode', 'spirit-registration'); ?></label></th>
		</tr>  
	</thead>
	<tbody>
<?php

$i = 1;
$j = count($services);

foreach ( $services as $service ) {

	
	$service_id = absint($service->id);
	$service_active = absint($service->active);
	$service_name = esc_attr($service->name);
	$service_capacity = esc_attr($service->capacity);
	$service_unlimited = absint($service->unlimited);
	$service_closing_day = absint($service->closing_day);
	$service_closing_hour = absint($service->closing_hour);
	$service_serv_day = absint($service->serv_day);
	$service_serv_hour = absint($service->serv_hour);
	$service_serv_minute = absint($service->serv_minute);

	$service_time = $service_serv_hour . ":" . ($service_serv_minute < 10 ? "0" . $service_serv_minute : $service_serv_minute );

?>
	<tr>
		<th class="check-column"><input id="cb-select-<?php echo $service_id; ?>" type="checkbox" name="post[]" value="<?php echo $service_id; ?>"></th>
		<td class="index-column order-column" data-order="<?php echo $i; ?>"><div class="dashicons dashicons-arrow-up-alt order-arrow" onclick="tsspsv_change_order(this,'1')"></div><span class="serv_order"><?php echo $i; ?></span>.<div class="dashicons dashicons-arrow-down-alt order-arrow" onclick="tsspsv_change_order(this,'0')"></div></td>
		<td class="tsspsv-edit tsspsv-check"><span class="tsspsv-read-value"><input type="checkbox" value="1" name="service_active_readonly" <?php checked($service_active,"1",true); ?> disabled="disabled" /></span><span class="tsspsv-edit-value"><input type="checkbox" value="1" name="service_active" <?php checked($service_active,"1",true); ?>/></span></td>
		<td class="tsspsv-edit"><span class="tsspsv-read-value"><?php echo $service_time . " " . $calendar_days[$service_serv_day]; ?></span><span class="tsspsv-edit-value"><?php echo tsspsv_get_serv_time($service_serv_day,$service_serv_hour,$service_serv_minute); ?></span></td>
		<td class="tsspsv-edit"><span class="tsspsv-read-value"><?php echo $service_name; ?></span><span class="tsspsv-edit-value"><input type="text" name="service_name" class="tsspsv-service-name" value="<?php echo $service_name; ?>"></span></td>
		<td class="tsspsv-edit capacity-column"><span class="tsspsv-read-value"><?php echo ($service_unlimited == 1 ? '' : $service_capacity); ?></span><span class="tsspsv-edit-value"><input type="number" name="service_capacity" value="<?php echo $service_capacity; ?>" size="3"></span></td>
		<td class="tsspsv-edit tsspsv-check"><span class="tsspsv-read-value"><input type="checkbox" value="0" name="service_unlimited_readonly" <?php checked($service_unlimited,"1",true); ?> disabled="disabled" /></span><span class="tsspsv-edit-value"><input type="checkbox" value="0" name="service_unlimited" <?php checked($service_unlimited,"1",true); ?>/></span></td>		
		<td class="tsspsv-edit"><span class="tsspsv-read-value"><?php _e($calendar_days[$service_closing_day], 'spirit-registration'); echo " " . $service_closing_hour . ":00"; ?></span><span class="tsspsv-edit-value"><?php echo tsspsv_get_reg_closed($service_closing_day,$service_closing_hour); ?></span></td>		
		<td class="tsspsv-shortcode"><span>[tsspsv_service id="<?php echo $service_id; ?>" time="<?php echo $service_time; ?>" text="<?php echo $service_name; ?>"]</span></td>	
	</tr>
<?php

$i++;
}

?>
	</tbody>
</table>
<?php	
}

/*
* Display new service row Ajax callback
*/
function tsspsv_new_service_row_callback() {
	?>
	
		<tr class="new">
			<td><span class="ajax-loader"></span></td>
			<td></td>
			<td></td>
			<td class="tsspsv-edit"><span class="tsspsv-edit-value"><?php echo tsspsv_get_serv_time(0,0,0); ?></span></td>	
			<td class="tsspsv-edit"><span class="tsspsv-edit-value"><input type="text" placeholder="<?php _e("Názov sv. omše",'spirit-registration'); ?>" name="service_name" class="tsspsv-service-name" value=""></span></td></td>
			<td class="tsspsv-edit"><span class="tsspsv-edit-value"><input type="number" placeholder="<?php _e("Počet",'spirit-registration'); ?>" name="service_capacity" class="tsspsv-service-capacity" value="" size="3"></span></td>
			<td class="tsspsv-edit"><span class="tsspsv-edit-value"><input type="checkbox" value="0" name="service_unlimited" class="tsspsv-service-unlimited"/></span></td>
			<td class="tsspsv-edit"><span class="tsspsv-edit-value"><?php echo tsspsv_get_reg_closed(0,0); ?></span></td>		
			<td></td>
		</tr>
	
		<?php
	
		wp_die();
}	

/*
* Save service add Ajax callback
*/
function tsspsv_add_service_save_callback() {
	if(isset($_POST['new_serv_name'])) { $new_serv_name = sanitize_text_field($_POST['new_serv_name']);} else $new_serv_name = 0;
	if(isset($_POST['new_serv_day'])) { $new_serv_day = absint($_POST['new_serv_day']);} else $new_serv_day = 0;
	if(isset($_POST['new_serv_hour'])) { $new_serv_hour = absint($_POST['new_serv_hour']);} else $new_serv_hour = 0;
	if(isset($_POST['new_serv_minute'])) { $new_serv_minute = absint($_POST['new_serv_minute']);} else $new_serv_minute = 0;
	if(isset($_POST['new_serv_capacity'])) { $new_serv_capacity = absint($_POST['new_serv_capacity']);} else $new_serv_capacity = 0;
	if(isset($_POST['new_serv_unlimited'])) { $new_serv_unlimited = absint($_POST['new_serv_unlimited']);} else $new_serv_unlimited = 0;
	if(isset($_POST['new_closing_day'])) { $new_closing_day = absint($_POST['new_closing_day']);} else $new_closing_day = 0;
	if(isset($_POST['new_closing_hour'])) { $new_closing_hour = absint($_POST['new_closing_hour']);} else $new_closing_hour = 0;
	if(isset($_POST['new_serv_order'])) { $new_serv_order = absint($_POST['new_serv_order']);} else $new_serv_order = 0;

	global $wpdb;
	global $tsspsv_serv_table;	

	//Insert registration
	$data = [
		'name' => $new_serv_name,
		'serv_day' => $new_serv_day,
		'serv_hour' => $new_serv_hour,
		'serv_minute' => $new_serv_minute,
		'capacity' => $new_serv_capacity,
		'unlimited' => $new_serv_unlimited,
		'closing_day' => $new_closing_day,
		'closing_hour' => $new_closing_hour,
		'serv_order' =>  $new_serv_order,
		'active' => 1
	];

	$format = [
		'%s',
		'%d',
		'%d',
		'%d',		
		'%d',
		'%d',
		'%d',
		'%d',
		'%d',
		'%d'
	];

	$wpdb->insert($wpdb->prefix . $tsspsv_serv_table, $data, $format);


	wp_die();	
}

/*
* Save service edit Ajax callback
*/
function tsspsv_save_service_edit_callback() {
	if(isset($_POST['checked_serv_ids'])) { $checked_serv_ids = explode(",",sanitize_text_field($_POST['checked_serv_ids']));} else $checked_serv_ids = 0;
	if(isset($_POST['serv_actives'])) { $serv_actives = explode(",",sanitize_text_field($_POST['serv_actives']));} else $serv_actives = 0;
	if(isset($_POST['serv_names'])) { $serv_names = explode(",",sanitize_text_field($_POST['serv_names']));} else $serv_names = 0;
	if(isset($_POST['serv_hours'])) { $serv_hours = explode(",",$_POST['serv_hours']);} else $serv_hours = 0;
	if(isset($_POST['serv_minutes'])) { $serv_minutes = explode(",",$_POST['serv_minutes']);} else $serv_minutes = 0;
	if(isset($_POST['serv_days'])) { $serv_days = explode(",",$_POST['serv_days']);} else $serv_days = 0;
	if(isset($_POST['serv_capacities'])) { $serv_capacities = explode(",",$_POST['serv_capacities']);} else $serv_capacities = 0;
	if(isset($_POST['serv_unlimited'])) { $serv_unlimited = explode(",",$_POST['serv_unlimited']);} else $serv_unlimited = 0;
	if(isset($_POST['serv_rc_day'])) { $serv_rc_day = explode(",",$_POST['serv_rc_day']);} else $serv_rc_day = 0;
	if(isset($_POST['serv_rc_hour'])) { $serv_rc_hour = explode(",",$_POST['serv_rc_hour']);} else $serv_rc_hour = 0;


	global $wpdb;
	global $tsspsv_serv_table;	
 

	for ($i=0; $i<count($checked_serv_ids); $i++) {

		$wpdb->update( 
			$wpdb->prefix . $tsspsv_serv_table, 
			array( 
				'name' => $serv_names[$i], 
				'serv_day' => absint($serv_days[$i]), 
				'serv_hour' => absint($serv_hours[$i]), 
				'serv_minute' => absint($serv_minutes[$i]), 
				'capacity' => absint($serv_capacities[$i]), 
				'unlimited' => absint($serv_unlimited[$i]), 
				'closing_day' => absint($serv_rc_day[$i]), 
				'closing_hour' => absint($serv_rc_hour[$i]),
				'active' => absint($serv_actives[$i])
			), 
			array( 'id' => $checked_serv_ids[$i] ), 
			array( 
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d' 
			), 
			array( '%d' ) 
		);	
	}

	wp_die();		
}

/*
* Services table - service time - day and hour select element
*/
function  tsspsv_get_serv_time($sel_day, $sel_hour, $sel_min) {
	$rc_HTML = "";

	$calendar_days = tsspsv_get_calendar_days();
	$day_hours = tsspsv_get_calendar_hours(false);
	$day_mins = tsspsv_get_calendar_minutes();


	$rc_HTML .= "<select name=\"serv_hour\" onchange=\"tsspsv_update_serv_time(this)\">";
		foreach($day_hours as $key=>$value) {
			$rc_HTML .= "<option value=\"" .  $key  . "\" " . selected( $sel_hour, $key, false) . ">" . $value . "</option>";
		}
	$rc_HTML .= "</select> : ";

	$rc_HTML .= "<select name=\"serv_minute\" onchange=\"tsspsv_update_serv_time(this)\">";
		foreach($day_mins as $key=>$value) {
			$rc_HTML .= "<option value=\"" .  $key  . "\" " . selected( $sel_min, $key, false) . ">" . $value . "</option>";
		}
	$rc_HTML .= "</select> - ";	

	$rc_HTML .= "<select name=\"serv_day\" onchange=\"tsspsv_update_serv_time(this)\">";
		foreach($calendar_days as $key=>$value) {
			$rc_HTML .= "<option value=\"" .  $key  . "\" " . selected( $sel_day, $key, false) . " >" . $value . "</option>";
		}
	$rc_HTML .= "</select>";
	$rc_HTML .= "<input type=\"hidden\" name=\"serv_input\" value=\"" . $day_hours[$sel_hour] . ":" . $day_mins[$sel_min] . " " . $calendar_days[$sel_day] . "\">";

	return $rc_HTML;
}

/*
* Services table - reg. closed - day and hour select element
*/
function  tsspsv_get_reg_closed($sel_day, $sel_hour) {
	$rc_HTML = "";

	$calendar_days = tsspsv_get_calendar_days();
	$day_hours = tsspsv_get_calendar_hours(true);

	$rc_HTML .= "<select name=\"rc_day\" onchange=\"tsspsv_update_cl_time(this)\">";
		foreach($calendar_days as $key=>$value) {
			$rc_HTML .= "<option value=\"" .  $key  . "\" " . selected( $sel_day, $key, false) . " >" . $value . "</option>";
		}
	$rc_HTML .= "</select>";

	$rc_HTML .= "<select name=\"rc_hour\" onchange=\"tsspsv_update_cl_time(this)\">";
		foreach($day_hours as $key=>$value) {
			$rc_HTML .= "<option value=\"" .  $key  . "\" " . selected( $sel_hour, $key, false) . ">" . $value . "</option>";
		}
	$rc_HTML .= "</select>";

	$rc_HTML .= "<input type=\"hidden\" name=\"rc_input\" value=\"" . $calendar_days[$sel_day] . " " .  $day_hours[$sel_hour] . "\">";

	return $rc_HTML;
}

/*
* Switch two service rows
*/
function tsspsv_reorder_services_callback() {
	if(isset($_POST['serv_id_main'])) { $serv_id_main = absint($_POST['serv_id_main']);} else $serv_id_main = 0;
	if(isset($_POST['serv_id_to_move'])) { $serv_id_to_move = absint($_POST['serv_id_to_move']);} else $serv_id_to_move = 0;
	if(isset($_POST['serv_order_id_main'])) { $serv_order_id_main = absint($_POST['serv_order_id_main']);} else $serv_order_id_main = 0;
	if(isset($_POST['serv_order_id_to_move'])) { $serv_order_id_to_move = absint($_POST['serv_order_id_to_move']);} else $serv_order_id_to_move = 0;

	global $wpdb;
	global $tsspsv_serv_table;
	
	//Update row to be moved
	$wpdb->update( 
		$wpdb->prefix . $tsspsv_serv_table, 
		array( 'serv_order' => $serv_order_id_to_move ), 
		array( 'id' => $serv_id_main ), 
		array( '%d' ), 
		array( '%d' ) 
	);		

	//Update row to be replaced
	$wpdb->update( 
		$wpdb->prefix . $tsspsv_serv_table, 
		array( 'serv_order' => $serv_order_id_main ), 
		array( 'id' => $serv_id_to_move ), 
		array( '%d' ), 
		array( '%d' ) 
	);			

	wp_die();
}

/*
* Services table reload
*/
function tsspsv_services_table_callback() {
	tsspsv_get_services_table();
	wp_die();
}

