<?php

if ( isset( $_POST['_wpnonce'] ) ) {
    
    require_once( '../../../../../../../wp-load.php' );

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    $additional_url = '&tab=general';

    if ( wp_verify_nonce( $_POST['_wpnonce'], TIMEPADEVENTS_SETTINGS ) && current_user_can( 'activate_plugins' ) ) {
        
        function timepad_save_autounsync( $data, $post ) {
            
            if ( isset( $post['timepad_auto_unsyncronize'] ) && !empty( $post['timepad_auto_unsyncronize'] ) ) {
                $data['autounsync'] = 1;
                $data['autounsync_to_post_type'] = sanitize_text_field( $post['timepad_autounsync_to_post_type'] );
                $data['category_id'] = intval( $post['timepad_autounsync_to_post_category'] );
                $data['autounsync_to_status'] = sanitize_text_field( $post['timepad_autounsync_to_status'] );
            } else {
                $data['autounsync'] = 0;
                $data['autounsync_to_post_type'] = null;
                if ( isset( $data['term_id'] ) ) {
                    $data['category_id'] = intval( $data['term_id'] );
                } else {
                    
                    
                    $cat_name     = __( 'TimePad Events', 'timepad' );
                    $cat_nicename = TIMEPADEVENTS_POST_TYPE_CATEGORY;
                    $cat_taxonomy = TIMEPADEVENTS_POST_TYPE . '_category';
                    $cat_i = 1;

                    /**
                     * Check for category exists, maybe user enter the one by himself...
                     */
                    if ( term_exists( $cat_name, $cat_taxonomy ) ) {
                        $category = get_term_by( 'name', $cat_name, $cat_taxonomy, ARRAY_A );
                        if ( isset( $category['term_id'] ) ) {
                            $data['category_id'] = $data['term_id'] = intval( $category['term_id'] );
                            TimepadEvents_Helpers::update_option_key( TIMEPADEVENTS_OPTION, $data['category_id'], 'category_id' );
                            TimepadEvents_Helpers::update_option_key( TIMEPADEVENTS_OPTION, $data['term_id'], 'term_id' );
                        }
                    } else {
                        while ( !term_exists( $cat_name, $cat_taxonomy ) ) {
                            if ( !function_exists( 'wp_insert_category' ) ) {
                                require_once TIMEPADEVENTS_ADMIN_ABS_PATH . 'includes/taxonomy.php';
                            }
                            //insert new category for events
                            if ( $category_id = wp_insert_category( array( 
                                'cat_name'           => $cat_name
                                ,'category_nicename' => $cat_nicename
                                ,'taxonomy'          => $cat_taxonomy ) ) 
                            ) {
                                $data['category_id'] = $data['term_id'] = intval( $category_id );
                                TimepadEvents_Helpers::update_option_key( TIMEPADEVENTS_OPTION, $data['category_id'], 'category_id' );
                                TimepadEvents_Helpers::update_option_key( TIMEPADEVENTS_OPTION, $data['term_id'], 'term_id' );
                            } else {
                                //security for hacks with unlimited requests
                                if ( $cat_i == 20 ) break;

                                $cat_i++;
                                $cat_name     += ' ' . $cat_i;
                                $cat_taxonomy += ' ' . $cat_i;
                            }
                        }
                    }
                    
                    
                }
                $data['autounsync_to_status'] = null;
            }
            
            return $data;
        }
        
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

        if ( isset( $_POST['reset_sync'] ) && !empty( $_POST['reset_sync'] ) ) {
            TimepadEvents_Setup_Admin::timepadevents_plugin_uninstall();
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
            $data = timepad_save_autounsync( $data, $_POST );
            update_option( 'timepad_data', $data );
        }
        
        if ( isset( $_POST['syncronize'] ) && !empty( $_POST['syncronize'] ) ) {
            wp_safe_redirect( TIMEPADEVENTS_SETTINGS_HTTP_URL . '&syncronize=1' );
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
            
            $data = timepad_save_autounsync( $data, $_POST );
            
            update_option( 'timepad_data', $data );
            
            if ( !isset( $data['autounsync'] ) || empty( $data['autounsync'] ) ) {
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
                    $wpdb->update( $wpdb->terms, $cat_args, array( 'term_id' => $data['category_id'] ) );
                }
            }

        }
    } else wp_die( __( 'Wrong security nonce value.', 'timepad' ) );
    
    wp_safe_redirect( TIMEPADEVENTS_SETTINGS_HTTP_URL . $additional_url );
    
} else die( 'Security nonce value is empty.' );