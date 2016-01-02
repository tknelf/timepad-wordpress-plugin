<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TimepadEvents_Admin_Settings_Previous' ) ) :
    
    class TimepadEvents_Admin_Settings_Previous extends TimepadEvents_Admin_Settings_Base {
        
        public function __construct() {
            parent::__construct();
            
            //set the tab title
            $this->tab_title = __( 'Previous Events', 'timepad' );
            
            $this->action = TIMEPADEVENTS_PLUGIN_HTTP_PATH . 'lib/admin/menu/settings/save_previous.php';
            
        }
        
        public function display() {
            $data = !empty( $this->_data ) ? $this->_data : array();
            include_once TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu/views/settings-previous-data.php';
        }
        
    }
    
endif;