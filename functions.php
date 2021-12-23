<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

    
if ( !function_exists( 'wpestate_chld_thm_cfg_parent_css' ) ):
   function wpestate_chld_thm_cfg_parent_css() {

    $parent_style = 'wpestate_style'; 
    wp_enqueue_style('bootstrap',get_template_directory_uri().'/css/bootstrap.css', array(), '1.0', 'all');
    wp_enqueue_style('bootstrap-theme',get_template_directory_uri().'/css/bootstrap-theme.css', array(), '1.0', 'all');
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css',array('bootstrap','bootstrap-theme'),'all' );
    wp_enqueue_style( 'wpestate-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
   }    
    
endif;
add_action( 'wp_enqueue_scripts', 'wpestate_chld_thm_cfg_parent_css' );
load_child_theme_textdomain('wprentals', get_stylesheet_directory().'/languages');
// END ENQUEUE PARENT ACTION

include get_stylesheet_directory_uri().'/libs/ajax_functions.php';
include get_stylesheet_directory_uri().'/libs/help_functions.php';
include get_stylesheet_directory_uri().'/libs/ajax_functions_booking.php';
add_action('admin_enqueue_scripts', 'wpestate_scripts');
add_action( 'admin_enqueue_scripts', 'admin_enqueue_scripts_function' );
function admin_enqueue_scripts_function() {
    // wp_enqueue_scripts('jquery-js',get_stylesheet_directory_uri().'/chosen/jquery-3.2.1.min.js', array(), '1.0', 'all');
    $wp_estate_service_fee            =   floatval( wprentals_get_option('wp_estate_service_fee','') );
    $wp_estate_service_fee_fixed_fee  =   floatval( wprentals_get_option('wp_estate_service_fee_fixed_fee','') );
    wp_enqueue_style('style-css',get_stylesheet_directory_uri().'/chosen/style.css', array(), '1.0', 'all');
    wp_enqueue_style('admin-style-css',get_stylesheet_directory_uri().'/css/admin.css', array(), '1.0', 'all');
    wp_enqueue_style('prism-css',get_stylesheet_directory_uri().'/chosen/prism.css', array(), '1.0', 'all');
    wp_enqueue_style('chosen-css',get_stylesheet_directory_uri().'/chosen/chosen.css', array(), '1.0', 'all');
    wp_enqueue_script('chosen-jquery-js',get_stylesheet_directory_uri().'/chosen/chosen.jquery.js', array(), '1.0', 'all');
    wp_enqueue_script('prism-js',get_stylesheet_directory_uri().'/chosen/prism.js', array(), '1.0', 'all');
    wp_enqueue_script('init-js',get_stylesheet_directory_uri().'/chosen/init.js', array(), '1.0', 'all');

    wp_enqueue_script( 'jquery-ui-datepicker' );

    // You need styling for the datepicker. For simplicity I've linked to the jQuery UI CSS on a CDN.
    wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css' );
    wp_enqueue_style( 'jquery-ui' );  
    
    wp_register_script('wpestate_admin-control', trailingslashit( get_stylesheet_directory_uri() ).'/js/admin-control.js',array('jquery'), '1.2', true);
    wp_localize_script('wpestate_admin-control', 'admin_control_vars_new',
        array( 'ajaxurl'             => esc_url(admin_url('admin-ajax.php') ),
                'currency_symbol'    =>  wpestate_curency_submission_pick(),
                'where_currency_symbol'     =>  esc_html( wprentals_get_option('wp_estate_where_currency_symbol', '') ),
                'service_fee'               =>  $wp_estate_service_fee,
                'service_fee_fixed_fee'     =>  $wp_estate_service_fee_fixed_fee,
                'processing'         =>  esc_html__( 'processing..','wprentals'),
            )
    );
}

if ( $_GET['test'] == 1 ) {
include get_stylesheet_directory_uri().'/processor.php';

    $processor_link     =   get_stylesheet_directory_uri().'/processor.php';

    echo "<br> processor_link = ".$processor_link;

    die('inn');
}

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

function save_booking_details( $post_id, $post ){
     
    // Only set for post_type = wpestate_booking!
    if ( 'wpestate_booking' == $post->post_type && $_POST['action'] == 'editpost' ) {


        $allowded_html      =   array();
        $userID             =   $_POST['user_id'];

        if ( isset($_POST['firstname']) ) {
            update_user_meta( $userID, 'first_name', $_POST['firstname'] );
        }

        if ( isset($_POST['lastname']) ) {
            update_user_meta( $userID, 'last_name', $_POST['lastname'] );
        }

        if ( isset($_POST['phoneno']) ) {
            update_user_meta( $userID, 'phone', $_POST['phoneno'] );
        }

        if ( !is_user_logged_in() ) {
            exit('ko');
        }
        if($userID === 0 ){
            exit('out pls');
        }


        $from               =   $_POST['user_email'];
        $comment            =   '';
        $status             =   'pending';

        if( isset($_POST['comment']) ){
            $comment            =    wp_kses ( $_POST['comment'],$allowded_html ) ;
        }

        $booking_guest_no    =   0;
        if(isset($_POST['booking_guests'])){
            $booking_guest_no    =   intval($_POST['booking_guests']);
        }

        if ( isset ($_POST['confirmed']) ) {
            if (intval($_POST['confirmed'])==1 ){
                $status    =   'confirmed';
            }
        }

        $property_id        =   intval( $_POST['listing_edit'] );
        $instant_booking    =   floatval   ( get_post_meta($property_id, 'instant_booking', true) );
        $user_notification  =   intval( $_POST['user_notification'] );
        $owner_id           =   wpsestate_get_author($property_id);
        $fromdate           =   wp_kses ( $_POST['booking_from_date'], $allowded_html );
        $to_date            =   wp_kses ( $_POST['booking_to_date'], $allowded_html );

        $fromdate   = wpestate_convert_dateformat_twodig($fromdate);
        $to_date    = wpestate_convert_dateformat_twodig($to_date);

        // echo "<br> fromdate = ".$fromdate;
        // $fromdate = get_post_meta($post_id, 'booking_from_date', true );
        // echo "<br> previous fromdate = ".$fromdate;
        // die('innn');

        $event_name         =   esc_html__( 'Booking Request From Backend','wprentals');
        $extra_options      =   '';
        if(isset($_POST['extra_options'])){
            $extra_options      =   wp_kses ( $_POST['extra_options'], $allowded_html );
        }

        global $wpdb;
        $post_data = array( 'post_title' => $event_name.' '.$post_id, 'post_author' => $userID  );
        $wpdb->update( $wpdb->prefix.'posts', $post_data , array( 'ID' =>  $post_id ) );
        
        update_post_meta($post_id, 'booking_status', $status);
        update_post_meta($post_id, 'booking_id', $property_id);
        update_post_meta($post_id, 'owner_id', $owner_id);
        update_post_meta($post_id, 'booking_from_date', $fromdate);
        update_post_meta($post_id, 'booking_to_date', $to_date);
        update_post_meta($post_id, 'booking_from_date_unix', strtotime($fromdate));
        update_post_meta($post_id, 'booking_to_date_unix', strtotime($to_date));
        update_post_meta($post_id, 'booking_from_date_unix', strtotime($fromdate));
        update_post_meta($post_id, 'booking_to_date_unix', strtotime($to_date));
        update_post_meta($post_id, 'booking_invoice_no', 0);
        update_post_meta($post_id, 'booking_pay_ammount', 0);
        update_post_meta($post_id, 'booking_guests', $booking_guest_no);
        update_post_meta($post_id, 'extra_options', $extra_options);

        $security_deposit= get_post_meta(  $property_id,'security_deposit',true);
        update_post_meta($post_id, 'security_deposit', $security_deposit);

        $full_pay_invoice_id =0;
        update_post_meta($post_id, 'full_pay_invoice_id', $full_pay_invoice_id);

        $to_be_paid =0;
        update_post_meta($post_id, 'to_be_paid', $to_be_paid);

        // die('innn');

        // build the reservation array
        $reservation_array = custom_wpestate_get_booking_dates($property_id, $post_id);
        update_post_meta($property_id, 'booking_dates', $reservation_array);

        if ( $owner_id == $userID ) {
            $subject    =   esc_html__( 'You reserved a period','wprentals');
            $description=   esc_html__( 'You have reserverd a period on your own listing','wprentals');

            // $from               =   $current_user->user_login;
            $to                 =   $owner_id;

            $receiver          =   get_userdata($owner_id);
            $receiver_email    =   $receiver->user_email;


            wpestate_add_to_inbox($userID,$userID,$userID, $subject,$description,"internal_book_req");
            wpestate_send_booking_email('mynewbook',$receiver_email,$property_id);


        }else{
            $receiver          =   get_userdata($owner_id);
            $receiver_email    =   $receiver->user_email;
            // $from               =   $current_user->ID;
            $to                 =   $owner_id;


            $subject    =   esc_html__( 'New Booking Request from ','wprentals');
            $description=   sprintf( esc_html__( 'Dear %s, You have received a new booking request from %s. Message sent to %s and %s','wprentals'),$receiver->user_login,$from,$receiver->user_login,$from);


            if($user_notification==1){
                //normal]
                wpestate_add_to_inbox($userID,$userID,$to, $subject,$description,"external_book_req");
                wpestate_send_booking_email('newbook',$receiver_email,$property_id);
            }

        }



        $extra_options_array=array();
        if($extra_options!=''){
            $extra_options_array    =   explode(',',$extra_options);
        }
        $invoice_id='';
        $booking_array      =   wpestate_booking_price($booking_guest_no,$invoice_id, $property_id, $fromdate, $to_date,$post_id,$extra_options_array);
        update_post_meta($post_id, 'custom_price_array',$booking_array['custom_price_array']);


        $property_author = wpsestate_get_author($property_id);

        if( $userID != $property_author){

            $add_booking_details =array(

                "booking_status"            =>  $status,
                "original_property_id"      =>  $property_id,

                "book_author"               =>  $userID,
                "owner_id"                  =>  $owner_id,
                "booking_from_date"         =>  $fromdate,
                "booking_to_date"           =>  $to_date,
                "booking_invoice_no"        =>  0,
                "booking_pay_ammount"       =>  $booking_array['deposit'],
                "booking_guests"            =>  $booking_guest_no,
                "extra_options"             =>  $extra_options,
                "security_deposit"          =>  $booking_array['security_deposit'],
                "full_pay_invoice_id"       =>  0,
                "to_be_paid"                =>  $booking_array['deposit'],
                "youearned"                 =>  $booking_array['youearned'],
                "service_fee"               =>  $booking_array['service_fee'],
                "booking_taxes"             =>  $booking_array['taxes'],
                "total_price"               =>  $booking_array['total_price'],
                "custom_price_array"        =>  $booking_array['custom_price_array'],
                "submission_curency_status" =>  esc_html( wprentals_get_option('wp_estate_submission_curency','') ),

            );

        }

        wpestate_add_booking_invoice_custom( $booking_array['total_price'], $post_id, 1, $invoice_id = 0);
        
    }

    // Only set for post_type = wpestate_invoice!
    if ( 'wpestate_invoice' == $post->post_type && !empty($_POST) && $_POST['originalaction'] == 'editpost' && $_POST['add_new_booking'] != 'yes' ) {


        $allowded_html      =   array();
        $invoice_id = $post_id;
        $bookid =   esc_html(get_post_meta($invoice_id, 'item_id', true));
        $booking_guest_no = $_POST['edit_guests'];
        $edit_price_per_night = $_POST['edit_price_per_night'];
        $edit_deposit = $_POST['edit_deposit'];
        $edit_balance = $_POST['edit_balance'];
        $edit_service_fee = $_POST['edit_service_fee'];
        $edit_total_price = $_POST['edit_total_price'];
        $edit_paid_amount = $_POST['edit_paid_amount'];
        $invoice_status   = $_POST['invoice_status'];
        $edit_add_name    = $_POST['edit_add_name'];
        $edit_sub_name    = $_POST['edit_sub_name'];
        $edit_add_amount  = $_POST['edit_add_amount'];
        $edit_discount    = $_POST['edit_discount'];
        $property_id      =   esc_html(get_post_meta($bookid, 'booking_id', true));

        /*if ( isset($edit_total_price) && $edit_total_price != '' ) {
            // update_post_meta($invoice_id, 'item_price', $edit_total_price);
        } else {
            // update_post_meta ( $invoice_id, 'new_item_price', $_POST['edit_total_amm'] );
        }

        echo "<br> POST new_item_price = ".$_POST['edit_total_amm'];
        echo "<br> POST edit_total_price = ".$edit_total_price;

        $item_price             =   get_post_meta ( $invoice_id, 'item_price', true);  
        $new_item_price         =   get_post_meta ( $invoice_id, 'new_item_price', true);  

        echo "<br> new_item_price = ".$new_item_price;

        if ( $new_item_price != '' ) {

            $total_new_amt = $new_item_price;
        } else {
            $total_new_amt = $item_price;
        }

        echo "<br> total_new_amt = ".$total_new_amt;
            

        $paid_amount = get_post_meta($invoice_id, 'paid_amount', true);

        for( $i = 0; $i < count($paid_amount); $i++ ) {
            $totol_paid_amount += $paid_amount[$i];
        }

        echo "<br> totol_paid_amount = ".$totol_paid_amount;

        if ( $total_new_amt != '' ) {  
            $balance            =   $total_new_amt - $totol_paid_amount;  
            // update_post_meta($invoice_id, 'balance', $balance);
        } else if ( !empty($paid_amount) ) {
            $balance                =   $item_price - $totol_paid_amount;  
            // update_post_meta($invoice_id, 'balance', $balance);
        }

        echo "<br> balance = ".$balance;
        die('innn');*/

        if ( $_POST['send_invoice'] == 'yes' ) {
            $current_user =     wp_get_current_user();
            $userID       =     $current_user->ID;
            $booking_prop       =   esc_html(get_post_meta($bookid, 'booking_id', true)); // property_id
            if( get_post_type($bookid)=='wpestate_booking'){
                 $user_id         =   get_post_field( 'post_author', $bookid );
            }else{
                $user_id           =   wpsestate_get_author($booking_prop);
            }

            $receiver          =   get_userdata($user_id);
            $receiver_email    =   $receiver->user_email;
            $receiver_login    =   $receiver->user_login;
            $from               =   $current_user->user_login;
            $to                 =   $user_id;
            $subject            =   esc_html__( 'New Invoice','wprentals');
            $description        =   esc_html__( 'A new invoice was generated for your booking request','wprentals');

            // wpestate_add_to_inbox($userID,$userID,$to,$subject,$description,1);
            // wpestate_send_booking_email('newinvoice',$receiver_email);
        }

        $extra_options      =   '';
        $fromdate           =   wp_kses ( $_POST['booking_from_date'], $allowded_html );
        $to_date            =   wp_kses ( $_POST['booking_to_date'], $allowded_html );

        // echo "<br> post fromdate = ".$fromdate;
        
        $get_from_date = get_post_meta($bookid, 'booking_from_date', true);

        if ( $fromdate != $get_from_date ) {
            $fromdate   = wpestate_convert_dateformat_twodig($fromdate);
            $to_date    = wpestate_convert_dateformat_twodig($to_date);
        } 
        // test start

        // test end

        if(isset($_POST['extra_options'])){
            $extra_options      =   wp_kses ( $_POST['extra_options'], $allowded_html );
        }

        $extra_options_array=array();
        if($extra_options!=''){
            $extra_options_array    =   explode(',',$extra_options);
        }
        $booking_array     =   wpestate_booking_price($booking_guest_no,$invoice_id, $property_id, $fromdate, $to_date,$bookid,$extra_options_array);
        update_post_meta($post_id, 'custom_price_array',$booking_array['custom_price_array']);

        $total_price = get_post_meta( $invoice_id, 'item_price', true );
        $booking_status = get_post_meta ( $bookid, 'booking_status', true );

        if ( $edit_paid_amount != 0 || $edit_add_amount != '' || $edit_discount != '' || $booking_status == 'confirmed'  ) {
            $_POST['is_confirmed'] = 1;
            
            update_post_meta($bookid, 'booking_status', 'confirmed');
            
        } 

        update_post_meta($bookid, 'booking_from_date', $fromdate);
        update_post_meta($bookid, 'booking_to_date', $to_date);
        update_post_meta($bookid, 'booking_from_date_unix', strtotime($fromdate));
        update_post_meta($bookid, 'booking_to_date_unix', strtotime($to_date));
        update_post_meta($bookid, 'booking_from_date_unix', strtotime($fromdate));
        update_post_meta($bookid, 'booking_to_date_unix', strtotime($to_date));
        
        $get_sub_amt    = get_post_meta($invoice_id, 'sub_amount', true);

        if ( $edit_discount != '' ) {
            $sub_amount_arr = array();
            $sub_amount_date_arr = array();
            $sub_name_arr = array();

            $get_sub_amount_date = get_post_meta($invoice_id, 'sub_amount_date', true);
            $get_sub_text   = get_post_meta($invoice_id, 'sub_text', true);

            if ( !empty($get_sub_amt) ) {
                array_push($get_sub_amt, $edit_discount);
                array_push($get_sub_amount_date, date('d-m-y'));

                if ( $get_sub_text == '' ) {
                    $get_sub_text = array();
                }
                array_push($get_sub_text, $edit_sub_name );
                update_post_meta($invoice_id, 'sub_amount', $get_sub_amt);
                update_post_meta($invoice_id, 'sub_amount_date', $get_sub_amount_date);
                update_post_meta($invoice_id, 'sub_text', $get_sub_text);
            } else {
                array_push($sub_amount_arr, $edit_discount);
                array_push($sub_amount_date_arr, date('d-m-y'));
                array_push($sub_name_arr, $edit_sub_name);
                update_post_meta($invoice_id, 'sub_amount', $sub_amount_arr);
                update_post_meta($invoice_id, 'sub_text', $sub_amount_date_arr);
                update_post_meta($invoice_id, 'sub_text', $sub_name_arr);
            }
        }
        
        $get_add_amount = get_post_meta($invoice_id, 'add_amount', true);

        if ( $edit_add_name != '' ) {
            $add_amount_arr = array();
            $add_name_arr   = array();
            $add_amount_date_arr = array();

            $get_add_text   = get_post_meta($invoice_id, 'add_text', true);
            $get_add_amount_date = get_post_meta($invoice_id, 'add_amount_date', true);

            if ( !empty($get_add_amount) ) {
                array_push($get_add_amount, $edit_add_amount);
                array_push($get_add_text, $edit_add_name);
                array_push($get_add_amount_date, date('d-m-y'));

                update_post_meta($invoice_id, 'add_amount_date', $get_add_amount_date);
                update_post_meta($invoice_id, 'add_amount', $get_add_amount);
                update_post_meta($invoice_id, 'add_text', $get_add_text);
            } else {
                array_push($add_amount_arr, $edit_add_amount);
                array_push($add_name_arr, $edit_add_name);
                array_push($add_amount_date_arr, date('d-m-y'));
                update_post_meta($invoice_id, 'add_amount', $add_amount_arr);
                update_post_meta($invoice_id, 'add_text', $add_name_arr);
                update_post_meta($invoice_id, 'add_amount_date', $add_amount_date_arr);
            }
        }
        
        // $paid_amount = get_post_meta($invoice_id, 'paid_amount' true);
        if ( $edit_paid_amount != '' ) {
            $paid_amount_arr = array();
            $paid_amount_date = array();
            $paid_amount_status = array();
            $total_new_price_arr = array();
            //$paid_amount_status = array();

            $get_paid_amount = get_post_meta($invoice_id, 'paid_amount', true);
            $get_paid_status = get_post_meta($invoice_id, 'paid_status', true);
            $get_paid_amount_date = get_post_meta($invoice_id, 'paid_amount_date', true);
            $total_new_price = get_post_meta($invoice_id, 'total_new_price', true);


            if ( !empty($get_paid_amount) ) {

                array_push($get_paid_amount, $edit_paid_amount);
                array_push($get_paid_amount_date, date('d-m-y'));
                array_push($get_paid_status, $invoice_status);
                array_push($total_new_price, $edit_paid_amount);
                update_post_meta($invoice_id, 'paid_amount', $get_paid_amount);
                update_post_meta($invoice_id, 'paid_status', $get_paid_status);
                update_post_meta($invoice_id, 'paid_amount_date', $get_paid_amount_date);
                update_post_meta($invoice_id, 'total_new_price', array_sum($total_new_price));
            } else {
                array_push($paid_amount_arr, $edit_paid_amount);
                array_push($paid_amount_date, date('d-m-y'));
                array_push($paid_amount_status, $invoice_status);
                update_post_meta($invoice_id, 'paid_amount', $paid_amount_arr);
                update_post_meta($invoice_id, 'paid_status', $paid_amount_status);
                update_post_meta($invoice_id, 'paid_amount_date', $paid_amount_date);
                array_push($total_new_price_arr, $edit_paid_amount);
                update_post_meta($invoice_id, 'total_new_price', $total_new_price_arr);
            }
                
            update_post_meta($invoice_id, 'depozit_to_be_paid', 0);
        }
        

        if ( isset($edit_total_price) && $edit_total_price != '' ) {
            update_post_meta($invoice_id, 'item_price', $edit_total_price);
        } else {
            update_post_meta ( $invoice_id, 'new_item_price', $_POST['edit_total_amm'] );
        }

        if ( isset($booking_guest_no) && $booking_guest_no != '' ) {
            update_post_meta($bookid, 'booking_guests', $booking_guest_no);
        } else {
            $booking_guest_no = get_post_meta($bookid, 'booking_guests', true );
        }

        if ( isset($edit_price_per_night) && $edit_price_per_night != '' ) {
            update_post_meta($invoice_id, 'edit_price_per_night', $edit_price_per_night);
        }

        if ( isset($_POST['amt_to_be_sent']) && $_POST['amt_to_be_sent'] != '' && $edit_paid_amount == '' ) {
            update_post_meta($invoice_id, 'depozit_to_be_paid', $_POST['amt_to_be_sent']);
        }

        if ( isset($edit_service_fee) && $edit_service_fee != '' ) {
            update_post_meta($invoice_id, 'edit_service_fee', $edit_service_fee);
        }

        $item_price             =   get_post_meta ( $invoice_id, 'item_price', true);  
        $new_item_price         =   get_post_meta ( $invoice_id, 'new_item_price', true);  

        // echo "<br> new_item_price = ".$new_item_price;
        if ( $new_item_price != '' ) {

            $total_new_amt = $new_item_price;
        } else {
            $total_new_amt = $item_price;
        }

        $paid_amount = get_post_meta($invoice_id, 'paid_amount', true);

        for( $i = 0; $i < count($paid_amount); $i++ ) {
            $totol_paid_amount += $paid_amount[$i];
        }

        if ( $total_new_amt != '' ) {  
            $balance            =   $total_new_amt - $totol_paid_amount;  
            update_post_meta($invoice_id, 'balance', $balance);
        } else if ( !empty($paid_amount) ) {
            $balance                =   $item_price - $totol_paid_amount;  
            update_post_meta($invoice_id, 'balance', $balance);
        } 

        wpestate_add_booking_invoice_custom( $booking_array['total_price'], $bookid, 0, $invoice_id);

    }
}
add_action( 'save_post', 'save_booking_details', 98, 2 );

function wpestate_add_booking_invoice_custom($price, $bookid, $new_booking, $invoice_id ){

    

    $price =(double) round ( floatval($price),2 )  ;
    $current_user =     wp_get_current_user();
    $userID       =     $current_user->ID;
    if ( !is_user_logged_in() ) {
        exit('ko');
    }
    if($userID === 0 ){
        exit('out pls');
    }

    $is_confirmed = 0;


    $is_confirmed            =   intval($_POST['is_confirmed']);
    $bookid                  =   intval($bookid);
    $wpestate_book_from      =   get_post_meta($bookid, 'booking_from_date', true);
    $wpestate_book_to        =   get_post_meta($bookid, 'booking_to_date', true);
    $listing_id              =   get_post_meta($bookid, 'booking_id', true);

    $the_post= get_post( $listing_id);

    if( $current_user->ID != $the_post->post_author ) {
        // exit('you don\'t have the right to see this');
    }

    // prepare
    $full_pay_invoice_id        =   0;
    $early_bird_percent         =   floatval(get_post_meta($listing_id, 'early_bird_percent', true));
    $early_bird_days            =   floatval(get_post_meta($listing_id, 'early_bird_days', true));
    $taxes_value                =   floatval(get_post_meta($listing_id, 'property_taxes', true));

    $old_date = date("m-d-Y", strtotime($wpestate_book_from));  
    $new_date = $_POST['booking_from_date'];

    if ( $new_date != $old_date && $new_booking == 0 ) {

        $reservation_array  = get_post_meta($listing_id, 'booking_dates',true);
        $new_booking_array = array();
        $new_booking_array1 = array();

        foreach( $reservation_array as $key => $value ) {
            if ( $value != $bookid ) {
                $new_booking_array[$key] = $value;
            } 
        }

        array_push($new_booking_array1, $new_booking_array);
        $reservation_array = $new_booking_array1[0];
        
    } else {
        $reservation_array  = get_post_meta($listing_id, 'booking_dates',true);
    }

    //check if period already reserverd
    if($reservation_array==''){
        $reservation_array = custom_wpestate_get_booking_dates($listing_id, $bookid);
    }


    /*echo "<pre>";
    print_r($reservation_array);
    echo "</pre>";
    die('innn');*/

    if ( $new_booking == 1 ) {
        wpestate_check_for_booked_time($wpestate_book_from,$wpestate_book_to,$reservation_array,$listing_id);
    }
    // end check


    // we proceed with issuing the invoice
    $allowed_html   =   array();
    $details        =   '';
    // $details        =   $_POST['details'];
    $manual_expenses=   '';
    // $manual_expenses=   $_POST['manual_expenses'];
    $billing_for    =   esc_html__( 'Reservation fee','wprentals');
    $type           =   esc_html__( 'One Time','wprentals');
    $pack_id        =   $bookid; // booking id

    $time           =   time();
    $date           =   date('Y-m-d H:i:s',$time);
    $user_id        =   wpse119881_get_author($bookid);
    $is_featured    =   '';
    $is_upgrade     =   '';
    $paypal_tax_id  =   '';



    // get the booking array
    // $invoice_id          =   0;
    $booking_guests      =   get_post_meta($bookid, 'booking_guests', true);
    $extra_options       =   esc_html(get_post_meta($bookid, 'extra_options', true));
    $extra_options_array =   explode(',', $extra_options);
    // done
    if ( $invoice_id == 0 ) {
        $invoice_id                 =  wpestate_booking_insert_invoice($billing_for,$type,$pack_id,$date,$user_id,$is_featured,$is_upgrade,$paypal_tax_id,$details,$price);
    }

    $submission_curency_status  = wpestate_curency_submission_pick();
    $booking_array       =   wpestate_booking_price($booking_guests,$invoice_id, $listing_id, $wpestate_book_from, $wpestate_book_to,$bookid,$extra_options_array,$manual_expenses);

    if ( $new_date > $old_date ) {
        update_post_meta($invoice_id, 'c_invoice_status', 'extend');
    } else if ( $new_date < $old_date ) {
        update_post_meta($invoice_id, 'c_invoice_status', 'shorten');
    }


    if ( $new_booking == 1 ) {
        $balance = $booking_array['deposit'];
        $depozit_to_be_paid = $booking_array['deposit'];
    } else {
        $balance = get_post_meta($invoice_id, 'balance', true);
        if ( $_POST['edit_paid_amount'] == '' ) {

            $depozit_to_be_paid = $_POST['amt_to_be_sent'];
        } else {
            
            $depozit_to_be_paid = 0;
        }
    }

    // update booking data
    update_post_meta($bookid, 'full_pay_invoice_id', $full_pay_invoice_id);
    update_post_meta($bookid, 'booking_taxes', $taxes_value);
    update_post_meta($bookid, 'early_bird_percent', $early_bird_percent);
    update_post_meta($bookid, 'early_bird_days', $early_bird_days);
    update_post_meta($bookid, 'security_deposit', $booking_array['security_deposit']);
    update_post_meta($bookid, 'booking_taxes', $booking_array['taxes']);
    update_post_meta($bookid, 'service_fee', $booking_array['service_fee']);
    update_post_meta($bookid, 'youearned', $booking_array['youearned']);
    update_post_meta($bookid, 'to_be_paid',$booking_array['deposit'] );
    update_post_meta($bookid, 'booking_status', 'waiting');
    update_post_meta($bookid, 'booking_invoice_no', $invoice_id);
    update_post_meta($bookid, 'total_price', $booking_array['total_price']);
    update_post_meta($bookid, 'balance'  , $balance);

    //update invoice data
    update_post_meta($invoice_id, 'booking_taxes', $taxes_value);
    update_post_meta($invoice_id, 'security_deposit', $booking_array['security_deposit']);
    update_post_meta($invoice_id, 'early_bird_percent', $early_bird_percent);
    update_post_meta($invoice_id, 'early_bird_days', $early_bird_days);
    update_post_meta($invoice_id, 'booking_taxes', $booking_array['taxes']);
    update_post_meta($invoice_id, 'service_fee', $booking_array['service_fee']);
    update_post_meta($invoice_id, 'youearned', $booking_array['youearned'] );
    // update_post_meta($invoice_id, 'depozit_to_be_paid', $booking_array['deposit'] );
    update_post_meta($invoice_id, 'depozit_to_be_paid', $depozit_to_be_paid );
    update_post_meta($invoice_id, 'balance'  , $balance);
    update_post_meta($invoice_id,  'manual_expense',$manual_expenses);

    $cleaning_fee_per_day       =   floatval(get_post_meta($listing_id, 'cleaning_fee_per_day', true));
    $city_fee_per_day           =   floatval(get_post_meta($listing_id, 'city_fee_per_day', true));
    $city_fee_percent           =   floatval(get_post_meta($listing_id, 'city_fee_percent', true));

    update_post_meta($invoice_id, 'cleaning_fee_per_day',$cleaning_fee_per_day);
    update_post_meta($invoice_id, 'city_fee_per_day',$city_fee_per_day);
    update_post_meta($invoice_id, 'city_fee_percent',$city_fee_percent);




    $booking_details=array(
        'total_price'           =>  $booking_array['total_price'],
        'to_be_paid'            =>  $booking_array['deposit'],
        'youearned'             =>  $booking_array['youearned'],
        'full_pay_invoice_id'   =>  $full_pay_invoice_id,
        'service_fee'           =>  $booking_array['service_fee'],
        'booking_taxes'         =>  $booking_array['taxes'],
        'security_deposit'      =>  $booking_array['security_deposit'],
        'booking_status'        =>  'waiting',
        'booking_invoice_no'    =>  $booking_invoice_no,
        'balance'               =>  $balance
    );
    if($is_confirmed==1){
        update_post_meta($bookid, 'booking_status', 'confirmed');
        $booking_detail['booking_status']='confirmed';
    }



    update_post_meta($invoice_id, 'custom_price_array',$booking_array['custom_price_array']);




    $invoice_details=array(
        "invoice_status"                =>  "issued",
        "purchase_date"                 =>  $date,
        "buyer_id"                      =>  $user_id,
        "item_price"                    =>  $booking_array['total_price'],
        "orignal_invoice_id"            =>  $invoice_id,
        "billing_for"                   =>  $billing_for,
        "type"                          =>  $type,
        "pack_id"                       =>  $pack_id,
        "date"                          =>  $date,
        "user_id"                       =>  $user_id,
        "is_featured"                   =>  $is_featured,
        "is_upgrade"                    =>  $is_upgrade,
        "paypal_tax_id"                 =>  $paypal_tax_id,
        "details"                       =>  $details,
        "price"                         =>  $price,
        "to_be_paid"                    =>  $booking_array['deposit'],
        "submission_curency_status"     =>  $submission_curency_status,
        "bookid"                        =>  $bookid,
        "author_id"                     =>  $author_id,
        "youearned"                     =>  $booking_array['youearned'],
        "service_fee"                   =>  $booking_array['service_fee'],
        "booking_taxes"                 =>  $booking_array['taxes'],
        "security_deposit"              =>  $booking_array['security_deposit'],
        "renting_details"               =>  $details,
        "custom_price_array"            =>  $booking_array['custom_price_array'],
        "balance"                       =>  $balance,
        "cleaning_fee_per_day"          =>  $cleaning_fee_per_day,
        "city_fee_per_day"              =>  $city_fee_per_day,
        "city_fee_percent"              =>  $city_fee_percent,
    );


    if( $balance > 0 ){
        update_post_meta($bookid, 'booking_status_full','waiting' );
        update_post_meta($invoice_id, 'invoice_status_full','waiting');
        $booking_details['booking_status_full'] =   'waiting';
        $booking_details['booking_invoice_no']  =   $invoice_id;
        $invoice_details['invoice_status_full'] =   'waiting';
    } else {
        update_post_meta($bookid, 'booking_status_full','confirmed' );
        update_post_meta($invoice_id, 'invoice_status_full','confirmed');
        $booking_details['booking_status_full'] =   'confirmed';
        $booking_details['booking_invoice_no']  =   $invoice_id;
        $invoice_details['invoice_status_full'] =   'confirmed';
    }

    $wp_estate_book_down            =   floatval( get_post_meta($invoice_id, 'invoice_percent', true) );
    $invoice_price                  =   floatval( get_post_meta($invoice_id, 'item_price', true)) ;

    if($wp_estate_book_down==100 ){
       $booking_details['booking_invoice_no']  =   $invoice_id;
    }



    if($is_confirmed==1){
        // echo "<br> bookid = ".$bookid;
        // die('is_confirmed_die');
        update_post_meta($bookid, 'booking_status', 'confirmed');
        $booking_details['booking_status']='confirmed';

        update_post_meta($invoice_id, 'invoice_status', 'confirmed');
        update_post_meta($invoice_id, 'depozit_paid', 0);
        update_post_meta($invoice_id, 'depozit_to_be_paid', $depozit_to_be_paid);
        update_post_meta($invoice_id, 'balance'  , $balance);
        $invoice_details['invoice_status']  =   'confirmed';
        $invoice_details['to_be_paid']      =   0;
        $invoice_details['balance']         =   $balance;
    }






    if($is_confirmed==1){
        $curent_listng_id   =   get_post_meta($bookid,'booking_id',true);
        $reservation_array  =   custom_wpestate_get_booking_dates($curent_listng_id, $bookid);
        // echo "<pre>";
        // print_r($reservation_array);
        // echo "</pre>";
        // update_post_meta($curent_listng_id, 'booking_dates','');
        update_post_meta($curent_listng_id, 'booking_dates', $reservation_array);

    }

    // die('innn');

    // send notification emails
    if($is_confirmed!==1){
        $receiver          =   get_userdata($user_id);
        $receiver_email    =   $receiver->user_email;
        $receiver_login    =   $receiver->user_login;
        $from               =   $current_user->user_login;
        $to                 =   $user_id;
        $subject            =   esc_html__( 'New Invoice','wprentals');
        $description        =   esc_html__( 'A new invoice was generated for your booking request','wprentals');

        wpestate_add_to_inbox($userID,$userID,$to,$subject,$description,1);
        wpestate_send_booking_email('newinvoice',$receiver_email);
    }else{
        //direct confirmation emails
        $user_email         =   $current_user->user_email;

        $receiver          =   get_userdata($user_id);
        $receiver_email    =   $receiver->user_email;
        $receiver_login    =   $receiver->user_login;


        //$receiver_id    =   wpsestate_get_author($booking_id);

        $receiver_email =   get_the_author_meta('user_email', $user_id);
        $receiver_name  =   get_the_author_meta('user_login', $user_id);
        wpestate_send_booking_email("bookingconfirmeduser",$receiver_email);// for user
        wpestate_send_booking_email("bookingconfirmed_nodeposit",$user_email);// for owner
        // add messages to inbox

        $subject=esc_html__( 'Booking Confirmation','wprentals');
        $description=esc_html__( 'A booking was confirmed','wprentals');
        wpestate_add_to_inbox($userID,$receiver_name,$userID,$subject,$description);

        $subject=esc_html__( 'Booking Confirmed','wprentals');
        $description=esc_html__( 'A booking was confirmed','wprentals');
        wpestate_add_to_inbox($receiver_id,$username,$receiver_id,$subject,$description);

    }


    if ( $new_booking == 1 ) {
        wp_redirect( site_url().'/wp-admin/post.php?post='.$invoice_id.'&action=edit' ); 
        exit;
    }

}

function custom_wpestate_get_booking_dates($listing_id, $booking_id){
    $args=array(
        'post_type'        => 'wpestate_booking',
        'post_status'      => 'any',
        'posts_per_page'   => -1,
        'meta_query' => array(
                            array(
                                'key'       => 'booking_id',
                                'value'     => $listing_id,
                                'type'      => 'NUMERIC',
                                'compare'   => '='
                            ),
                            array(
                                'key'       =>  'booking_status',
                                'value'     =>  'confirmed',
                                'compare'   =>  '='
                            )
                        )
        );
    
    $reservation_array          =   get_post_meta($listing_id, 'booking_dates',true);

    $new_booking_array = array();
    $new_booking_array1 = array();

    foreach( $reservation_array as $key => $value ) {
        // echo "<br> key = ".$key;
        if ( $value != $booking_id ) {
            $new_booking_array[$key] = $value;
        }
    }
    
    // array_push($new_booking_array1, $new_booking_array);
    
    // $wprentals_is_per_hour      =   wprentals_return_booking_type($listing_id);
    
    if( !is_array($reservation_array) || $reservation_array=='' ){
        $reservation_array  =   array();
    }
     
    $booking_selection  =   new WP_Query($args);
    $now=time();
    if($wprentals_is_per_hour==2){
        $daysago = $now-1*24*60*60;
    }else{
        $daysago = $now-2*24*60*60;
    }
  
    if ($booking_selection->have_posts()){    

        while ($booking_selection->have_posts()): $booking_selection->the_post();


            $pid                =   get_the_ID();
            $fromd              =   esc_html(get_post_meta($pid, 'booking_from_date', true));
            $tod                =   esc_html(get_post_meta($pid, 'booking_to_date', true));
            $unix_time_start    =   strtotime ($fromd);
            $unix_time_end      =   strtotime ($tod);
            
            
            if ($unix_time_start > $daysago){ // add booking from 1 if h, 2 if day -  days ago 
                if($wprentals_is_per_hour==2){
                    $new_booking_array[$unix_time_start]=$unix_time_end;
                }else{
                    $from_date      =   new DateTime($fromd);
                    $from_date_unix =   $from_date->getTimestamp();
                    $to_date        =   new DateTime($tod);
                    $to_date_unix   =   $to_date->getTimestamp();

                    $new_booking_array[$from_date_unix] =   $pid;
                    $from_date_unix                     =   $from_date->getTimestamp();


                    while ($from_date_unix < $to_date_unix){
                        $new_booking_array[$from_date_unix]=$pid;
                        $from_date->modify('tomorrow');
                        $from_date_unix =   $from_date->getTimestamp();
                    }  
                }        
            }
        endwhile;
     
        wp_reset_query();
    }        

    return $new_booking_array;
    
}


if ( $_GET['test1'] == 1 ) {

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // update_post_meta('42192', 'booking_status', 'confirmed');
    echo "<br> Status = ";
    print_r(get_post_meta($_GET['id'], 'booking_status', true));

    // echo site_url().'/my-reservations/';
    // $wp_estate_book_down            =   floatval( get_post_meta("42027", 'invoice_percent', true) );
    // echo "<br> wp_estate_book_down = ".$wp_estate_book_down;
    // // die('innn');

    // echo "<br> booking_status = ".get_post_meta($bookid, 'booking_status', true);
    // die('innn');
}
function register_custom_menu_page() {
    add_menu_page('All in One Calendar', 'All in One Calendar', 'add_users', 'allinonecalender', '_admin_all_inOne_calender', 'dashicons-calendar', 12); 
}
add_action('admin_menu', 'register_custom_menu_page');

function _admin_all_inOne_calender(){
   wp_enqueue_script('wprentals-child', get_stylesheet_directory_uri().'/js/admin_ajaxcalls_add.js',array('jquery'), '1.0', true);
   wp_enqueue_style( 'wprentals-child', get_stylesheet_directory_uri() . '/dashboard/css/admin_all_in_one-style.css');

   include('admin_all_inOne.php');
}

// add_action('wp_enqueue_scripts', 'wpse26822_script_fix', 100);
// function wpse26822_script_fix()
// {
//     wp_dequeue_script('wprentals');
//     wp_enqueue_script('wprentals-child', get_stylesheet_directory_uri().'/js/ajaxcalls_add.js', array('jquery'));
// }
