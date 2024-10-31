<?php
/*
 * Everything related to Wordpress administration.
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action( 'wp_ajax_tsspsv_submit_form', 'tsspsv_submit_form_callback'); 
add_action( 'wp_ajax_nopriv_tsspsv_submit_form', 'tsspsv_submit_form_callback'); 
add_action( 'wp_ajax_tsspsv_submit_dereg_form', 'tsspsv_submit_dereg_form_callback'); 
add_action( 'wp_ajax_nopriv_tsspsv_submit_dereg_form', 'tsspsv_submit_dereg_form_callback'); 

/*
*	Mode 3 = History, Mode 2 = Client deregister, Mode 1 = Client register, 0 = Admin
*/
function tsspsv_get_services($mode) {

	global $wpdb;
	global $tsspsv_serv_table;
	global $tsspsv_table;

	$history = "";
	if ($mode == 3) $history = "_history";

	$calendar_days = tsspsv_get_calendar_days();

	$select_box = "<select name=\"your-service\" data-mode=\"" . $mode . "\">";
	$serv_status = "";

	$services = $wpdb->get_results( 
		"
			SELECT spo.id, spo.name, sp.phone, (spo.capacity - count(sp.id)) as available, spo.unlimited,
			". ($mode == 3 ? 'serv_date,' : '') . "
			spo.serv_day, spo.serv_hour, spo.serv_minute,
			CASE WHEN ( (spo.closing_day + (6 - spo.serv_day) ) % 7 <= ((WEEKDAY(NOW()) + (6 - spo.serv_day) ) % 7)  AND HOUR(NOW()) >= spo.closing_hour) THEN 1 ELSE 0 END as closed 
			FROM " . $wpdb->prefix . $tsspsv_serv_table . " spo
			". ($mode != 3 ? ' LEFT' : '') . " JOIN " . $wpdb->prefix . $tsspsv_table . $history . " sp ON sp.id_service = spo.id
			WHERE spo.active = 1
			GROUP BY spo.id, spo.name, spo.serv_order ". ($mode == 3 ? ',sp.serv_date' : '') . "
			ORDER BY spo.serv_order
		"
	);

	$select_box .= "<option value=\"0\">" . __('Prosím vyberte...','spirit-registration') . "</option>";
	 
	foreach ( $services as $service ) {

		$service_id = absint($service->id);
		$service_serv_day = absint($service->serv_day);
		$service_serv_hour = absint($service->serv_hour);
		$service_serv_minute = absint($service->serv_minute);
		$service_available = absint($service->available);
		$service_unlimited = absint($service->unlimited);
		$service_closed = absint($service->closed);
		$service_name = esc_attr($service->name);

		//Calculate service date
		$day_now = date('N', current_time('timestamp')) - 1;
		$future_day = tsspsv_get_future_day( $service_serv_day,$day_now);
		$service_fut_date = " " . date('j.n.', $future_day);
		if ($mode == 3) { $service_fut_date = ""; }

		$service_time = $calendar_days[$service_serv_day] . $service_fut_date . " - " . $service_serv_hour . ":" . ($service_serv_minute < 10 ? "0" . $service_serv_minute : $service_serv_minute );

		if ($mode == 1) {
			if ($service_available == 0) $serv_status = " - " . __('Kapacita sa naplnila','spirit-registration');
			if ($service_available > 0 && $service_closed == 1) $serv_status = " - " . __('Zapisovanie ukončené','spirit-registration');

			$select_box .= "<option value=\"" . $service_id  . "\" " . tsspsv_serv_disabled($service_available,$service_unlimited, $service_closed, 1) . " >" . $service_time . " " . $service_name . ($service_unlimited == 0 ? " (" . $service_available . ")" : '') . $serv_status . "</option>";
		} else if ($mode == 2) {
			$select_box .= "<option value=\"" . $service_id  . "\">" . $service_time . " " . $service_name . "</option>";	
		}
		else if ($mode == 3){
			$service_date = esc_attr($service->serv_date);

			$select_box .= "<option value=\"" . $service_id  . "\" data-serv_date=\"" . $service_date . "\">" . $service_date . " " . $service_time . " " . $service_name . "</option>";
		}
		else {
			$select_box .= "<option value=\"" . $service_id  . "\" >" . $service_time . " " . $service_name . ($service_unlimited == 0 ? " (" . $service_available . ")" : "") . "</option>";
		}

		$serv_status = "";
	}


	$select_box .= "</select>";

	return $select_box;
}

