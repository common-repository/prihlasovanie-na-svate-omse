<?php
/*
* Everything related to Wordpress administration.
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if (is_admin()) {
    add_action('init', 'tsspsv_admin_init');
    add_action('admin_init', 'tsspsv_admin_settings');
    add_action('admin_enqueue_scripts', 'tsspsv_admin_enqueue_styles' ); //Register styles for Admin
    add_action('admin_enqueue_scripts', 'tsspsv_admin_enqueue_scripts' ); //Register scripts for Admin

    add_action('wp_ajax_tsspsv_registration_table', 'tsspsv_registration_table_callback'); 
	add_action('wp_ajax_tsspsv_registration_history_table', 'tsspsv_registration_history_table_callback'); 
    add_action('wp_ajax_tsspsv_services_table', 'tsspsv_services_table_callback'); 
    add_action('wp_ajax_tsspsv_delete_record_admin', 'tsspsv_delete_record_admin_callback');  
    add_action('wp_ajax_tsspsv_save_service_edit', 'tsspsv_save_service_edit_callback');  
    add_action('wp_ajax_tsspsv_add_service_save', 'tsspsv_add_service_save_callback'); 
    add_action('wp_ajax_tsspsv_new_service_row', 'tsspsv_new_service_row_callback'); 
    add_action('wp_ajax_tsspsv_reorder_services', 'tsspsv_reorder_services_callback'); 
	add_action('wp_ajax_tsspsv_csv_export', 'tsspsv_csv_export_callback');
}

/*
* Initiate admin menu
*/
function tsspsv_admin_init() {
    add_action( 'admin_menu', 'tsspsv_admin_menu' );
}

/*
* Enqueue admin styles
*/
function tsspsv_admin_enqueue_styles() {

    wp_enqueue_style('spirit-registration-admin-css', plugin_dir_url( __FILE__ ) . '../css/spirit-registration-admin.css', array(), TSSPSV_VERSION, 'all' );
}

