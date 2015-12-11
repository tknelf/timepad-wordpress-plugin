<?php

if ( isset( $_POST['_wpnonce'] ) ) {
    
    require_once( '../../../../../../../wp-load.php' );

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    $additional_url = '';

    if ( wp_verify_nonce( $_POST['_wpnonce'], 'timepadevents-settings' ) && current_user_can( 'activate_plugins' ) ) {
        if ( isset( $_POST['cancel_account'] ) && !empty( $_POST['cancel_account'] ) ) {
            $data = get_option( 'timepad_data' );
            unset( $data['organizations'] );
            unset( $data['events'] );
            
            unset( $data['autoimport'] );
            unset( $data['autounsync'] );
            unset( $data['current_organization_id'] );
            
            unset( $data['token'] );
            unset( $_COOKIE['timepad_token'] );
            setcookie( 'timepad_token', null, -1, '/' );
            
            update_option( 'timepad_data', $data );
        }
        
        if ( isset( $_POST['cancel_organization'] ) && !empty( $_POST['cancel_organization'] ) ) {
            $data = get_option( 'timepad_data' );
            unset( $data['autoimport'] );
            unset( $data['autounsync'] );
            unset( $data['current_organization_id'] );
            
            update_option( 'timepad_data', $data );
        }
        
        if ( isset( $_POST['select_organization'] ) && !empty( $_POST['select_organization'] ) && isset( $_POST['organization'] ) && !empty( $_POST['organization'] ) ) {
            $data = get_option( 'timepad_data' );
            $data['current_organization_id'] = intval( $_POST['organization'] );
            $additional_url = '&syncronize=1';
            update_option( 'timepad_data', $data );
        }
        
        if ( isset( $_POST['syncronize'] ) && !empty( $_POST['syncronize'] ) ) {
            wp_safe_redirect( TIMEPADEVENTS_ADMIN_URL . 'edit.php?post_type=timepad-events&page=timepad-events-options&syncronize=1' );
            exit;
        }

        if ( isset( $_POST['save_changes'] ) && !empty( $_POST['save_changes'] )/* && !isset( $_POST['cancel_account'] )*/ ) {
            $data = get_option( 'timepad_data' );
            if ( isset( $_POST['timepad_autoimport'] ) && !empty( $_POST['timepad_autoimport'] ) ) {
                $data['autoimport'] = 1;
                if ( !wp_next_scheduled( 'timepad_cron' ) ) {
                    wp_schedule_event( time(), 'once_three_mins', 'timepad_cron' );
                }
            } else {
                $data['autoimport'] = 0;
                unset( $data['autoimport'] );
            }
            
            if ( isset( $_POST['timepad_auto_unsyncronize'] ) && !empty( $_POST['timepad_auto_unsyncronize'] ) && isset( $_POST['timepad_autounsync_to_post_type'] ) && !empty( $_POST['timepad_autounsync_to_post_type'] ) ) {
                $data['autounsync'] = 1;
                $data['autounsync_to_post_type'] = sanitize_text_field( $_POST['timepad_autounsync_to_post_type'] );
                $data['autounsync_to_post_category'] = intval( $_POST['timepad_autounsync_to_post_category'] );
            } else {
                $data['autounsync'] = 0;
                unset( $data['autounsync'] );
                
                $data['autounsync_to_post_type'] = null;
                unset( $data['autounsync_to_post_type'] );
                
                $data['autounsync_to_post_category'] = 0;
                unset( $data['autounsync_to_post_category'] );
            }
            
            if ( isset( $_POST['timepad_widget_regulation'] ) && !empty( $_POST['timepad_widget_regulation'] ) ) {
                $data['widget_regulation'] = sanitize_text_field( $_POST['timepad_widget_regulation'] );
            } else {
                $data['widget_regulation'] = 'auto_after_desc';
            }
            
            update_option( 'timepad_data', $data );
            
            $cat_args = array();
            if ( isset( $_POST['cat_name'] ) && !empty( $_POST['cat_name'] ) && strlen( esc_attr( $_POST['cat_name'] ) ) > 5 ) {
                if ( isset( $_POST['cat_name_current'] ) && !empty( $_POST['cat_name_current'] ) && strlen( esc_attr( $_POST['cat_name_current'] ) ) > 5 ) {
                    if ( esc_attr( $_POST['cat_name_current'] ) != esc_attr( $_POST['cat_name'] ) ) {
                        $cat_args['name'] = esc_attr( $_POST['cat_name'] );
                    }
                }
            } else wp_die( __( 'Category name must have string length more than 5 characters.', 'timepad' ) );

            if ( isset( $_POST['cat_slug'] ) && !empty( $_POST['cat_slug'] ) && strlen( esc_attr( $_POST['cat_slug'] ) ) > 5 ) {
                if ( isset( $_POST['cat_slug_current'] ) && !empty( $_POST['cat_slug_current'] ) && strlen( esc_attr( $_POST['cat_slug_current'] ) ) > 5 ) {
                    if ( esc_attr( $_POST['cat_slug_current'] ) != esc_attr( $_POST['cat_slug'] ) ) {
                        $cat_args['slug'] = sanitize_title_for_query( esc_attr( $_POST['cat_slug'] ) );
                    }
                }
            } else wp_die( __( 'Category slug must have string length more than 5 characters.', 'timepad' ) );
            
            if ( !empty( $cat_args ) ) {
                global $wpdb;
                $wpdb->update( 'wp_terms', $cat_args, array( 'term_id' => $data['category_id'] ) );
            }

        }
    } else wp_die( __( 'Wrong security nonce value.', 'timepad' ) );
    
    wp_safe_redirect( TIMEPADEVENTS_ADMIN_URL . 'edit.php?post_type=timepad-events&page=timepad-events-options' . $additional_url );
    
} else die( 'Security nonce value is empty.' );

