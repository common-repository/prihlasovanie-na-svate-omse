(function( $ ) {
    'use strict';

    $( document ).ready(function() {

        tsspsv_register_form_submit();
        tsspsv_deregister_form_submit();

        $('select[name=your-service]').change(function() {
            if ($(this).val() == 0) {
                $(this).closest('form').find('.tsspsv_form_body').hide();
            }
            else {
                $(this).closest('form').find('.tsspsv_form_body').show();
            }
        })

        $('input[name=your-email-check]').change(function() {
            if ($(this).is(':checked')) {
                $(this).closest('form').find('.tsspsv-email').show();
            } 
            else {
                $(this).closest('form').find('.tsspsv-email').hide();
            }
        });

        
    });

})( jQuery );

/*
* Form validation
*/
function tsspsv_validate(event) {
    var your_name = jQuery(event.target).find('input[name=your-name]');
	var your_phone = jQuery(event.target).find('input[name=your-phone]');
    var your_service = jQuery(event.target).find('select');
	var your_confirmation = jQuery(event.target).find('input[name=your-confirmation]');
    var your_gdpr = jQuery(event.target).find('input[name=your-gdpr]');
    var your_email_confirm = jQuery(event.target).find('input[name=your-email-confirm]');
    var form_valid = true;

   //Validate your-name
   if (your_name.val().trim() == "") {
        jQuery(your_name).siblings('.tsspsv-not-valid-tip').text(my_ajax_object.field_required);
       form_valid = false;
   } else {
        jQuery(your_name).siblings('.tsspsv-not-valid-tip').text('');
   }

   //Validate your-name
   if (your_phone.data('required') && your_phone.val().trim() == "") {
        jQuery(your_phone).siblings('.tsspsv-not-valid-tip').text(my_ajax_object.field_required);
       form_valid = false;
   } else {
        jQuery(your_phone).siblings('.tsspsv-not-valid-tip').text('');
   }


   //Validate your-service, if select box exists (not for tsspsv_service shortcode)
    if (your_service.length) {
        if (your_service.val() == 0) {
                jQuery(your_service).siblings('.tsspsv-not-valid-tip').text(my_ajax_object.select_service);
            form_valid = false;
        } else {
                jQuery(your_service).siblings('.tsspsv-not-valid-tip').text('');
        } 
    }      
   
   //Validate your-gdpr
    if (your_gdpr.length) {
        if (!jQuery(your_gdpr).is(':checked')) {
                jQuery(your_gdpr).siblings('.tsspsv-not-valid-tip').text(my_ajax_object.privacy_consent);
                form_valid = false;
        } else {
                jQuery(your_gdpr).siblings('.tsspsv-not-valid-tip').text('');
        }
    }

   //Validate your-confirmation
   if (your_confirmation.length) {
	if (!jQuery(your_confirmation).is(':checked')) {
			jQuery(your_confirmation).siblings('.tsspsv-not-valid-tip').text(my_ajax_object.field_required);
			form_valid = false;
	} else {
			jQuery(your_confirmation).siblings('.tsspsv-not-valid-tip').text('');
	}
}	
   
    //Validate your-email-confirm (if checked)
    if (your_email_confirm.length) {
        if (jQuery(event.target).find('input[name=your-email-check]').is(':checked')) {

            jQuery(your_email_confirm).val(jQuery(your_email_confirm).val().trim());
        
            if (!tsspsv_validate_email(jQuery(your_email_confirm).val())) {
                    jQuery(your_email_confirm).siblings('.tsspsv-not-valid-tip').text(my_ajax_object.email_incorrect_format);
                    form_valid = false;
            } else {
                    jQuery(your_email_confirm).siblings('.tsspsv-not-valid-tip').text('');
            }
        }
    }
     
   return form_valid;

};

