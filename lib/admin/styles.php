<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Styles Admin Class
 *
 * Class includes TimePad admin styles
 *
 * @class       TimepadEvents_Admin_Styles
 * @version     1.0.0
 * @package     TimePadEvents/Admin
 * @author      TimePad Team
 * @extends     TimepadEvents_Admin_Base
 * @category    Admin Class
 */
if ( ! class_exists( 'TimepadEvents_Admin_Styles' ) ) :
    class TimepadEvents_Admin_Styles extends TimepadEvents_Admin_Base {
    
        public function __construct() {
            parent::__construct();
        }

        /**
         * Init function that calls at TimepadEvents_Setup_Admin class
         *
         * @return void
         */
        public function init() {
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_init_styles' ) );
        }
        
        /**
         * Function for admin_enqueue_scripts WordPress core hook
         * to include TimePad Admin CSS files
         *
         * @param type $hook
         * @return void
         */
        public function admin_init_styles( $hook ) {
            wp_register_style( 'timepadevents-admin', plugins_url( 'assets/css/admin/admin.css', TIMEPADEVENTS_FILE ) );
            wp_enqueue_style( 'timepadevents-admin' );
        }
    }
endif;