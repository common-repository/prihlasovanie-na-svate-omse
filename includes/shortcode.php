<?php

/*
* Register shortcode to display services
*/
function tsspsv_register_shortcodes(){
	add_shortcode('tsspsv_form', 'tsspsv_form'); //Select control -> register
	add_shortcode('tsspsv_dereg_form', 'tsspsv_dereg_form'); //Select control -> deregister
	add_shortcode('tsspsv_service', 'tsspsv_service'); //Button
	add_shortcode('tsspsv_day_of_service', 'tsspsv_day_of_service'); //Day of service
}
add_action( 'init', 'tsspsv_register_shortcodes',1);

/*
* Registration form for type Select 
* [tsspsv_form]
*/
function tsspsv_form($attrs) {
  $attrs = (object) shortcode_atts( array(
 ), $attrs );

	$serv_form = "";

	$serv_form .= "<form action=\"#\" class=\"tsspsv tsspsv-register\" method=\"post\">";
		$serv_form .= "<p>";
			$serv_form .= "<label>" . __('Svätá Omša','spirit-registration') . "<br><span>" . tsspsv_get_services(1) . "<span class=\"tsspsv-note\">* " . __('Číslo v zátvorke znamená aktuálny počet voľných miest.','spirit-registration') . "</span><span class=\"tsspsv-not-valid-tip\"></span></span></label>";
		$serv_form .= "</p>";
		
		$serv_form .= tsspsv_get_form_body();
		
	$serv_form .= "</form>"; 

	return $serv_form;
}

/*
* Registration for type Button
*/
function tsspsv_service($attrs) {

	global $wpdb;
	global $tsspsv_table;	
	global $tsspsv_serv_table;	

	$attrs = (object) shortcode_atts( array(
		'id' => 0,
		'time' => '',
		'show_date' => 'false',
		'text' => ''
	), $attrs );
	
	$id_service = absint($attrs->id);
	$time = esc_attr($attrs->time);
	$text = esc_attr($attrs->text);
	$show_date = rest_sanitize_boolean($attrs->show_date);	

	$service = $wpdb->get_row( 
		"
			SELECT (spo.capacity - count(sp.id)) as available, spo.unlimited,
				CASE WHEN ( (spo.closing_day + (6 - spo.serv_day) ) % 7 <= ((WEEKDAY(NOW()) + (6 - spo.serv_day) ) % 7)  AND HOUR(NOW()) >= spo.closing_hour) THEN 1 ELSE 0 END as closed,
				spo.active, spo.serv_day 
				FROM " . $wpdb->prefix . $tsspsv_serv_table . " spo
				LEFT JOIN " . $wpdb->prefix . $tsspsv_table . " sp ON sp.id_service = spo.id
				WHERE spo.id=" . $id_service . "
				GROUP BY spo.id, spo.name, spo.serv_order
				ORDER BY spo.serv_order
		"
	);

	if ($show_date) {
		$calendar_days = tsspsv_get_calendar_days();
		$day_now = date('N', current_time('timestamp')) - 1;
		$future_day = tsspsv_get_future_day($service->serv_day,$day_now);
		$day_name = $calendar_days[$service->serv_day] . " " . date('j.n.Y', $future_day) . ": ";

		$text = $day_name . " " . $text;
	}	

	$serv_block = "";

	if ($service->active){
		$button_text = __('Zapíšte sa tu','spirit-registration') . ($service->unlimited == 0 ? " (" . $service->available . ")" : '');
		if ($service->available <= 0 && $service->unlimited == 0) $button_text = __('Kapacita sa naplnila','spirit-registration');
		if (($service->available > 0 || $service->unlimited == 1) && $service->closed == 1) $button_text = __('Zapisovanie ukončené','spirit-registration');
	
		$serv_block = "<form id=\"tsspsv-service-" . $id_service . "\" class=\"tsspsv tsspsv-service tsspsv-register\" action=\"#\" method=\"post\">";
			$serv_block .= "<div class=\"headline\">";
				$serv_block .= "<div class=\"service-time\"><strong>" . $time . "</strong> " . $text . "</div>";
				$serv_block .= "<div class=\"service-link\"><button type=\"button\" class=\"" . tsspsv_serv_disabled($service->available,$service->unlimited, $service->closed, 2) . "\" onclick=\"tsspsv_get_form_body(" . $id_service . ")\">" . $button_text . "</button></div>";	
			$serv_block .= "</div>";
			$serv_block .= "<div class=\"formline\">";
				$serv_block .= tsspsv_get_form_body();
			$serv_block .= "</div>";		
			$serv_block .= "<input type=\"hidden\" name=\"id_service\" value=\"" . $id_service . "\">";
		$serv_block .= "</form>";
	}

	return $serv_block;
}