/*
* Registration form submit
*/
function tsspsv_register_form_submit(){

    jQuery('form.tsspsv-register').submit( function( event ) {
        var form_valid = true;

        event.preventDefault();

        form_valid = tsspsv_validate(event);

        if (!form_valid) {
            return false;
        }
        else{
            jQuery(event.target).find('.ajax-loader').css('visibility', 'visible');

            var data = {
                action: 'tsspsv_submit_form',
                your_service: (jQuery(event.target).find('select').length ? jQuery(event.target).find('select').val() : jQuery(event.target).find('input[name=id_service]').val() ),
                your_name: jQuery(event.target).find('input[name=your-name]').val(),
				your_phone: (jQuery(event.target).find('input[name=your-phone]').length > 0 ? jQuery(event.target).find('input[name=your-phone]').val() : ''),
                your_email_check: (jQuery(event.target).find('input[name=your-email-check]').is(':checked') ? '1' : '0'),
                your_email_confirm: jQuery(event.target).find('input[name=your-email-confirm]').val()
            };

            var response_data = "";

            jQuery.ajax(
                {
                    type: "post",
                    url: my_ajax_object.ajax_url,
                    data: data,
                    success: function(response){
                        jQuery(event.target).find('.ajax-loader').css('visibility', 'hidden');

                        response_data = JSON.parse(response);

                        if (response_data.status == "OK") {
                            
                            jQuery(event.target).removeClass('form-full');
                            jQuery(event.target).addClass('form-ok');
                            jQuery(event.target).find('.tsspsv-response-output').html("<strong>" + response_data.message + "</strong><br>" + my_ajax_object.service + ": " + response_data.service_name + "<br>" + my_ajax_object.name  + ": " + response_data.name);
                            jQuery(event.target).find('input[name=your-name]').val('');
                            
                            //Update capacity
							if (response_data.unlimited == 0) {
								if (jQuery(event.target).find('select').length) {
									jQuery(event.target).find('select option[value=' + response_data.id_service  + ']').text(response_data.service_name + " (" + response_data.capacity + ")");
								}
								else {
									jQuery(event.target).find('button').text(my_ajax_object.sign_up_text + ' (' + response_data.capacity + ')')
								}
							}        
                        }

                        if (response_data.status == "FULL") {
                            jQuery(event.target).addClass('form-full');
                            jQuery(event.target).removeClass('form-ok');
                            jQuery(event.target).find('.tsspsv-response-output').text(response_data.message);
                        }

                        if (response_data.status == "ERROR") {
                            jQuery(event.target).addClass('form-full');
                            jQuery(event.target).removeClass('form-ok');
                            jQuery(event.target).find('.tsspsv-response-output').text(response_data.message);
                        }                            

                        jQuery(event.target).find('.tsspsv-response-output').show();
                    }
                }
            );
        }
    });
}

/*
* Deregistration form submit
*/
function tsspsv_deregister_form_submit(){

    jQuery('form.tsspsv-deregister').submit( function( event ) {
        var form_valid = true;

        event.preventDefault();

        form_valid = tsspsv_validate(event);

        if (!form_valid) {
            return false;
        }
        else{
            jQuery(event.target).find('.ajax-loader').css('visibility', 'visible');

            
            var data = {
                action: 'tsspsv_submit_dereg_form',
                your_service: (jQuery(event.target).find('select').length ? jQuery(event.target).find('select').val() : jQuery(event.target).find('input[name=id_service]').val() ),
                your_name: jQuery(event.target).find('input[name=your-name]').val()
            };

            var response_data = "";

            jQuery.ajax(
                {
                    type: "post",
                    url: my_ajax_object.ajax_url,
                    data: data,
                    success: function(response){
                        jQuery(event.target).find('.ajax-loader').css('visibility', 'hidden');

                        response_data = JSON.parse(response);

                        if (response_data.status == "OK") {
                            
                            jQuery(event.target).removeClass('form-full');
                            jQuery(event.target).addClass('form-ok');
                            jQuery(event.target).find('.tsspsv-response-output').html("<strong>" + response_data.message + "</strong><br>" + my_ajax_object.service + ": " + response_data.service_name + "<br>" + my_ajax_object.name  + ": " + response_data.name);
                            jQuery(event.target).find('input[name=your-name]').val('');
                        }

                        if (response_data.status == "ERROR") {
                            jQuery(event.target).addClass('form-full');
                            jQuery(event.target).removeClass('form-ok');
                            jQuery(event.target).find('.tsspsv-response-output').text(response_data.message);
                        }                            

                        jQuery(event.target).find('.tsspsv-response-output').show();
                    }
                }
            );

        }
    });
}

/*
* Email validation script
*/
function tsspsv_validate_email(your_email_confirm) 
{
    if (/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(your_email_confirm))
    {
        return true;
    }
    
    return false;
}

/*
* Slide down effect when opening form
*/
function tsspsv_get_form_body(id_service) {
    if( jQuery('#tsspsv-service-' + id_service + ' .tsspsv_form_body').is(":visible") ) {
        jQuery('#tsspsv-service-' + id_service + ' .tsspsv_form_body').slideUp("slow");
    }
    else {
        jQuery('.tsspsv-service .tsspsv_form_body').slideUp("slow");
        jQuery('#tsspsv-service-' + id_service + ' .tsspsv_form_body').slideDown("slow");
    }
}