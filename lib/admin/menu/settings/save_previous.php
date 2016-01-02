<?php

if ( isset( $_POST['_wpnonce'] ) ) {
    
    require_once( '../../../../../../../wp-load.php' );

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    $additional_url = '&tab=previous';

    if ( wp_verify_nonce( $_POST['_wpnonce'], TIMEPADEVENTS_SETTINGS ) && current_user_can( 'activate_plugins' ) ) {
        
        if ( isset( $_POST['save_changes'] ) && !empty( $_POST['save_changes'] )/* && !isset( $_POST['cancel_account'] )*/ ) {
            $data = get_option( 'timepad_data' );
            
            if ( isset( $_POST['timepad_previous_events'] ) && !empty( $_POST['timepad_previous_events'] ) ) {
                $data['previous_events'] = sanitize_text_field( $_POST['timepad_previous_events'] );
            } else {
                $data['previous_events'] = 'ignore';
            }
            
            if ( isset( $_POST['timepad_event_date'] ) && !empty( $_POST['timepad_event_date'] ) ) {
                $data['event_date'] = sanitize_text_field( $_POST['timepad_event_date'] );
            } else {
                $data['event_date'] = 'natural';
            }
            
            update_option( 'timepad_data', $data );
        }
        
    } else wp_die( __( 'Wrong security nonce value.', 'timepad' ) );
    
    wp_safe_redirect( TIMEPADEVENTS_ADMIN_URL . 'edit.php?post_type=' . TIMEPADEVENTS_POST_TYPE . '&page=' . TIMEPADEVENTS_ADMIN_OPTIONS_PAGE . $additional_url );
    
} else die( 'Security nonce value is empty.' );