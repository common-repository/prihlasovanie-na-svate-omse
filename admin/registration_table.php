<?php
/*
* Registration table callback
*/
function tsspsv_registration_table_callback() {
	if(isset($_POST['your_service'])) { $your_service = absint($_POST['your_service']);} else $your_service = 0;

	if ($your_service == 0) wp_die();

	global $wpdb;
	global $tsspsv_table;
	global $tsspsv_serv_table;
	
	$registrations = $wpdb->get_results( 
		"
			SELECT sp.id, sp.name, sp.reg_date, sp.reg_key  
			FROM " . $wpdb->prefix . $tsspsv_table . " sp
			WHERE sp.id_service = " . $your_service . "
			Order By
			LTrim(Reverse(Left(Reverse(sp.name),LOCATE(' ', CONCAT(Reverse(sp.name),' '))))) ASC
		"
	);

	$service = $wpdb->get_row( 
		"
		SELECT spo.name as serv_name, spo.serv_day, spo.serv_hour, spo.serv_minute  
		FROM " . $wpdb->prefix . $tsspsv_serv_table . " spo
		WHERE spo.id = " . $your_service . "
		"
	);

	$calendar_days = tsspsv_get_calendar_days();

	$service_name = esc_attr($service->serv_name);
	$service_serv_day = absint($service->serv_day);
	$service_serv_hour = absint($service->serv_hour);
	$service_serv_minute = absint($service->serv_minute);

	$service_time = $service_serv_hour . ":" . ($service_serv_minute < 10 ? "0" . $service_serv_minute : $service_serv_minute );

	?>
		<div class="tablenav top no-print">
			<div class="alignleft actions bulkactions">
				<input type="submit" id="doaction" class="button action" value="Vytlačiť" onclick="tsspsv_printDiv('#tsspsv-reg-table')">
				<input type="submit" id="export_csv" class="button action" value="CSV Export" onclick="tsspsv_csv_export('<?php echo $your_service; ?>')"> 
				<input type="submit" id="deletereg" class="button delete" style="display: none;" value="Odstrániť" onclick="tsspsv_delete_reg('#tsspsv-reg-table')">
			</div>
		</div>
		<div style="text-align: center;"><h2><?php echo $service_name . " " . $calendar_days[$service_serv_day] . " - " . $service_time; ?></h2></div>
		<div style="display:inline-block;">
		<table class="wp-list-table widefat fixed striped table-view-list posts">
			<thead>
				<tr>
					<td class="manage-column column-cb check-column"><input id="cb-select-all" type="checkbox" value="0"></td>
					<th class="manage-column column-order"><label><?php _e('Poradie', 'spirit-registration'); ?></label></th>
					<th class="manage-column"><label><?php _e('Meno', 'spirit-registration'); ?></label></th>
					<td class="manage-column column-cb check-column"></td>
					<th class="manage-column column-order"><label><?php _e('Poradie', 'spirit-registration'); ?></label></th>
					<th class="manage-column"><label><?php _e('Meno', 'spirit-registration'); ?></label></th>	
					<td class="manage-column column-cb check-column"></td>
					<th class="manage-column column-order"><label><?php _e('Poradie', 'spirit-registration'); ?></label></th>
					<th class="manage-column"><label><?php _e('Meno', 'spirit-registration'); ?></label></th>											
				</tr>  
			</thead>
			<tbody>
	<?php
	 
	for($i=0; $i<count($registrations); $i++) {

		$reg_id = absint($registrations[$i]->id);
		$reg_name = esc_attr($registrations[$i]->name);

		?>
			<tr>
				<th class="check-column"><input id="cb-select-<?php echo $reg_id; ?>" type="checkbox" name="post[]" value="<?php echo $reg_id; ?>"></th>
				<td class="index-column column-order"><?php echo ($i+1); ?>.</td>
				<td><?php echo $reg_name; ?></td>
				<?php 
					if (isset($registrations[$i+1])) {
						$i++;
						$reg_id = absint($registrations[$i]->id);
						$reg_name = esc_attr($registrations[$i]->name);
				?>
				<th class="check-column"><input id="cb-select-<?php echo $reg_id; ?>" type="checkbox" name="post[]" value="<?php echo $reg_id; ?>"></th>
				<td class="index-column column-order"><?php echo ($i+1); ?>.</td>
				<td><?php echo $reg_name; ?></td>				
				<?php 
					} else {
						echo "<th></th><td></td><td></td>";
					}
				?>
				<?php 
					if (isset($registrations[$i+1])) {
						$i++;
						$reg_id = absint($registrations[$i]->id);
						$reg_name = esc_attr($registrations[$i]->name);
				?>
				<th class="check-column"><input id="cb-select-<?php echo $reg_id; ?>" type="checkbox" name="post[]" value="<?php echo $reg_id; ?>"></th>
				<td class="index-column column-order"><?php echo ($i+1); ?>.</td>
				<td><?php echo $reg_name; ?></td>				
				<?php 
					} else {
						echo "<th></th><td></td><td></td>";
					}
				?>				
			</tr>
		<?php
	
	}
	
	?>
			</tbody>
		</table>
		</div>
	<?php

	wp_die();
}

/*
* CSV export table callback
*/
function tsspsv_csv_export_callback() {
	if(isset($_POST['your_service'])) { $your_service = absint($_POST['your_service']);} else $your_service = 0;

	if ($your_service == 0) wp_die();	

	global $wpdb;
	global $tsspsv_table;
	global $tsspsv_serv_table;


	$wpdb->set_charset($wpdb->dbh, 'utf8');
	write_log('Charset');
	write_log($wpdb->charset);

	//$wpdb->set_charset($wpdb->dbh, 'utf8');

	$calendar_days = tsspsv_get_calendar_days();
	
	$registrations = $wpdb->get_results( 
		"
			SELECT sp.id, sp.name, sp.phone, sp.reg_date, sp.reg_key  
			FROM " . $wpdb->prefix . $tsspsv_table . " sp
			WHERE sp.id_service = " . $your_service . "
			Order By
			LTrim(Reverse(Left(Reverse(sp.name),LOCATE(' ', CONCAT(Reverse(sp.name),' '))))) ASC
		"
	);

	$service = $wpdb->get_row( 
		"
		SELECT spo.name as serv_name, spo.serv_day, spo.serv_hour, spo.serv_minute  
		FROM " . $wpdb->prefix . $tsspsv_serv_table . " spo
		WHERE spo.id = " . $your_service . "
		"
	);	

	$service_name = esc_attr($service->serv_name);
	$service_serv_day = absint($service->serv_day);
	$service_serv_hour = absint($service->serv_hour);
	$service_serv_minute = absint($service->serv_minute);
	$service_time = $calendar_days[$service_serv_day] . "_" . $service_serv_hour . "-" . ($service_serv_minute < 10 ? "0" . $service_serv_minute : $service_serv_minute );
	
	$filename = $service_time . "_" . $service_name . ".csv";
	$file_path = plugin_dir_path( dirname(__FILE__) ) . 'export/' . $filename; 
	$csv_file = fopen($file_path, 'w');

	if ($csv_file) {

		for($i=0; $i<count($registrations); $i++) {
			$line = array($i+1,$registrations[$i]->name,$registrations[$i]->phone);
			fputcsv($csv_file, $line, ';');
		}

		fclose($csv_file);	

		//Return JSON object
		echo json_encode(array(
			'status' => 'OK',
			'filename' => $filename
		));		

	} else {
		echo json_encode(array(
			'status' => 'ERROR',
			'filename' => $filename
		));		
	}

	wp_die();
}
