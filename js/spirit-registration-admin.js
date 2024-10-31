(function( $ ) {
    'use strict';

    $( document ).ready(function() {
        tsspsv_select_serv();
        tsspsv_refresh_table_controls();
    });
   
})( jQuery );

/*
* Print div
*/
function tsspsv_printDiv(divName) {
    var printContents = jQuery(divName).html();
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;

    window.print();

    document.body.innerHTML = originalContents;

    tsspsv_refresh_table_controls();
    tsspsv_select_serv();
}

/*
* Save service information
*/
function tsspsv_save_edit(id_table) {

    //Check if Add or Edit operation
    if (jQuery(id_table + ' tr.new').length > 0) {
        tsspsv_add_service_save(id_table);
        return;
    }

    var checked_serv_ids = [];
    var serv_actives = [];
    var serv_names = [];
    var serv_hours = [];
    var serv_days = [];
    var serv_minutes = [];
    var serv_capacities = [];
	var serv_unlimited = [];
    var serv_rc_day = [];
    var serv_rc_hour = [];

    jQuery(id_table + ' .check-column input[type=checkbox]:checked').not('[value=0]').each(function() {
        checked_serv_ids.push(jQuery(this).val());
        serv_actives.push( (jQuery(this).parent().parent().find('input[name=service_active]').is(':checked') ? 1 : 0) );
        serv_names.push(jQuery(this).parent().parent().find('.tsspsv-service-name').val());
        serv_hours.push(jQuery(this).parent().parent().find('select[name=serv_hour]').val());
        serv_minutes.push(jQuery(this).parent().parent().find('select[name=serv_minute]').val());
        serv_days.push(jQuery(this).parent().parent().find('select[name=serv_day]').val());
        serv_capacities.push(jQuery(this).parent().parent().find('input[name=service_capacity]').val());
		serv_unlimited.push((jQuery(this).parent().parent().find('input[name=service_unlimited]').is(':checked') ? 1 : 0) );
        serv_rc_day.push(jQuery(this).parent().parent().find('select[name=rc_day]').val());
        serv_rc_hour.push(jQuery(this).parent().parent().find('select[name=rc_hour]').val());
    });
    
    var data = {
        action: 'tsspsv_save_service_edit',
        checked_serv_ids: checked_serv_ids.toString(),
        serv_actives: serv_actives.toString(),
        serv_names: serv_names.toString(),
        serv_hours: serv_hours.toString(),
        serv_minutes: serv_minutes.toString(),
        serv_days: serv_days.toString(),
        serv_capacities: serv_capacities.toString(),
		serv_unlimited: serv_unlimited.toString(),
        serv_rc_day: serv_rc_day.toString(),
        serv_rc_hour: serv_rc_hour.toString()
    };

    jQuery.ajax(
        {
            type: "post",
            url: my_ajax_object.ajax_url,
            data: data,
            success: function(response){
                jQuery(id_table + ' .check-column input[type=checkbox]:checked').not('[value=0]').parent().parent().find('td').each(function() {
                    if(jQuery(this).hasClass('tsspsv-edit') && !jQuery(this).hasClass('tsspsv-check')) {
                        jQuery(this).find('.tsspsv-read-value').text(jQuery(this).find('.tsspsv-edit-value input').val());
                    }
                    
                    if(jQuery(this).hasClass('tsspsv-check')) {
                        jQuery(this).find('.tsspsv-read-value input[name=service_active_readonly]').prop('checked', jQuery(this).find('.tsspsv-edit-value input[name=service_active]').is(':checked'));
						jQuery(this).find('.tsspsv-read-value input[name=service_unlimited_readonly]').prop('checked', jQuery(this).find('.tsspsv-edit-value input[name=service_unlimited]').is(':checked'));
					}
                });

                jQuery(id_table + ' tbody tr').each(function() {
                    var serv_minute = jQuery(this).find('select[name=serv_minute] option:selected').val();

                    jQuery(this).find('.tsspsv-shortcode span').text('[tsspsv_service id="' + jQuery(this).find('.check-column input').val() + '" time="' + jQuery(this).find('select[name=serv_hour] option:selected').val() + ':' + (serv_minute.length == 1 ? "0" + serv_minute : serv_minute) + '" text="' + jQuery(this).find('input[name=service_name]').val() + '"]');
                });
                
                tsspsv_cancel_edit(id_table);
            }
        } 
    )     
    
}

/*
* Update input value of closing date and time
*/
function tsspsv_update_cl_time(e) {
    jQuery(e).parent().find('input[name=rc_input]').val(  jQuery(e).parent().find('select[name=rc_day] option:selected').text() + ' ' + jQuery(e).parent().find('select[name=rc_hour] option:selected').text());
}

