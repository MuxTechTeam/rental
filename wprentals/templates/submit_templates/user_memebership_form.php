<?php 
$current_user = wp_get_current_user();
$userID                 =   $current_user->ID;
$user_login             =   $current_user->user_login;
$user_pack              =   get_the_author_meta( 'package_id' , $userID );
$user_registered        =   get_the_author_meta( 'user_registered' , $userID );
$user_package_activation=   get_the_author_meta( 'package_activation' , $userID );
$images                 =   '';
$counter                =   0;
$wpestate_unit          =   esc_html( wprentals_get_option('wp_estate_measure_sys', '') );
$paid_submission_status = esc_html ( wprentals_get_option('wp_estate_paid_submission','') );
?> 


    <?php                        
    if( $paid_submission_status == 'membership'){
       print'
       <div class="submit_container">    
       <div class="submit_container_header">'.esc_html__( 'Membership','wprentals').'</div>'; 
       wpestate_get_pack_data_for_user($userID,$user_pack,$user_registered,$user_package_activation);
       print'</div>'; // end submit container
    }
    if( $paid_submission_status == 'per listing'){
        $price_submission               =   floatval( wprentals_get_option('wp_estate_price_submission','') );
        $price_featured_submission      =   floatval( wprentals_get_option('wp_estate_price_featured_submission','') );
        $submission_curency_status      =   wpestate_curency_submission_pick();
        print'
        <div class="submit_container">
        <div class="submit_container_header">'.esc_html__( 'Paid submission','wprentals').'</div>';
        print '<p class="full_form-nob">'.esc_html__(  'This is a paid submission.','wprentals').'</p>';
        print '<p class="full_form-nob">'.esc_html__(  'Price: ','wprentals').'<span class="submit-price">'.esc_html($price_submission).' '.esc_html($submission_curency_status).'</span></p>';
        print '<p class="full_form-nob">'.esc_html__(  'Featured (extra): ','wprentals').'<span class="submit-price">'.esc_html($price_featured_submission).' '.esc_html($submission_curency_status).'</span></p>';
        print'</div>'; // end submit container
     }
    ?>