/*
* Check if option or button should be disabled
* type: 1 = option, 2 = button
*/
function tsspsv_serv_disabled($available,$unlimited,$closed, $type) {
	$disabled = "";

	if(($available <= 0 && $unlimited == 0) || $closed == 1) {
		if ($type == 1) $disabled = "disabled=\"disabled\"";
		if ($type == 2) $disabled = "disabled";
	}
	
	return $disabled;
}

/*
* Register participant
*/
function tsspsv_submit_form_callback() {
	if(isset($_POST['your_service'])) { $your_service = absint($_POST['your_service']);} else $your_service = 0;	
	if(isset($_POST['your_name'])) { $your_name = sanitize_text_field($_POST['your_name']);} else $your_name = 0;
	if(isset($_POST['your_phone'])) { $your_phone = sanitize_text_field($_POST['your_phone']);} else $your_phone = '';
	if(isset($_POST['your_email_check'])) { $your_email_check = absint($_POST['your_email_check']);} else $your_email_check = 0;	
	if(isset($_POST['your_email_confirm'])) { $your_email_confirm = sanitize_email($_POST['your_email_confirm']);} else $your_email_confirm = 0;	

	global $wpdb;
	global $tsspsv_table;
	global $tsspsv_serv_table;

	$calendar_days = tsspsv_get_calendar_days();

	//Check if there is a place still available
	$availability = $wpdb->get_row( 
		"
			SELECT sp.id_service, sp.booked, spo.name as service_name, spo.capacity, spo.unlimited,
			spo.serv_day, spo.serv_hour, spo.serv_minute,
			CASE WHEN ( (spo.closing_day + (6 - spo.serv_day) ) % 7 <= ((WEEKDAY(NOW()) + (6 - spo.serv_day) ) % 7)  AND HOUR(NOW()) >= spo.closing_hour) THEN 1 ELSE 0 END as closed 
			FROM
				(
				SELECT Count(*) as booked, " . $your_service . " AS id_service
				FROM " . $wpdb->prefix . $tsspsv_table . " 
				WHERE id_service = " . $your_service . "
				) as sp
			JOIN " . $wpdb->prefix . $tsspsv_serv_table . " spo ON spo.id = sp.id_service
		"
	);

	$service_id = absint($availability->id_service);
	$service_serv_day = absint($availability->serv_day);
	$service_serv_hour = absint($availability->serv_hour);
	$service_serv_minute = absint($availability->serv_minute);
	$service_name = esc_attr($availability->service_name);
	$capacity = absint($availability->capacity);
	$unlimited = absint($availability->unlimited);
	$booked = absint($availability->booked);
	$closed = absint($availability->closed);

	$total_capacity = $capacity - $booked;

	$day_now = date('N', current_time('timestamp')) - 1;
	$future_day = tsspsv_get_future_day($service_serv_day,$day_now);

	$service_time = $calendar_days[$service_serv_day] . " " . date('j.n.Y', $future_day) . " - " . $service_serv_hour . ":" . ($service_serv_minute < 10 ? "0" . $service_serv_minute : $service_serv_minute );


	if (($booked < $capacity || $unlimited ==1) && $closed != 1) {

		//Insert registration
		$data = [
			'id_service' => $your_service,
			'name' => $your_name,
			'phone' => $your_phone,
			'reg_date' =>  current_time('Y-m-d H:i:s'),
			'reg_key' => tsspsv_key_generator()
		];

		$format = [
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
		];

		$wpdb->insert($wpdb->prefix . $tsspsv_table, $data, $format);

		//Send confirm email if checked
		if ($your_email_check == 1){
			
			$event_name = "Svätá omša: ";
 			$to = $your_email_confirm;
			$subject = 'Potvrdenie registrácie - ' . $service_time . " " . $service_name;
			$body = "<strong>" . __('Registrácia úspešná.','spirit-registration') . "</strong><br>" . $event_name . $service_time . " " . $service_name . "<br>" . "Meno: " . $your_name . "<br><br>" .  __('Tento e-mail je generovaný automaticky, prosíme, neodpovedajte naň.','spirit-registration');
			$headers = array('Content-Type: text/html; charset=UTF-8',
							 'From: Registrácia na sv. omše <wordpress@' . tsspsv_get_site_url() . '>');
			
			wp_mail( $to, $subject, $body, $headers );
		}

		//Return JSON object
		echo json_encode(array(
			'status' => 'OK',
			'message' => __('Registrácia úspešná.','spirit-registration'),
			'name' => $your_name,
			'service_name' => $service_time . " " . $service_name,
			'id_service' => $your_service,
			'capacity' => ($total_capacity - 1),
			'unlimited' => $unlimited
		));

	}
	else {
		$message = __('Je nám ľúto, kapacita sa už naplnila.','spirit-registration');
		if ($availability_closed == 1) $message =  __('Je nám ľúto, zapisovanie je ukončené.','spirit-registration');

		echo json_encode(array(
			'status' => 'FULL',
			'message' => $message,
			'service_name' => $service_time . " " . $service_name,
			'id_service' => $your_service,
			'capacity' => ($total_capacity),
			'unlimited' => $unlimited
		));
	}
 
	wp_die();
}

