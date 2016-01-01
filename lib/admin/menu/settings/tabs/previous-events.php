<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TimepadEvents_Admin_Settings_PreviousEvents' ) ) :
    
    class TimepadEvents_Admin_Settings_PreviousEvents extends TimepadEvents_Admin_Settings_Base {
        
        public function __construct() {
            parent::__construct();
            
            //set the tab title
            $this->tab_title = __( 'Previous Events', 'timepad' );
        }
        
    }
    
endif;