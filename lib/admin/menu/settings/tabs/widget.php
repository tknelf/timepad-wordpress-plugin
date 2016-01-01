<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TimepadEvents_Admin_Settings_Widget' ) ) :
    
    class TimepadEvents_Admin_Settings_Widget extends TimepadEvents_Admin_Settings_Base {
        
        public function __construct() {
            parent::__construct();
            
            //set the tab title
            $this->tab_title = __( 'Widget', 'timepad' );
            
            $this->action = TIMEPADEVENTS_PLUGIN_HTTP_PATH . 'lib/admin/menu/settings/save_widget.php';
        }
        
        public function display() {
            if ( $this->_data ) {
                $data = $this->_data;
                include_once TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu/views/settings-widget-data.php';
            }
        }
        
    }
    
endif;