<?php

if ( isset( $_POST['_wpnonce'] ) ) {

    if (!empty($_SERVER['DOCUMENT_ROOT']))
        require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    $additional_url = '&tab=prevfuture';

    if ( wp_verify_nonce( $_POST['_wpnonce'], TIMEPADEVENTS_SETTINGS ) && current_user_can( 'activate_plugins' ) ) {
        
        if ( isset( $_POST['save_changes'] ) && !empty( $_POST['save_changes'] )/* && !isset( $_POST['cancel_account'] )*/ ) {
            $data = get_option( 'timepad_data' );
            
            if ( isset( $_POST['timepad_previous_events'] ) && !empty( $_POST['timepad_previous_events'] ) ) {
                $data['previous_events'] = sanitize_text_field( $_POST['timepad_previous_events'] );
            } else {
                $data['previous_events'] = 'ignore';
            }
            
            if ( isset( $_POST['timepad_future_event_date'] ) && !empty( $_POST['timepad_future_event_date'] ) ) {
                $data['future_event_date'] = sanitize_text_field( $_POST['timepad_future_event_date'] );
            } else {
                $data['future_event_date'] = 'current';
            }
            
            update_option( 'timepad_data', $data );
        }
        
    } else wp_die( __( 'Wrong security nonce value.', 'timepad' ) );
    
    wp_safe_redirect( TIMEPADEVENTS_SETTINGS_HTTP_URL . $additional_url );
    
} else die( 'Security nonce value is empty.' );
