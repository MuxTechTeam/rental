/*global $, jQuery, ajaxcalls_vars, document, control_vars, mapbase_vars,window, control_vars, submit_change,wpestate_timeConverter, ajaxcalls_add_vars, dashboard_vars, google, wprentals_google_fillInAddress, wpestate_check_booking_valability_internal, wpestate_mark_as_booked_actions*/
jQuery(document).ready(function ($) {
    "use strict";
    var curent_m_set=2;
    var curr_max_months = 12;
     $('#calendar-prev-internal-allinone').on('click',function () {
        if (curent_m_set > 1) {
            curent_m_set = curent_m_set - 1;
        } else {
            curent_m_set = 1;
        }

        $('.booking-calendar-wrapper-allinone').hide();
        $('.booking-calendar-wrapper-allinone').each(function () {
            var curent;
            curent   =   parseInt($(this).attr('data-mno'), 10);
            if (curent === curent_m_set ) {
                //$(this).fadeIn();
                 $(this).css('display','inline-block');
            }
        });
    });

    $('#calendar-next-internal-allinone').on('click',function () {

        if (curent_m_set < (curr_max_months-2) )  {
            curent_m_set = curent_m_set + 1;
        } else {
            curent_m_set = curr_max_months-1;
        }
        console.log(curent_m_set);
        $(".booking-calendar-wrapper-allinone ").hide();
        $('.booking-calendar-wrapper-allinone ').each(function () {
            var curent;
            curent   =   parseInt($(this).attr('data-mno'), 10);
            if (curent === curent_m_set ) {
               // $(this).fadeIn();
                $(this).css('display','inline-block');
            }
        });

    });

    jQuery(".calendar_pad").on("mouseenter", function(event) {
           var timeunix=$(this).attr('data-curent-date');
           jQuery(".calendar_pad[data-curent-date=" + timeunix + "]").addClass('calendar-pad-hover');
           jQuery(".calendar_pad_title[data-curent-date=" + timeunix + "]").addClass('calendar-pad-hover');


           if( jQuery(this).hasClass('calendar-reserved-admin') ){
               console.log("run from admin..");
               var reservation_data=$(this).find('.allinone_reservation');
               reservation_data.show();
               var internal_booking_id =   parseFloat( $(this).find('.allinone_reservation').attr('data-internal-reservation'),10);
               if (!isNaN(internal_booking_id) && internal_booking_id!=0 ){
                   var ajaxurl     =   ajaxcalls_vars.admin_url + 'admin-ajax.php';

                    var nonce = jQuery('#wprentals_allinone').val();
                   jQuery.ajax({
                       type: 'POST',
                       url: ajaxurl,

                       data: {
                           'action'                  :   'wpestate_get_booking_data',
                           'internal_booking_id'     :   internal_booking_id,
                           'security'                  : nonce ,
                           'fromAdminPanel' :1
                       },
                       success: function (data) {
                           reservation_data.empty().append(data);

                       },
                       error: function (errorThrown) {}
                   });//end ajax
               }
           }

       });

});