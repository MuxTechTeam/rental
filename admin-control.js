/*global $, jQuery, document, window, tb_show, tb_remove ,admin_control_vars_new, booking_array*/
jQuery(document).ready(function ($) {
    booking_array=[];
    var icon_field;
    $('.input-group-addon').on('click',function(event){
          $('.iconpicker-items_wrapper').show();
          $('.icon_look_for_class').val('');
          $('.iconpicker-item').show();
          icon_field = $(this).parent().find('.icp-auto');
    });

    $('.iconpicker-items_wrapper_close').on('click',function(event){
        $('.iconpicker-items_wrapper').hide();
    });

    $('.iconpicker-item').on('click',function(event){
        event.preventDefault();
        var value = $(this).find('i').attr('class');
        icon_field.val(value);
        $('.iconpicker-items_wrapper').hide();
    });


    $('.icon_look_for_class').keydown(function(event){

        var look_for= $(this).val();
        var title, search_term,parent;
        parent = $(this).parent();

        if(look_for!==''){
            parent.find('.iconpicker-item').each(function() {
                title       = $(this).attr('title');
                search_term = $(this).attr('data-search-terms');

                if(typeof title==='undefined'){
                    title='';
                }
                if(typeof search_term==='undefined'){
                    search_term='';
                }


                if(title.indexOf(look_for) !== -1 || search_term.indexOf(look_for) !== -1){
                    $(this).show();
                }else{
                    $(this).hide();
                }

            });
        }else{
            parent.find('.iconpicker-item').show();
        }
    });


    $('.css_modal_close').on('click',function(){
        $('#css_modal').hide();
    });

    $('#copycsscode').on('click',function(){
        $('#css_modal').html();

    });

    $('#check_email').on('click',function(){
        var user_email,ajaxurl;
        user_email     = $("#user_email").val();
        ajaxurl     =   admin_control_vars_new.ajaxurl;
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action'        :   'wpestate_custom_email_check',
                'user_email'    :   user_email,
            },
            success: function (json) {
                var obj = jQuery.parseJSON( json );

                if ( obj.message == 'blank_email' ) {
                    alert('Please enter a valid email address');
                } else if ( obj.message == 'email_not_found' ) {
                    // alert('Entered email address is a registered user, please create a new user');
                    jQuery('.custom-field').hide();
                    jQuery('#firstname').val("");
                    jQuery('#lastname').val("");
                    jQuery('#phoneno').val("");
                    if (confirm('Entered email address is a registered user, please create a new user')) {
                        // user clicked on Yes
                        window.open(document.location.origin+'/wp-admin/user-new.php', '_blank');
                    }
                } else {
                    jQuery('.custom-field').show();
                    jQuery('#firstname').val( obj.firstname );
                    jQuery('#lastname').val( obj.lastname );
                    jQuery('#phoneno').val( obj.phone );
                    jQuery('#user_id').val( obj.user_id );
                    alert('User is already registered');
                }

            },
            error: function (errorThrown) {
            }
        });

    });

    from_backend = 0;
    jQuery("#edit_invoice").click(function() {
        var booking_id, property_id;
        jQuery( ".display-on-click" ).show();
        booking_id  = jQuery("#booking_id").val();
        property_id = jQuery("#property_id").val();
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action'             :   'wpestate_set_admin_variables',
                'property_id'        :   property_id,
                'booking_id'         :   booking_id,
            },
            success: function (json) {
                var obj = jQuery.parseJSON( json );
                check_in_out_enable2('booking_from_date', 'booking_to_date', obj.min_days_booking, obj.date_format );
                from_backend  = 1;
                booking_array   = JSON.parse (obj.booking_array);
            },
            error: function (errorThrown) {
            }
        });
    });

    //////////////////////////////////////////////////////////////////////////////////////
    /// remind email
    ///////////////////////////////////////////////////////////////////////////////////////

    $('.full_invoice_reminder').on('click',function () {
        var invoice_id, booking_id, acesta, parent, amt_to_be_sent;
        booking_id  =   $(this).attr('data-bookid');
        invoice_id  =   $(this).attr('data-invoiceid');
        acesta      =   $(this);
        parent      =   $(this).parent().parent();
        amt_to_be_sent = jQuery('#amt_to_be_sent').val();
        $(this).text('Sending...');
        $(this).unbind('click');
        acesta=$(this);
        var nonce = jQuery('#wprentals_bookings_actions').val();

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action'            :   'wpestate_send_full_pay_reminder',
                'invoice_id'        :   invoice_id,
                'booking_id'        :   booking_id,
                'amt_to_be_sent'    :   amt_to_be_sent,
                'security'          :   nonce,
            },
            success: function (json) {

                var obj = jQuery.parseJSON( json );
                if ( obj.message == 'validation' ) {
                    alert('The amount should be less than the remaining balance!');
                    window.location.reload();
                    acesta.text('Send reminder email!');
                } else {
                    jQuery('#reservation_fee').text(obj.amt_to_be_sent);
                    acesta.text('Sent');
                }
            },
            error: function (errorThrown) {

            }
        });
    });

    $('#property_select').on('change',function(){
        var property_id, booking_id;
        property_id = $(this).chosen().val();
        jQuery('#listing_edit').val( property_id );
        booking_id = jQuery('#booking_id').val();
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action'             :   'wpestate_set_admin_variables',
                'property_id'        :   property_id,
                'booking_id'         :   booking_id,
            },
            success: function (json) {
                var obj = jQuery.parseJSON( json );
                check_in_out_enable2('booking_from_date', 'booking_to_date', obj.min_days_booking, obj.date_format );
                booking_array   = JSON.parse (obj.booking_array);
                from_backend = 0;
            },
            error: function (errorThrown) {
            }
        });
    });

    jQuery('.add_inv_expenses,.add_inv_discount').on('click',function (){
        var acesta=jQuery(this);
        wpestate_recreate_invoice_manual_expenses_new(acesta);
    });

    $('#activate_pack_reservation_fee').on('click',function(){
        var book_id, invoice_id,ajaxurl,type;
        jQuery(this).text(admin_control_vars_new.processing);
        book_id     = $(this).attr('data-item');
        invoice_id  = $(this).attr('data-invoice');
        type        = $(this).attr('data-type');
        ajaxurl     =   admin_control_vars_new.ajaxurl;
        var nonce = jQuery('#wprentals_activate_pack').val();
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
        data: {
            'action'        :   'wpestate_admin_activate_reservation_fee',
            'book_id'       :   book_id,
            'invoice_id'    :   invoice_id,
            'security'      :   nonce,
        },
        success: function (data) {
            jQuery("#activate_pack_reservation_fee").remove();
            jQuery("#invnotpaid").remove();
        },
        error: function (errorThrown) {
        }
    });//end ajax

    });



     $('#activate_pack_listing').on('click',function(){
        var item_id, invoice_id,ajaxurl,type;

        item_id     = $(this).attr('data-item');
        invoice_id  = $(this).attr('data-invoice');
        type        = $(this).attr('data-type');
        ajaxurl     =   admin_control_vars_new.ajaxurl;
        var nonce = jQuery('#wprentals_activate_pack').val();
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
        data: {
            'action'        :   'wpestate_activate_purchase_listing',
            'item_id'       :   item_id,
            'invoice_id'    :   invoice_id,
            'type'          :   type,
            'security'      :   nonce,

        },
        success: function (data) {
            jQuery("#activate_pack_listing").remove();
            jQuery("#invnotpaid").remove();


        },
        error: function (errorThrown) {}
    });//end ajax

    });

     ///////////////////////////////////////////////////////////////////////////////
    /// activate purchase
    ///////////////////////////////////////////////////////////////////////////////

     $('#activate_pack').on('click',function(){
        var item_id, invoice_id,ajaxurl;

        item_id     =   $(this).attr('data-item');
        invoice_id  =   $(this).attr('data-invoice');
        ajaxurl     =   admin_control_vars_new.ajaxurl;
        var nonce = jQuery('#wprentals_activate_pack').val();


        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
        data: {
            'action'        :   'wpestate_activate_purchase',
            'item_id'       :   item_id,
            'invoice_id'    :   invoice_id,
            'security'         :   nonce,

        },
        success: function (data) {
            jQuery("#activate_pack").remove();
            jQuery("#invnotpaid").remove();

        },
        error: function (errorThrown) {}
    });//end ajax

    });















    var formfield, imgurl;
     $('#splash_video_mp4_button').on('click',function () {
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            var mediaUrl = jQuery(html).attr("href");
            jQuery('#splash_video_mp4').val(mediaUrl);
            tb_remove();
        };
        return false;
    });


    $('#splash_video_webm_button').on('click',function () {
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            var mediaUrl = jQuery(html).attr("href");
            jQuery('#splash_video_webm').val(mediaUrl);
            tb_remove();
        };
        return false;
    });


    $('#splash_video_ogv_button').on('click',function () {
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            var mediaUrl = jQuery(html).attr("href");
            jQuery('#splash_video_ogv').val(mediaUrl);
            tb_remove();
        };
        return false;
    });



     $('#page_custom_video_button').on('click',function () {
        formfield = $('#page_custom_video').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            var mediaUrl = jQuery(html).attr("href");

            jQuery('#page_custom_video').val(mediaUrl);
            tb_remove();
        };
        return false;
    });
       $('#page_custom_video_webbm_button').on('click',function () {

        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            var mediaUrl = jQuery(html).attr("href");
            jQuery('#page_custom_video_webbm').val(mediaUrl);
            tb_remove();
        };
        return false;
    });

    $('#page_custom_video_ogv_button').on('click',function () {

        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            var mediaUrl = jQuery(html).attr("href");
            jQuery('#page_custom_video_ogv').val(mediaUrl);
            tb_remove();
        };
        return false;
    });


    $('#page_custom_image_button').on('click',function () {
        formfield = $('#page_custom_image').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            $('#page_custom_image').val(imgurl);
            tb_remove();
        };
        return false;
    });

    $('.category_featured_image_button').on('click',function () {
        var parent = $(this).parent();
        formfield  = parent.find('#category_featured_image').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            parent.find('#category_featured_image').val(imgurl);
            var theid = $('img', html).attr('class');
            var thenum = theid.match(/\d+$/)[0];
            parent.find('#category_attach_id').val(thenum);
            tb_remove();
        };
        return false;
    });


     $('.category_featured_icon_button').on('click',function () {
        var parent = $(this).parent();
        formfield  = parent.find('#category_featured_icon').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            parent.find('#category_featured_icon').val(imgurl);
            var theid = $('img', html).attr('class');
            var thenum = theid.match(/\d+$/)[0];
            parent.find('#category_attach_id').val(thenum);
            tb_remove();
        };
        return false;
    });




    $('.category_icon_image_button').on('click',function () {
        var parent = $(this).parent();
        formfield  = parent.find('#category_icon_image').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            parent.find('#category_icon_image').val(imgurl);
            var theid = $('img', html).attr('class');
            var thenum = theid.match(/\d+$/)[0];
            parent.find('#category_attach_id').val(thenum);
            tb_remove();
        };
        return false;
    });


    $('#page_custom_image_button').on('click',function () {
        formfield = $('#page_custom_image').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            $('#page_custom_image').val(imgurl);
            tb_remove();
        };
        return false;
    });

    $('#page_custom_video_cover_image_button').on('click',function () {
        formfield = $('#page_custom_image').attr('name');
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        window.send_to_editor = function (html) {
            imgurl = $('img', html).attr('src');
            $('#page_custom_video_cover_image').val(imgurl);
            tb_remove();
        };
        return false;
    });

    if (jQuery('.user-verifications').length === 1) {
        var verifications = jQuery('.user-verifications');

        verifications.on('change', 'input[type="checkbox"]', function () {
            var   userID = jQuery(this).data('userid');

            var   isVerified = 0;
            var   editUser = jQuery(this).closest('.verify-user', jQuery('.user-verifications'));


            if( $('input[name="verified-users[]"]:checked').length > 0 ){
                isVerified = 1;
            }


           var nonce = jQuery('#wprentals_user_verfication').val();

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    'action': 'wpestate_update_verification',
                    'userid': userID,
                    'verified': isVerified,
                    'security': nonce,
                },
                success: function (data) {
                  console.log(data);
                    switch (true) {
                        case (isVerified === 0):

                            editUser.removeClass('verified');
                            break;
                        case (isVerified === 1):

                            editUser.addClass('verified');
                            break;
                    }
                },
                error: function (errorThrown) {

                }
            });
        });
    }


});

