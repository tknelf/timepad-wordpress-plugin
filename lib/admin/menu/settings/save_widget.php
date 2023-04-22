<?php

if ( isset( $_POST['_wpnonce'] ) ) {

    if (!empty($_SERVER['DOCUMENT_ROOT']))
        require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    $additional_url = '&tab=widget';

    if ( wp_verify_nonce( $_POST['_wpnonce'], TIMEPADEVENTS_SETTINGS ) && current_user_can( 'activate_plugins' ) ) {
        
        if ( isset( $_POST['save_changes'] ) && !empty( $_POST['save_changes'] )/* && !isset( $_POST['cancel_account'] )*/ ) {
            $data = get_option( 'timepad_data' );
            
            if ( isset( $_POST['timepad_widget_regulation'] ) && !empty( $_POST['timepad_widget_regulation'] ) ) {
                $data['widget_regulation'] = sanitize_text_field( $_POST['timepad_widget_regulation'] );
            } else {
                $data['widget_regulation'] = 'auto_after_desc';
            }
            
            if ( isset( $_POST['timepad_widget_customization_id'] ) && !empty( $_POST['timepad_widget_customization_id'] ) ) {
                $data['widget_customization_id'] = intval( $_POST['timepad_widget_customization_id'] );
            } else {
                $data['widget_customization_id'] = 0;
                unset( $data['widget_customization_id'] );
            }
            
            update_option( 'timepad_data', $data );
        }
        
    } else wp_die( __( 'Wrong security nonce value.', 'timepad' ) );
    
    wp_safe_redirect( TIMEPADEVENTS_SETTINGS_HTTP_URL . $additional_url );
    
} else die( 'Security nonce value is empty.' );