/*
* Enqueue admin scripts
*/
function tsspsv_admin_enqueue_scripts() {

    wp_enqueue_script('spirit-registration-admin-js', plugin_dir_url( __FILE__ ) . '../js/spirit-registration-admin.js', array('jquery'), TSSPSV_VERSION, 'false' );
    wp_localize_script( 'spirit-registration-admin-js', 'my_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

/*
* Register menu
*/
function tsspsv_admin_menu() { 

	$tsspsv_options = get_option('tsspsv_options');
	$access_rights = 'manage_options';

	$tsspsv_editor_on = (isset($tsspsv_options['tsspsv_editor_on']) ? $tsspsv_options['tsspsv_editor_on'] : '');
	
	if ($tsspsv_editor_on) {
		$access_rights = 'manage_categories';
	}

    add_menu_page(
		__( 'Prihlasovanie na sv. omše','spirit-registration'),
		__( 'Prihlasovanie na sv. omše','spirit-registration'),
		$access_rights,
		'spirit-registration',
		'tsspsv_registrations_page',
		'dashicons-smiley',
		50
    );

	add_submenu_page(
		'spirit-registration', 
		__('História', 'spirit-registration'),
		__('História', 'spirit-registration'), 
		$access_rights,
		'tsspsv_registrations_history_page',
		'tsspsv_registrations_history_page'
	); 

    add_submenu_page(
		'spirit-registration', 
		__('Nastavenia', 'spirit-registration'),
		__('Nastavenia', 'spirit-registration'), 
		$access_rights,
		'tsspsv_settings_page',
		'tsspsv_settings_page'
); 
}

/*
* Registration admin page HTML
*/
function tsspsv_registrations_page() { 
 
?>    
    <div class="wrap columns-2 dd-wrap">
	<div style="margin-bottom: 20px;">
		<h1><?php _e('Prihlasovanie na sv. omše', 'spirit-registration'); ?></h1>
	</div>		
    
    <div id="poststuff" class="metabox-holder has-right-sidebar tsspsv">
        <div id="post-body">
            <div id="post-body-content">
					<div class="postbox">
						<div class="inside">
							<table class="form-table" style="max-width:500px;">
								<tr valign="top">
									<th><label><?php _e('Svätá omša', 'spirit-registration'); ?>:</label></th>
									<td><?php echo tsspsv_get_services(0); ?></td>
								</tr>                             
                            </table> 							
						</div>
					</div>

                    <div id="tsspsv-reg-table">
                    <!-- Table loaded by AJAX -->
                    </div>
				
			</div>                
        </div>
    </div>
</div>

<?php    
}

/*
* Registration history admin page HTML
*/
function tsspsv_registrations_history_page() { 
 
	?>    
		<div class="wrap columns-2 dd-wrap">
		<div style="margin-bottom: 20px;">
			<h1><?php _e('História prihlasovania', 'spirit-registration'); ?></h1>
		</div>		
		
		<div id="poststuff" class="metabox-holder has-right-sidebar tsspsv">
			<div id="post-body">
				<div id="post-body-content">
						<div class="postbox">
							<div class="inside">
								<table class="form-table" style="max-width:500px;">
									<tr valign="top">
										<th><label><?php _e('Svätá omša', 'spirit-registration'); ?>:</label></th>
										<td><?php echo tsspsv_get_services(3); ?></td>
									</tr>                             
								</table> 	
								<p><?php _e('Záznamy sú po 14 dňoch automaticky odstránené z databázy.', 'spirit-registration'); ?></p>						
							</div>
						</div>
	
						<div id="tsspsv-reg-table">
						<!-- Table loaded by AJAX -->
						</div>
					
				</div>                
			</div>
		</div>
	</div>
	
	<?php    
	}

/*
* Settings admin page HTML
*/
function tsspsv_settings_page() { 
    ?>    
    <div class="wrap columns-2 dd-wrap">
	<div style="margin-bottom: 20px;">
		<h1><?php _e('Nastavenia', 'spirit-registration'); ?></h1>
	</div>		
    
    <div id="poststuff" class="metabox-holder tsspsv"> <!-- has-right-sidebar -->
        <div id="post-body">
            <div id="post-body-content">
                <div id="tsspsv-services-table">
                    <?php tsspsv_get_services_table(); ?>
                </div>
				
                <!-- WP_Cron settings -->
                <div class="postbox" style="margin-top: 40px;">
                        <h3 class="hndle"><?php _e('Ďalšie nastavenia', 'spirit-registration'); ?></h3>
                        <div class="inside">
							<?php
								//Prepare date for [tsspsv_day_of_service]
								$calendar_days = tsspsv_get_calendar_days();
								$day_now = date('N', current_time('timestamp')) - 1;
							
								$future_day = tsspsv_get_future_day(6,$day_now);
								$day_name = $calendar_days[6] . " " . date('j.n.Y', $future_day);

							?>
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th><?php _e('Shortcode pre všetky omše', 'spirit-registration'); ?></th>
                                        <td class="tsspsv-shortcode"><span>[tsspsv_form]</span></td>
                                    </tr>
                                    <tr>
                                        <th><?php _e('Shortcode odhlásenie účastníka', 'spirit-registration'); ?></th>
                                        <td class="tsspsv-shortcode"><span>[tsspsv_dereg_form]</span></td>
                                    </tr>    	
                                    <tr>
                                        <th><?php _e('Shortcode pre zobrazenie dátumu', 'spirit-registration'); ?></th>
                                        <td class="tsspsv-shortcode"><span>[tsspsv_day_of_service day_number="6"]</span> (Zobrazí: <?php echo $day_name; ?>)</td>
                                    </tr>   																                                
                                </tbody>
                            </table>                
                            <form method="post" action="options.php">	
                                <?php 
                                    settings_fields('tsspsv_options');
                                    do_settings_sections('tsspsv_settings_page');
                                    submit_button('Uložiť', 'spirit-registration');
                                ?>
                        </div>
                    </div>
                </form> 
            </div>                            
        </div>
    </div>
</div>

<?php     
}