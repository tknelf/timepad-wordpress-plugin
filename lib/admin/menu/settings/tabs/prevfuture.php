<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TimepadEvents_Admin_Settings_PrevFuture' ) ) :
    
    class TimepadEvents_Admin_Settings_PrevFuture extends TimepadEvents_Admin_Settings_Base {
        
        public function __construct() {
            parent::__construct();
            
            //set the tab title
            $this->tab_title = __( 'Previous and future events', 'timepad' );
            
            $this->action = TIMEPADEVENTS_PLUGIN_HTTP_PATH . 'lib/admin/menu/settings/save_prevfuture.php';
            
        }
        
        public function display() {
            $data = !empty( $this->_data ) ? $this->_data : array();
            include_once TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu/views/settings-prevfuture-data.php';
        }
        
    }
    
endif;