/*
* Update input value of closing date and time
*/
function tsspsv_update_serv_time(e) {
    jQuery(e).parent().find('input[name=serv_input]').val(  jQuery(e).parent().find('select[name=serv_hour] option:selected').text() + ':' + jQuery(e).parent().find('select[name=serv_minute] option:selected').text() + " " + jQuery(e).parent().find('select[name=serv_day] option:selected').text());
}

/*
* Add new service - show blank row
*/
function tsspsv_add_service(id_table) {

    //Get new service row
    var data = {
        action: 'tsspsv_new_service_row'
    };

    jQuery.ajax(
        {
            type: "post",
            url: my_ajax_object.ajax_url,
            data: data,
            success: function(response){
                jQuery(id_table + " tbody").prepend(response);

                jQuery('.button.edit').show();
                jQuery('.button.action').hide();
            }
        } 
    )  
}

/*
* Add new service - save data
*/
function tsspsv_add_service_save(id_table) {
    var new_serv_name = jQuery('tr.new .tsspsv-service-name').val();
    var new_serv_day = jQuery('tr.new select[name=serv_day]').val();
    var new_serv_hour = jQuery('tr.new select[name=serv_hour]').val();
    var new_serv_minute = jQuery('tr.new select[name=serv_minute]').val();
    var new_serv_capacity =  jQuery('tr.new .tsspsv-service-capacity').val();
	var new_serv_unlimited =  (jQuery('tr.new .tsspsv-service-unlimited').is(':checked') ? 1 : 0);
    var new_serv_order =  jQuery(id_table + ' tbody tr').length;
    var new_closing_day =  jQuery('tr.new select[name=rc_day]').val();
    var new_closing_hour =  jQuery('tr.new select[name=rc_hour]').val();

    if (new_serv_name && new_serv_capacity &&  new_serv_order) {

        jQuery(id_table).find('.ajax-loader').css('visibility', 'visible');

        var data = {
            action: 'tsspsv_add_service_save',
            new_serv_name: new_serv_name,
            new_serv_day: new_serv_day,
            new_serv_hour: new_serv_hour,
            new_serv_minute: new_serv_minute,
            new_serv_capacity: new_serv_capacity,
			new_serv_unlimited: new_serv_unlimited,
            new_serv_order: new_serv_order,
            new_closing_day: new_closing_day,
            new_closing_hour: new_closing_hour
        };
    
        jQuery.ajax(
            {
                type: "post",
                url: my_ajax_object.ajax_url,
                data: data,
                success: function(response){
                    //Reload services table
                    data = {
                        action: 'tsspsv_services_table'
                    };

                    jQuery.ajax(
                        {
                            type: "post",
                            url: my_ajax_object.ajax_url,
                            data: data,
                            success: function(response){
                                jQuery('#tsspsv-services-table').html(response);
                                jQuery(event.target).find('.ajax-loader').css('visibility', 'hidden');
                                tsspsv_refresh_table_controls();
                            }
                        }
                    )

                }
            } 
        ) 
    }
    else {
        alert('Prosím zadajte názov sv. omše a jej aktuálnu kapacitu.');
    }
}

/*
* Cancel edit on service screen
*/
function tsspsv_cancel_edit(id_table) {
    jQuery(id_table + ' .check-column input[type=checkbox]:checked').not('[value=0]').each(function() {
        jQuery(this).parent().parent().find('.tsspsv-read-value').show();
        jQuery(this).parent().parent().find('.tsspsv-edit-value').hide();
    });

    //Check if Add or Edit operation
    if (jQuery(id_table + ' tr.new')) {
        jQuery(id_table + ' tr.new').remove();
        jQuery(id_table + ' #add_service').show();
    }
    else {
        jQuery('.button.action').show();
    }

    jQuery('.button.edit').hide();

    jQuery('.check-column input[type=checkbox]').prop("checked", false);
}

/*
* Show editable fields on services screen
*/
function tsspsv_edit_service(id_table) {
    jQuery(id_table + ' .check-column input[type=checkbox]:checked').not('[value=0]').each(function() {
        jQuery(this).parent().parent().find('.tsspsv-read-value').hide();
        jQuery(this).parent().parent().find('.tsspsv-edit-value').show();
    });

    jQuery('.button.edit').show();
    jQuery('.button.action').hide();
}

