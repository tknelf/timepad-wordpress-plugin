<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TimepadEvents_Admin_Settings_Previous' ) ) :
    
    class TimepadEvents_Admin_Settings_Previous extends TimepadEvents_Admin_Settings_Base {
        
        public function __construct() {
            parent::__construct();
            
            //set the tab title
            $this->tab_title = __( 'Previous Events', 'timepad' );
        }
        
        public function display() {
            if ( $this->_data ) {
                $data = $this->_data;
                include_once TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu/views/settings-previous-data.php';
            }
        }
        
    }
    
endif;