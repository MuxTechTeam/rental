<?php
// register the custom post type
// echo "custom post type override"; die();
?>
<?php
// remove_action('manage_posts_custom_column', 'wpestate_populate_booking_columns');
// remove_filter( 'manage_edit-wpestate_booking_columns', 'wpestate_my_booking_columns');
add_filter( 'manage_edit-wpestate_booking_columns', 'wpestate_my_booking_columns' );

if( !function_exists('wpestate_my_booking_columns') ):
    function wpestate_my_booking_columns( $columns ) {
        $slice=array_slice($columns,2,2);
        unset( $columns['comments'] );
        unset( $slice['comments'] );
        $splice=array_splice($columns, 2);  
        $columns['booking_estate_status']   = esc_html__( 'Status','wprentals-core');
        $columns['booking_estate_period']   = esc_html__( 'Period','wprentals-core');
        $columns['booking_estate_listing']  = esc_html__( 'Listing','wprentals-core');
        $columns['booking_estate_owner']    = esc_html__( 'Owner','wprentals-core');
        $columns['booking_estate_renter']   = esc_html__( 'Renter','wprentals-core');
        $columns['booking_estate_value']    = esc_html__( 'Value','wprentals-core');
        $columns['booking_estate_value_to_be_paid']   = esc_html__( 'Initial Deposit','wprentals-core');
        return  array_merge($columns,array_reverse($slice));
    }
endif; // end   wpestate_my_columns  

add_action( 'manage_posts_custom_column', 'wpestate_populate_booking_columns' );
if( !function_exists('wpestate_populate_booking_columns') ):
    function wpestate_populate_booking_columns( $column ) {
        $the_id=get_the_ID();

        $invoice_no         =   get_post_meta($the_id, 'booking_invoice_no', true);
        if(  'booking_estate_status' == $column){
            $booking_status         =  esc_html(get_post_meta($the_id, 'booking_status', true));
            $booking_status_full    = esc_html(get_post_meta($the_id, 'booking_status_full', true));
            
            // echo "<br> booking_status = ".$booking_status;
            // echo "<br> booking_status_full = ".$booking_status_full;

            if($booking_status == 'canceled' && $booking_status_full== 'canceled'){
                esc_html_e('canceled','wprentals-core');
            }else if($booking_status == 'confirmed' && $booking_status_full== 'confirmed'){
                echo    esc_html__('confirmed','wprentals-core').' | ' .esc_html__('fully paid','wprentals-core');
            }else if($booking_status == 'confirmed' && $booking_status_full== 'waiting'){
                echo    esc_html__('deposit paid','wprentals-core').' | ' .esc_html__('waiting for full payment','wprentals-core');
            }else if($booking_status == 'refunded' ){
                esc_html_e('refunded','wprentals-core');
            }else if($booking_status == 'pending' ){
                esc_html_e('pending','wprentals-core');
            }else if($booking_status == 'waiting' ){
                esc_html_e('waiting','wprentals-core');
            }
       
        }
          
        if(  'booking_estate_period' == $column){
            echo esc_html__( 'from','wprentals-core').' '.esc_html(get_post_meta($the_id, 'booking_from_date', true)).' '.esc_html__( 'to','wprentals-core').' '. esc_html(get_post_meta($the_id, 'booking_to_date', true));
        }
        
        if(  'booking_estate_listing' == $column){
            $curent_listng_id= get_post_meta($the_id, 'booking_id',true);
            echo get_the_title($curent_listng_id);
        }
        
        if(  'booking_estate_owner' == $column){
            $owner_id = get_post_meta($the_id, 'owner_id', true);
            $user = get_user_by( 'id', $owner_id );
            print $user->user_login;
        }
        
        if(  'booking_estate_renter' == $column){
            print $author             =   get_the_author();
        }
        
        if(  'booking_estate_value' == $column){
            $total_price        =   get_post_meta($invoice_no, 'item_price', true);
            print $total_price;
        }
        if(  'booking_estate_value_to_be_paid' == $column){
            $to_be_paid         =   floatval ( get_post_meta($invoice_no, 'depozit_to_be_paid', true));
            print $to_be_paid;
        }
        
       
        
    }
endif;
  
?>