/*
* Form body
*/
function tsspsv_get_form_body() {

	$options = get_option('tsspsv_options');
	$tsspsv_phone_on = (isset($options['tsspsv_phone_on']) ? $options['tsspsv_phone_on'] : '');
	$tsspsv_phone_req = (isset($options['tsspsv_phone_req']) ? $options['tsspsv_phone_req'] : '');
	$tsspsv_confirm_on = (isset($options['tsspsv_confirm_on']) ? $options['tsspsv_confirm_on'] : '');
	$tsspsv_confirm_text = (isset($options['tsspsv_confirm_text']) ? $options['tsspsv_confirm_text'] : '');

	$serv_form = "";

	$serv_form .= "<div class=\"tsspsv_form_body\">";	
		$serv_form .= "<p>";
			$serv_form .= "<label>" . __('Vaše meno','spirit-registration') . "<br><span><input type=\"text\" name=\"your-name\"><span class=\"tsspsv-not-valid-tip\"></span></span></label>";
		$serv_form .= "</p>";

		if ($tsspsv_phone_on) {
			$serv_form .= "<p class=\"tsspsv-phone\">";
				$serv_form .= "<label>" . __('Telefón','spirit-registration') . "<br><span><input type=\"text\" name=\"your-phone\" data-required=\"" . ($tsspsv_phone_req ? 'true' : 'false')  . "\"><span class=\"tsspsv-not-valid-tip\"></span></span></label>";
			$serv_form .= "</p>";	
		}

		$serv_form .= "<p>";
			$serv_form .= "<label><input type=\"checkbox\" name=\"your-email-check\"><span> " . __( 'Poslať potvrdenie na email.', 'spirit-registration' ). "</span></label>";
		$serv_form .= "</p>";
		$serv_form .= "<p class=\"tsspsv-email\" style=\"display: none;\">";
			$serv_form .= "<label>" . __('Vaš Email','spirit-registration') . "<br><span><input type=\"text\" name=\"your-email-confirm\"><span class=\"tsspsv-not-valid-tip\"></span></span></label>";
		$serv_form .= "</p>";	
		
		if ($tsspsv_confirm_on) {
			$serv_form .= "<p>";
				$serv_form .= "<label><input type=\"checkbox\" name=\"your-confirmation\" data-required=\"true\"><span> " . $tsspsv_confirm_text . "</span><span class=\"tsspsv-not-valid-tip\"></span></label>";
			$serv_form .= "</p>";	
		}

		$serv_form .= "<p>";
			$serv_form .= "<label><input type=\"checkbox\" name=\"your-gdpr\"><span> " .  sprintf( wp_kses( __( 'Súhlasím so spracovaním <a href="%s" target="_blank" >osobných údajov</a>.', 'spirit-registration' ), array('a'=>array('href'=>array(), 'target' =>array() ))), esc_url("https://gdpr.kbs.sk/")) . "</span><span class=\"tsspsv-not-valid-tip\"></span></label>";
		$serv_form .= "</p>";					
		$serv_form .= "<p>";
			$serv_form .= "<input type=\"submit\" value=\"" . __('Odoslať','spirit-registration')  ."\" class=\"\">";
			$serv_form .= "<span class=\"ajax-loader\"></span>";
		$serv_form .= "</p>";
		$serv_form .= "<div class=\"tsspsv-response-output\"></div>";
	$serv_form .= "</div>";

	return $serv_form;
}

/*
* Deregistration of participant
*/
function tsspsv_dereg_form($attrs) {
	$attrs = (object) shortcode_atts( array(
   ), $attrs );
  
	  $serv_form = "";
  
	  $serv_form .= "<form action=\"#\" class=\"tsspsv tsspsv-deregister\" method=\"post\">";
		  $serv_form .= "<p>";
			  $serv_form .= "<label>" . __('Svätá Omša','spirit-registration') . "<br><span>" . tsspsv_get_services(2) . "<span class=\"tsspsv-note\">* " . __('Vyberte udalosť, z ktorej sa chcete odhlásiť.','spirit-registration') . "</span><span class=\"tsspsv-not-valid-tip\"></span></span></label>";
		  $serv_form .= "</p>";
		  
		  $serv_form .= "<div class=\"tsspsv_form_body\">";	
		  $serv_form .= "<p>";
			  $serv_form .= "<label>" . __('Vaše Meno','spirit-registration') . "<br><span><input type=\"text\" name=\"your-name\"><span class=\"tsspsv-not-valid-tip\"></span></span></label>";
		  $serv_form .= "</p>";					
		  $serv_form .= "<p>";
			  $serv_form .= "<input type=\"submit\" value=\"" . __('Odoslať','spirit-registration')  ."\" class=\"\">";
			  $serv_form .= "<span class=\"ajax-loader\"></span>";
		  $serv_form .= "</p>";
		  $serv_form .= "<div class=\"tsspsv-response-output\"></div>";
	  $serv_form .= "</div>";
		  
	  $serv_form .= "</form>"; 
  
	  return $serv_form;
  }




/*
* Display day of service with date 
*/
function tsspsv_day_of_service($attrs) {
	$attrs = (object) shortcode_atts( array(
		'day_number' => 0,
		'day' => ''
   ), $attrs );

	$day_number = $attrs->day_number;  
	$calendar_days = tsspsv_get_calendar_days();
	$day_now = date('N', current_time('timestamp')) - 1;
	
   	$future_day = tsspsv_get_future_day($day_number,$day_now);
	
	$day_name = $calendar_days[$day_number] . " " . date('j.n.Y', $future_day);
	$day_name_html =  "<h2 class='has-text-align-center'>" .  $day_name . "</h2>";

	return $day_name_html;
}