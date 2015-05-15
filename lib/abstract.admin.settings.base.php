<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TimepadEvents_Admin_Settings_Base' ) ) :

    abstract class TimepadEvents_Admin_Settings_Base extends TimepadEvents_Admin_Base {
        
        protected $_request_args = array();
        protected $_token = '';
        protected $_error_response = '';
        protected $_organizer_request_url;
        protected $_create_organization_url;
        protected $_settings = array();
        protected $_settings_tabs = array();
        public $active_tab    = array();
        public $action;

        public $title;
        public $fields = array();


        public function __construct() {
            parent::__construct();
            
            if ( isset( $_COOKIE['timepad_token'] ) && !empty( $_COOKIE['timepad_token'] ) ) {
                $this->_token = esc_attr( $_COOKIE['timepad_token'] );
                if ( !isset( $this->_data['token'] ) || empty( $this->_data['token'] ) ) {
                    TimepadEvents_Helpers::update_option_key( 'timepad_data', $this->_token, 'token' );
                    $this->_data['token'] = $this->_token;
                }
            } else {
                if ( isset( $this->_data['token'] ) && !empty( $this->_data['token'] ) ) {
                    $this->_token = $this->_data['token'];
                } else {
                    if ( isset( $_GET['timepad_error'] ) && !empty( $_GET['timepad_error'] ) ) {
                        $this->_error_response = utf8_decode( urldecode( $_GET['timepad_error'] ) );
                    }
                }
            }
            
            $this->_request_args = array(
                'headers' => array( 'Content-type' => 'application/json' )
            );
            
            $this->_organizer_request_url   = 'https://api.timepad.ru:443/introspect';
            $this->_create_organization_url = 'https://api.timepad.ru/v1/organizations';
            $this->action = TIMEPADEVENTS_PLUGIN_HTTP_PATH . 'lib/admin/menu/settings/save.php';
            
            add_action( 'wp_trash_post', function( $id ) {
                $post_data = get_post_meta( $id, 'timepad_meta', true );
                $tmp_events = isset( $this->_data['events'] ) ? $this->_data['events'] : array();
                if ( !empty( $tmp_events ) && is_array( $tmp_events ) ) {
                    foreach ( $tmp_events as $org_id => $event_array ) {
                        if ( isset( $event_array[$post_data['event_id']] ) ) {
                            unset( $this->_data['events'][$org_id][$post_data['event_id']] );
                        }
                        if ( empty( $this->_data['events'][$org_id] ) ) unset( $this->_data['events'][$org_id] );
                    }
                    
                    TimepadEvents_Helpers::update_option_key( 'timepad_data' , $this->_data['events'], 'events' );
                }
            } );
        }


        protected function _get_category( $id, $output = OBJECT, $filter = 'raw' ) {
            $category = get_term( $id, parent::$post_type . '_category', $output, $filter );
            if ( is_wp_error( $category ) )
                return $category;
            
            _make_cat_compat( $category );

            return $category;
        }

    }

endif;