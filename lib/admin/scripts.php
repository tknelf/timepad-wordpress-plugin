<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Scripts Admin Class
 *
 * Class includes TimePad admin scripts
 *
 * @class       TimepadEvents_Admin_Scripts
 * @version     1.0.0
 * @package     TimePad/Admin
 * @author      TimePad Team
 * @extends     TimepadEvents_Admin_Base
 * @category    Admin Class
 */
if ( ! class_exists( 'TimepadEvents_Admin_Scripts' ) ) :
    
    class TimepadEvents_Admin_Scripts extends TimepadEvents_Admin_Base {
        
        /**
         * This @var is array of WordPress core scripts handlers 
         * that need to be included in TimePad admin screen
         * 
         * @access private
         * @var array
         */
        private static $_jsHandlesInsidePluginAdmin = array(
            'jquery'
        );
        
        public function __construct() {
            parent::__construct();
        }
        
        /**
         * Init function that calls at TimepadEvents_Setup_Admin class
         * 
         * @access public
         * @return void
         */
        public function init() {
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_init_scripts' ), 9999 );
            if ( isset( $this->_data['auto_js_include'] ) && !empty( $this->_data['auto_js_include'] ) ) {
                add_action( 'wp_head', array( $this, 'frontend_init_wp_head' ), 9999 );
            }
        }
        
        /**
         * Function for admin_enqueue_scripts WordPress core hook 
         * to include TimePad Admin JS scripts
         * 
         * @access public
         * @param type $hook
         */
        public function admin_init_scripts( $hook ) {
            
            foreach ( self::$_jsHandlesInsidePluginAdmin as $handle ) {
                if ( $handle == 'media-upload' ) {
                    if ( function_exists( 'wp_enqueue_media' ) ) {
                        wp_enqueue_media();
                    }
                }
                wp_enqueue_script( $handle );
            }

            wp_register_script( 'timepad-jquery-cookie', plugins_url( 'assets/js/admin/jquery.cookie.js', TIMEPADEVENTS_FILE ), self::$_jsHandlesInsidePluginAdmin, null/*, true*/ );
            wp_enqueue_script( 'timepad-jquery-cookie' );
            
            //TimePadEvents admin script
            wp_register_script( 'timepad-admin-js', plugins_url( 'assets/js/admin/admin.js', TIMEPADEVENTS_FILE ), self::$_jsHandlesInsidePluginAdmin, null/*, true*/ );
            wp_enqueue_script( 'timepad-admin-js' );

            //JavaScript Array to transfer data from backend/php/wp to frontend
            $this->_timepadevents_js_array = array(
                '_site_url'   => TIMEPADEVENTS_SITEURL
                ,'_admin_url' => TIMEPADEVENTS_ADMIN_URL
                ,'_security'  => wp_create_nonce( $this->_config['security_nonce'] )
                ,'_confirm'   => __( 'Are you sure?', 'timepad' )
            );

            wp_localize_script( 'timepad-admin-js', 'timepad', $this->_timepadevents_js_array );
            
            if ( $hook == 'tools_page_timepad-events-redirect' ) {
                wp_register_script( 'timepad-redirect', plugins_url( 'assets/js/admin/redirect.js', TIMEPADEVENTS_FILE ), array( 'jquery', 'timepad-jquery-cookie' ), null );
                wp_enqueue_script( 'timepad-redirect' );
            }
        }
        
        /**
         * Adds TimePad Widget script to the WordPress site automatically
         * 
         * @since  1.1
         * @access public
         * @return void
         */
        public function frontend_init_wp_head() {
            //echo '<script type="text/javascript" defer="defer" charset="UTF-8" data-timepad-widget="org_subscribe" src="' . $this->_config['external_js_file'] . '"></script>';
            echo '<script type="text/javascript" src="' . $this->_config['external_js_file'] . '"></script>';
        }
    }
endif;