/*
* Deregister participant
*/
function tsspsv_submit_dereg_form_callback() {
	if(isset($_POST['your_service'])) { $your_service = absint($_POST['your_service']);} else $your_service = 0;	
	if(isset($_POST['your_name'])) { $your_name = sanitize_text_field($_POST['your_name']);} else $your_name = 0;

	global $wpdb;
	global $tsspsv_table;
	global $tsspsv_serv_table;

	$calendar_days = tsspsv_get_calendar_days();

	//Check if participant is already registered
	$registrant = $wpdb->get_row( 
		"
			SELECT sp.id
			FROM " . $wpdb->prefix . $tsspsv_table . " sp
			WHERE sp.id_service = " . $your_service . " AND sp.name = '" . $your_name . "' 
			LIMIT 1
		"
	);	

	if(isset($registrant->id)) {
		$registrant_id = absint($registrant->id);
	}
	else {
		$registrant_id = 0;
	}

	//If there is a match, proceed to removal
	if ($registrant_id != 0) {

		$service = $wpdb->get_row( 
			"
				SELECT spo.id, spo.name, spo.serv_day, spo.serv_hour, spo.serv_minute
				FROM " . $wpdb->prefix . $tsspsv_serv_table . " spo 
				WHERE spo.id = " . $your_service . "
			"
		);

		$service_id = absint($service->id);
		$service_name = esc_attr($service->name);
		$service_serv_day = absint($service->serv_day);
		$service_serv_hour = absint($service->serv_hour);
		$service_serv_minute = absint($service->serv_minute);
	
		$service_time = $calendar_days[$service_serv_day] . " - " . $service_serv_hour . ":" . ($service_serv_minute < 10 ? "0" . $service_serv_minute : $service_serv_minute );	

		$wpdb->query( 
			"
				DELETE FROM " . $wpdb->prefix . $tsspsv_table . "
				WHERE id = " . $registrant_id . "
			"
		);

		//Return JSON object
		echo json_encode(array(
			'status' => 'OK',
			'message' => __('Odhlásenie úspešné.','spirit-registration'),
			'name' => $your_name,
			'service_name' => $service_time . " " . $service_name,
			'id_service' => $your_service
		));

	}
	else {
		$message = __('Odhlásenie neúspešné, kombinácia mena a sv. omše sa nenašla','spirit-registration');

		echo json_encode(array(
			'status' => 'ERROR',
			'message' => $message,
			'id_service' => $your_service
		));
	}
 
	wp_die();
}

