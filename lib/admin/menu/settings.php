<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TimepadEvents_Admin_Settings' ) ) :
    
    class TimepadEvents_Admin_Settings extends TimepadEvents_Admin_Settings_Base {

        public function __construct() {
            parent::__construct();

            $this->active_tab = isset( $_GET[ 'tab' ] ) ? esc_attr( $_GET[ 'tab' ] ) : 'general';

            if ( ! $this->_classes ) {
                if ( is_admin() ) {
                    $this->_classes = array(
                        'TimepadEvents_Admin_Settings_General' => TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu/settings/tabs/general.php'
                    );
                }
            }
        }

        public function init() {
            foreach ( $this->_classes as $class_name => $path ) {
                if ( file_exists( $path ) && !class_exists( $class_name ) ) {
                    include_once $path;
                }
                if ( file_exists( $path ) && class_exists( $class_name ) && $this->check_class( $class_name ) ) {
                    $instance = $class_name::getInstance();
                    $this->_settings_tabs[] = array(
                        'title'   => $instance->tab_title,
                        'class'   => $class_name,
                        'handler' => $instance->handler,
                        'fields'  => apply_filters( 'timepadevents_settings_fields_' . $instance->handler, $instance->fields )
                    );
                }
            }
        }

        public function display() {
            $self_url = $this->self_url;
            $active_tab = $this->active_tab;
            $tabs = $this->_settings_tabs;
            include 'views/settings-view.php';
        }
        
    }
    
endif;