// Calculatate expense
function wpestate_recreate_invoice_manual_expenses_new(butonul){
    var inv_service_fee_fixed,extra_guests,is_remove,taxes_value,security_dep,early_bird_percent,invoice_manual_extra,invoice_default_extra,inter_price,early_bird,inv_depozit,edit_inv_balance,youearned,inv_service_fee,inv_taxes,book_down_fixed_fee,ex_name, ex_value, ex_value_show, new_row, total_amm, deposit, balance, book_down, cleaning_fee, city_fee, total_amm_compute,include_expenses;
    is_remove           =   0;
    total_amm           =   parseFloat(jQuery('#total_amm').attr('data-total'));

    if( butonul.is('.add_inv_discount') ){

        ex_name     =   jQuery('#edit_sub_name').val();
        ex_value    =   parseFloat(jQuery('#edit_discount').val(), 10)*(-1);
        total_amm = total_amm+ex_value;
    }

    if( butonul.is('.add_inv_expenses') ){
        ex_name             =   jQuery('#edit_add_name').val();
        ex_value            =   parseFloat(jQuery('#edit_add_amount').val());
        total_amm = total_amm+ex_value;
    }

    if( butonul.hasClass('delete_exp') ){
        is_remove   =   1;
        ex_name     =   'nothng';
        ex_value    =   parseFloat(butonul.attr('data-delvalue'))*-1;
        total_amm = total_amm+ex_value;
    }

    jQuery('#edit_total_amm').val(total_amm);

    if (admin_control_vars_new.where_currency_symbol === 'before') {
        ex_value_show = admin_control_vars_new.currency_symbol + ' ' + '<span class="inv_data_value" data-clearprice="'+ex_value+'">'+ex_value+'</span>';
    } else {
        ex_value_show = '<span class="inv_data_value"  data-clearprice="'+ex_value+'" >'+ex_value +'</span>' + ' ' + admin_control_vars_new.currency_symbol;
    }

    cleaning_fee        =   parseFloat(jQuery('#cleaning-fee').val());
    city_fee            =   parseFloat(jQuery('#city-fee').val());
    early_bird          =   parseFloat(jQuery('#erarly_bird_row').attr('data-val'));
    inv_depozit         =   parseFloat(jQuery('#inv_depozit').attr('data-val'));
    edit_inv_balance    =   parseFloat(jQuery('#edit_inv_balance').attr('data-val'));
    youearned           =   parseFloat(jQuery('#youearned').attr('data-youearned'));
    inv_service_fee     =   parseFloat(jQuery('#inv_service_fee').attr('data-value'));



    /*inv_taxes           =   parseFloat(jQuery('#inv_taxes').attr('data-value'));
    inter_price         =   parseFloat(jQuery('#inter_price').attr('data-value'));
    security_dep        =   parseFloat(jQuery('#security_depozit_row').attr('data-val'));
    early_bird_percent  =   parseFloat(jQuery('#property_details_invoice').attr('data-earlyb'));
    taxes_value         =   parseFloat(jQuery('#property_details_invoice').attr('data-taxes_value'));
    extra_guests        =   parseFloat(jQuery('#extra-guests').attr('data-extra-guests'));*/


    if (isNaN(cleaning_fee)) {
        cleaning_fee = 0;
    }
    if (isNaN(city_fee)) {
        city_fee = 0;
    }
    if (isNaN(inv_taxes)) {
        inv_taxes = 0;
    }

     if (isNaN(inv_service_fee_fixed)) {
        inv_service_fee_fixed = 0;
    }

    if (isNaN(youearned)) {
        youearned = 0;
    }
    if (isNaN(inter_price)) {
        inter_price = 0;
    }
    if (isNaN(security_dep)) {
        security_dep = 0;
    }


    //total_amm_compute       =   total_amm  ;
    if (ex_name !== '' &&  ex_value !== '' && ex_name !== 0 &&  ex_value !== 0 && !isNaN(ex_value)) {

        if(is_remove==1){
            butonul.parent().remove();
        }else{
            new_row = '<div class="invoice_row invoice_content manual_ex"><span class="inv_legend">' + ex_name + '</span><span class="inv_data invoice_manual_extra" data-value="'+ex_value+'">' + ex_value_show + '</span><span class="inv_exp"></span><span class="delete_exp" data-include_ex="'+include_expenses+'" data-delvalue="' + ex_value + '"><i class="fas fa-times"></i></span></div>';
            jQuery('.invoice_total').before(new_row);
            jQuery('#inv_expense_name').val('');
            jQuery('#inv_expense_value').val('');
            jQuery('#inv_expense_discount').val('');
        }


        /*if(early_bird   >   0){
            early_bird = (inter_price+invoice_default_extra +invoice_manual_extra+extra_guests)*early_bird_percent/100;
        }*/



        var service_fee         = jQuery('#service_fee').val();
        inv_service_fee_fixed   = parseFloat(admin_control_vars_new.service_fee_fixed_fee);


        





        if( parseFloat(inv_service_fee_fixed,10) > 0){
            inv_service_fee= parseFloat(inv_service_fee_fixed);
        }else{
        }
            inv_service_fee = (total_amm -security_dep -city_fee-cleaning_fee)*service_fee/100;




        youearned           =   total_amm-security_dep-city_fee-cleaning_fee;
        youearned           =   Math.round(youearned * 100) / 100;


        inv_taxes           =   youearned*taxes_value/100;
        inv_taxes           =   Math.round(inv_taxes * 100) / 100;



        /*book_down           =   parseFloat(admin_control_vars_new.book_down);
        book_down_fixed_fee =   parseFloat(admin_control_vars_new.book_down_fixed_fee);

        if(include_expenses==='yes'){
            deposit     =   wpestate_calculate_deposit_js(book_down,book_down_fixed_fee,total_amm);
        }else{
            deposit     =   wpestate_calculate_deposit_js(book_down,book_down_fixed_fee,(total_amm-city_fee-cleaning_fee) );
        }*/
        deposit     =   jQuery('#total-paid-amount').val();

        balance     =   total_amm - deposit;
        balance     =   Math.round(balance * 100) / 100;

        wpestate_delete_expense_js();
        jQuery('#total_amm').attr('data-total', total_amm);
        if (admin_control_vars_new.where_currency_symbol === 'before') {
            jQuery('#edit_inv_balance').empty().html(admin_control_vars_new.currency_symbol + ' ' + balance);
            jQuery('#total_amm').empty().append(admin_control_vars_new.currency_symbol + ' ' + total_amm);

            jQuery("#youearned").attr('data-value',youearned);
            jQuery("#youearned").empty().html(admin_control_vars_new.currency_symbol + ' ' + youearned);

            jQuery("#inv_depozit").attr('data-value',inv_service_fee);
            jQuery("#inv_depozit").empty().html(admin_control_vars_new.currency_symbol + ' ' + inv_service_fee);

            jQuery("#inv_taxes").attr('data-value',inv_taxes);
            jQuery("#inv_taxes").empty().html(admin_control_vars_new.currency_symbol + ' ' + inv_taxes);

        } else {
            jQuery('#edit_inv_balance').empty().html(balance + ' ' + admin_control_vars_new.currency_symbol);
            jQuery('#total_amm').empty().append(total_amm + ' ' + admin_control_vars_new.currency_symbol);

            jQuery("#youearned").attr('data-value',youearned);
            jQuery("#youearned").empty().html(youearned+ ' '+ admin_control_vars_new.currency_symbol );

            jQuery("#inv_depozit").attr('data-value',inv_service_fee);
            jQuery("#inv_depozit").empty().html(inv_service_fee+ ' '+ admin_control_vars_new.currency_symbol );

            jQuery("#inv_taxes").attr('data-value',inv_taxes);
            jQuery("#inv_taxes").empty().html( inv_taxes + ' '+ admin_control_vars_new.currency_symbol );

        }
    }
}