/*
* Get calendar days array
*/
function  tsspsv_get_calendar_days() {
	$calendar_days = array(
		0 => __('Pondelok', 'spirit-registration'),
		1 => __('Utorok', 'spirit-registration'),
		2 => __('Streda', 'spirit-registration'),
		3 => __('Štvrtok', 'spirit-registration'),
		4 => __('Piatok', 'spirit-registration'),
		5 => __('Sobota', 'spirit-registration'),
		6 => __('Nedeľa', 'spirit-registration')
	);

	return $calendar_days;
}

/*
* Get calendar hours array
*/
function  tsspsv_get_calendar_hours($has_mins) {
	$day_hours = array();

	for ($i=0; $i<24; $i++) {
		if ($has_mins) {
			if ($i<10) {
				$day_hours[$i] = "0" . $i . ":00";
			}
			else{
				$day_hours[$i] = $i . ":00";	
			}	
		}
		else {
			if ($i<10) {
				$day_hours[$i] = "0" . $i;
			}
			else{
				$day_hours[$i] = $i;	
			}	
		}
	}

	return $day_hours;
}

/*
* Get calendar minutes array
*/
function  tsspsv_get_calendar_minutes() {
	$day_hours = array();

	for ($i=0; $i<59; $i++) {
		if ($i<10) {
			$day_hours[$i] = "0" . $i;
		}
		else{
			$day_hours[$i] = $i;	
		}	
	}

	return $day_hours;
}

/*
* Delete record from Registration or Services table - Ajax callback
*/
function tsspsv_delete_record_admin_callback() {
	if(isset($_POST['checked_regs'])) { $checked_regs = sanitize_text_field($_POST['checked_regs']);} else $checked_regs = 0;
	if(isset($_POST['id_table'])) { $id_table = sanitize_text_field($_POST['id_table']);} else $id_table = 0;
	
	global $wpdb;
	global $tsspsv_table;	
	global $tsspsv_serv_table;	

	$table = (strcmp($id_table, "#tsspsv-reg-table") == 0 ? $tsspsv_table : $tsspsv_serv_table);

	$wpdb->query( 
			"
				DELETE FROM " . $wpdb->prefix . $table . "
				WHERE id IN (" . $checked_regs . ")
			"
	);

	wp_die();	
}

/*
* Calculate future day
*/
function tsspsv_get_future_day($day_number,$day_now){
	
	$future_day = 0;

	if ($day_now <= $day_number) {
		$future_day = strtotime("+" . ($day_number - $day_now )   . " day") + (3600 *  get_option('gmt_offset'));
	}
	else {
		$future_day = strtotime("+" . ($day_number - $day_now + 7)   . " day") + (3600 *  get_option('gmt_offset'));
	}

	return $future_day;
}

/*
* Generate random key - currently not used
*/
function tsspsv_key_generator()
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$length = 6;
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/*
* Get site URL
*/
function tsspsv_get_site_url() {
	$protocols = array('http://', 'http://www.', 'www.','https://', 'https://www.');

	return str_replace($protocols, '', get_bloginfo('wpurl'));
}

/*
* Write Log Debugging
*/
if ( ! function_exists('write_log')) {
	function write_log ( $log )  {
	   if ( is_array( $log ) || is_object( $log ) ) {
		  error_log( print_r( $log, true ) );
	   } else {
		  error_log( $log );
	   }
	}
 }