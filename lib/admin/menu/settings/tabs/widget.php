<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TimepadEvents_Admin_Settings_Widget' ) ) :
    
    class TimepadEvents_Admin_Settings_Widget extends TimepadEvents_Admin_Settings_Base {
        
        public function __construct() {
            parent::__construct();
            
            //set the tab title
            $this->tab_title = __( 'Widget', 'timepad' );
        }
        
        public function display() {
            $data = !empty( $this->_data ) ? $this->_data : array();
            include_once TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu/views/settings-widget-data.php';
        }
        
    }
    
endif;