function wpestate_delete_expense_js() {
    "use strict";
    jQuery(".delete_exp").unbind("click");
    jQuery('.delete_exp').on('click',function (event) {
         var acesta=jQuery(this);
        wpestate_recreate_invoice_manual_expenses_new(acesta);
    });
};
/**
* Set booking calendar
*
*
*
*
*/
function check_in_out_enable2(in_date, out_date, min_days_booking, date_format) {
    // console.log('check_in_out_enable2---');
    var today, prev_date,read_in_date,date_format,calendar_opens, min_days_booking;
    
    today           =   new Date();
    date_format     =   date_format.toUpperCase();
    today           =   moment(today).format("MM/DD/YYYY");
    minim_days      =   parseFloat (min_days_booking,10);
    calendar_opens  =   'left';
    if(jQuery('#primary').hasClass('col-md-pull-8')){
        calendar_opens  =   'right';
    }

    jQuery("#" + in_date).attr('readonly','readonly');

    var options = {
            opens:calendar_opens,
            singleDatePicker: false,
            autoApply: true,
            alwaysShowCalendars: true,
            autoUpdateInput: false,
            minDate:today,
            locale:{
                daysOfWeek:dayNamesShort,
                monthNames:longmonths
            },

            isCustomDate:wpestate_booking_show_booked,

        };

    // set minimum days
    if(minim_days!==0){
        options.minSpan= {
            "days": minim_days
        };
    }



    var date_format     = control_vars.date_format.toUpperCase();
    date_format=date_format.replace("YY", "YYYY");

    var in_date_front   = jQuery('#' + in_date);
    var out_date_front  = jQuery('#' + out_date);



    jQuery("#" + out_date).removeAttr('disabled');


    var calendar= jQuery("#" + in_date).daterangepicker(
        options,
        function (start, end, label) {


            start_date  =                 start.format(date_format);
            end_date    =                 end.format(date_format);

            in_date_front.val(start_date);
            out_date_front.val(end_date);
            who_is=1;
            booking_started=1;
            jQuery('.wpestate_calendar').removeClass('minim_days_reservation').removeClass('wpestate_min_days_required');

            var prop_id=jQuery('#listing_edit').val();
            wpestate_setCookie('booking_prop_id_cookie',  prop_id , 1);
            wpestate_setCookie('booking_start_date_cookie',  jQuery('#start_date').val() , 1);
            wpestate_setCookie('booking_end_date_cookie',  jQuery('#end_date').val() , 1);

            // show_booking_costs();

        }
    );

    jQuery("#" + in_date).on('click',function(){
        jQuery('.daterangepicker').css('margin-top','0px');
    });

    jQuery("#" + out_date).on('click',function(){
        jQuery("#" + in_date).trigger('click');
        jQuery('.daterangepicker').css('margin-top','65px');
    });



    jQuery("html").on("mouseenter",".wpestate_booking_class", function() {
        var  unit_class = jQuery(this).attr('class');
        unit_class = unit_class.match(/\d+/);
        jQuery(this).find('.wpestate_show_price_calendar').show();

        if(who_is===1){
            wpestate_show_min_days_reservation(unit_class);
        }
    });


    jQuery("html").on("mouseleave",".wpestate_booking_class", function() {
        jQuery(this).find('.wpestate_show_price_calendar').hide();
        wpestate_remove_min_days_reservation(this);
    });

}