/*
* Delete row - both Services + Registration table
*/
function tsspsv_delete_reg(id_table) {

    var checked_regs = [];
    var checked_regs_ids = [];

    if ( confirm('Naozaj chcete odstrániť označené záznamy?') ) {

        jQuery('.check-column input[type=checkbox]:checked').not('[value=0]').each(function() {
            checked_regs.push(jQuery(this).val());
            checked_regs_ids.push(jQuery(this).attr('id'));
        });

        var data = {
            action: 'tsspsv_delete_record_admin',
            checked_regs: checked_regs.toString(),
            id_table: id_table

        };

        jQuery.ajax(
            {
                type: "post",
                url: my_ajax_object.ajax_url,
                data: data,
                success: function(response){

                    if (jQuery('select[name=your-service]').length) {
                        jQuery('select[name=your-service]').trigger( "change" );
                    }
                    else {
                        jQuery(checked_regs_ids).each(function() {
                            jQuery('#' + this).closest('tr').remove();
                        });
                    }
                    jQuery('#deletereg').hide();

                    tsspsv_reindex_reg_table();
                }
            } 
        )          
    }
    else {
        return false;
    }

}

/*
* Refresh services table
*/
function tsspsv_select_serv() {
    jQuery('select[name=your-service]').change( function( event ) {

		var mode = jQuery(event.target).data('mode');
		var action = (mode != 3 ? 'tsspsv_registration_table' : 'tsspsv_registration_history_table');

        var data = {
            action: action,
            your_service: jQuery(event.target).val(),
			your_serv_date: (mode == 3 ? jQuery(event.target).find(':selected').data('serv_date') : '')
        };

        jQuery.ajax(
            {
                type: "post",
                url: my_ajax_object.ajax_url,
                data: data,
                success: function(response){
                    jQuery('#tsspsv-reg-table').html(response);
                    tsspsv_refresh_table_controls();
                }
            } 
        )           

    });        
}

/*
* Export registrants table
*/
function tsspsv_csv_export(your_service) {
	var data = {
		action: 'tsspsv_csv_export',
		your_service: your_service
	};

	jQuery.ajax(
		{
			type: "post",
			url: my_ajax_object.ajax_url,
			data: data,
			success: function(response){
				response_data = JSON.parse(response);

				if (response_data.status == "OK") {
					window.location = '../wp-content/plugins/prihlasovanie-na-svate-omse/export/' + response_data.filename;
				}

				if (response_data.status == "ERROR") {
					alert('Export zlyhal, kontaktuje autora pluginu.');
				}
			}
		} 
	)  
}

/*
* Refresh table controls
*/
function tsspsv_refresh_table_controls() {
    jQuery('.check-column input[type=checkbox]').change(function(event) {
        
        if (jQuery('.check-column input[type=checkbox]:checked').length > 0 ) {
            jQuery('#deletereg').show();
            jQuery('#edit_service').show();
        }
        else {
            jQuery('#deletereg').hide();
            jQuery('#edit_service').hide();
        }
    });
}

/*
* Reorder services in table
*
* direction = 1 => down, direction = 0 => up
*/
function tsspsv_change_order(e,direction) {
    var row = jQuery(e).parents("tr:first");
    var order_id = jQuery(row).find('td.order-column').data('order');
    var order_id_to_move = 0;
    var serv_id_main = jQuery(row).find('th.check-column input').val();
    var serv_id_to_move = 0;
    var row_count = jQuery(row).parent().find('tr').length;

    if (direction == 1) { // move up
        if (order_id == 1) return;    
        serv_id_to_move = jQuery(row.prev()).find('th.check-column input').val();
        order_id_to_move = order_id-1;
    } else { // move down
        if (order_id == row_count) return;
        serv_id_to_move = jQuery(row.next()).find('th.check-column input').val();
        order_id_to_move = order_id+1;
    }

    //Update ordering in DB
    var data = {
        action: 'tsspsv_reorder_services',
        serv_id_main: serv_id_main,
        serv_id_to_move: serv_id_to_move,
        serv_order_id_main: order_id,
        serv_order_id_to_move: order_id_to_move

    };

    jQuery.ajax(
        {
            type: "post",
            url: my_ajax_object.ajax_url,
            data: data,
            success: function(response){

                if (direction == 1) {
                    jQuery(row.prev()).find('td.order-column').data('order',order_id);
                    jQuery(row.prev()).find('.serv_order').text(order_id);     
                    row.insertBefore(row.prev()).hide().show('slow');
                    jQuery(row).find('td.order-column').data('order',order_id_to_move);
                    jQuery(row).find('.serv_order').text(order_id_to_move); 
                }
                else {
                    jQuery(row.next()).find('td.order-column').data('order',order_id);
                    jQuery(row.next()).find('.serv_order').text(order_id);
                    row.insertAfter(row.next()).hide().show('slow');   
                    jQuery(row).find('td.order-column').data('order',order_id_to_move);
                    jQuery(row).find('.serv_order').text(order_id_to_move);                                  
                }
            }
        } 
    )               
}


/*
* Reindex reginstration table
*/
function tsspsv_reindex_reg_table() {
    var i=1;

    jQuery('#tsspsv-reg-table table tbody tr').each(function() {
        jQuery(this).find('.index-column').text(i + ".");
        i++;
    });
}