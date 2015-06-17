<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Main Setup Class for init plugin and admin
 *
 * Main Frontend class that prepares all TimepadEvents frontend
 *
 * @class       TimepadEvents_Setup_Admin
 * @version     1.0.0
 * @package     TimepadEvents/Admin
 * @author      TimepadEvents Team
 * @extends     TimepadEvents_Admin_Base
 * @category    Main Class / Admin Class
 */

if ( ! class_exists( 'TimepadEvents_Setup_Admin' ) ) :
    
    class TimepadEvents_Setup_Admin extends TimepadEvents_Admin_Base {

        public function __construct() {
            parent::__construct();

            //core init classes store
            $this->_classes = array(
                'TimepadEvents_Admin_i18n'       => TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/i18n.php'
                ,'TimepadEvents_Admin_Post_Type' => TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/post-type.php'
                
                ,'TimepadEvents_Admin_Settings'  => TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu/settings.php'
                
                ,'TimepadEvents_Admin_Menu'      => TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu.php'
            );

            if ( is_admin() ) {
                $this->_classes['TimepadEvents_Admin_Scripts']   = TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/scripts.php';
                $this->_classes['TimepadEvents_Admin_Styles']    = TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/styles.php';
            }

            //init core hooks
            register_activation_hook(   TIMEPADEVENTS_BASENAME, array(  $this,    'timepadevents_register_activation_hook' ) );
            register_deactivation_hook( TIMEPADEVENTS_BASENAME, array( __CLASS__, 'timepadevents_plugin_deactivate' ) );
            register_uninstall_hook(    TIMEPADEVENTS_BASENAME, array( __CLASS__, 'timepadevents_plugin_uninstall' ) );

            //init
            foreach( $this->_classes as $class_name => $path ) {
                if ( ! class_exists( $class_name ) && file_exists( $path ) ) {
                    require_once( $path );
                }
                if ( class_exists( $class_name ) ) {
                    if ( $this->check_class( $class_name ) ) {
                        $instance = $class_name::getInstance();
                        if ( method_exists( $class_name, 'init' ) && is_callable( $class_name, 'init' ) ) {
                            $instance->init();
                        } else {
                            if ( has_action( 'timepadevents_admin_init_' . $instance->handler ) ) {
                                do_action( 'timepadevents_admin_init_' . $instance->handler );
                            }
                            if ( has_action( 'timepadevents_frontend_init_' . $instance->handler ) ) {
                                do_action( 'timepadevents_frontend_init_' . $instance->handler );
                            }
                        }
                    }
                }
            }
            
            //init TimePadEvents cron functionality
            add_action( 'timepad_cron', function() {
                if ( $this->_data['autoimport'] ) {
                    require_once(TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu/settings/tabs/general.php');
                    TimepadEvents_Admin_Settings_General::getInstance()->post_events($this->_data['current_organization_id']);
                }
            } );

        }

        /**
         * Register plugin activation hook
         * 
         * @access public
         * @return void
         */
        public function timepadevents_register_activation_hook() {
            if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }
            
            if ( !isset( $_COOKIE['timepad_site_url'] ) || empty( $_COOKIE['timepad_site_url'] ) ) {
                setcookie( 'timepad_site_url', TIMEPADEVENTS_SITEURL, 3600 * 24 * 5, '/' );
            }
        }

        /**
         * Register plugin deactivation hook
         * 
         * @access public
         * @return void
         */
        public static function timepadevents_plugin_deactivate() {
            if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }

            $timestamp = wp_next_scheduled( 'timepad_cron' );
            wp_unschedule_event( $timestamp, 'timepad_cron' );
        }

        public static function timepadevents_plugin_uninstall() {
            if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }
            
            $timepad_posts = get_posts(array(
                'post_type'       => TIMEPADEVENTS_POST_TYPE
                ,'posts_per_page' => -1
            ));
            if ( !empty( $timepad_posts ) && is_array( $timepad_posts ) ) {
                foreach ( $timepad_posts as $timepad_post ) {
                    wp_delete_post( $timepad_post->ID );
                }
            }
            
            $timepad_terms = get_terms( TIMEPADEVENTS_POST_TYPE . '_category', array( 'hide_empty' => false ) );
            if ( !empty( $timepad_terms ) && is_array( $timepad_terms ) ) {
                foreach ( $timepad_terms as $timepad_term ) {
                    wp_delete_term( $timepad_term->term_id, TIMEPADEVENTS_POST_TYPE . '_category' );
                }
            }
            
            delete_option( 'timepad_data' );
            delete_option( 'timepad_flushed' );
            
            setcookie( 'timepad_site_url', null, -1, '/' );
            setcookie( 'timepad_token', null, -1, '/' );

            wp_clear_scheduled_hook( 'timepad_cron' );
        }

    }

endif;