/**
* Set booking days
*
*
*
*
*/

    
    var end_reservation_class       =   '0';
function wpestate_booking_show_booked(date){
    var mega_details        =   '';
    var checkin_change_over =   '0';
    var checkin_checkout_change_over =   '0';
    var display_price       =   '';
    var from_who            =   "start_date";
    var reservation_class   =   '';
    var from_css_class      =   '';
    var received_date       =   new Date(date);
    var today               =   Math.floor(Date.now() / 1000);
    var unixtime            =   received_date.getTime()/1000;
    var unixtime1           =   unixtime - received_date.getTimezoneOffset()*60;
    var unixtime1_key       =   String(unixtime1);
    var week_day            =   received_date.getDay();
    var block_check_in_check_out    =   0;
    var block_check_in      =   0;
    if(week_day===0){
        week_day=7;
    }

    // console.log('booking_array = '+booking_array);
        // establish the start day block
    //////////////////////////////////////////////////////////////////////////
    if(mega_details[unixtime1_key] !== undefined){
        if( parseFloat(mega_details[unixtime1_key]['period_checkin_change_over'],10)!==0 ) {
            block_check_in =  parseFloat(mega_details[unixtime1_key]['period_checkin_change_over'],10);
        }
    }else if( parseFloat(checkin_change_over)!==0 ){
        block_check_in =  parseFloat(checkin_change_over,10);
    }

    // establish the start day - end day block
    ////////////////////////////////////////////////////////////////////////////
    if(mega_details[unixtime1_key] !== undefined){
        if( parseFloat(mega_details[unixtime1_key]['period_checkin_checkout_change_over'],10)!==0 ) {
            block_check_in_check_out =  parseFloat(mega_details[unixtime1_key]['period_checkin_checkout_change_over'],10);
        }
    }else if( parseFloat(checkin_checkout_change_over)!==-1 ){
        block_check_in_check_out =  parseFloat(checkin_checkout_change_over,10);
    }




    var block_class=' disabled off';
    if( booking_array[unixtime1] != undefined){
        end_reservation_class=1;

        if(start_reservation_class==1){
            reservation_class=' start_reservation';
            start_reservation_class=0;
            
            if ( from_backend == 1 ) {
                return "wpestate_calendar calendar-reserved"+reservation_class;
                // jQuery('.start_reservation').removeClass('disabled');
            } else {
                return "wpestate_calendar calendar-reserved"+reservation_class+" "+block_class;
            }
        }




        return "wpestate_calendar calendar-reserved"+reservation_class+" "+block_class;;

    }else{
        start_reservation_class=1;
        if(end_reservation_class===1){
            reservation_class=' end_reservation';
            end_reservation_class=0;

        }

        if(week_day !== block_check_in_check_out && block_check_in_check_out!==0 && unixtime1_key > (today-24*60*60) ){

            if(reservation_class !== ' end_reservation'){
                reservation_class=reservation_class+' check_in_block 1';
            }
            return  "wpestate_calendar "+reservation_class+" date"+unixtime1_key;


        //Check in/Check out  only on '+weekdays[block_check_in]
        }else if(week_day !== block_check_in && block_check_in!==0 && from_who ==='start_date' && unixtime1_key > (today-24*60*60) ){
            return "wpestate_calendar "+reservation_class+" date"+unixtime1_key;

        }

        return "freetobook wpestate_calendar"+reservation_class+" date"+unixtime1_key+" "+from_css_class;
    }

}

function wpestate_setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}