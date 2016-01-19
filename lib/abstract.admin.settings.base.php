<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TimepadEvents_Admin_Settings_Base' ) ) :

    abstract class TimepadEvents_Admin_Settings_Base extends TimepadEvents_Admin_Base {
        
        /**
         * Token from TimePad API to make requests
         * @access protected
         * @var string
         */
        protected $_token = '';
        
        /**
         * Possible error messages from TimePad API
         * @access protected
         * @var string
         */
        protected $_error_response = '';
        
        protected $_settings = array();
        protected $_settings_tabs = array();
        
        /**
         * Settings tab title
         * 
         * @access public
         * @var    string
         */
        public $tab_title;
        
        /**
         * Active settings page tab
         * @access protected
         * @var string
         */
        public $active_tab    = array();
        
        /**
         * Settings page tab form action url
         * @access protected
         * @var string
         */
        public $action;
        
        /**
         * Settings tab logo
         * @access public
         * @var    string
         */
        public $logo;

        public function __construct() {
            parent::__construct();
            
            //get token value from cookie or from option. Set token option if the one is empty
            if ( isset( $_COOKIE['timepad_token'] ) && !empty( $_COOKIE['timepad_token'] ) ) {
                $this->_token = esc_attr( $_COOKIE['timepad_token'] );
                if ( !isset( $this->_data['token'] ) || empty( $this->_data['token'] ) ) {
                    TimepadEvents_Helpers::update_option_key( TIMEPADEVENTS_OPTION, $this->_token, 'token' );
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
            
            $this->action = TIMEPADEVENTS_PLUGIN_HTTP_PATH . 'lib/admin/menu/settings/save_' . $this->handler . '.php';
            
            $this->logo = TIMEPADEVENTS_PLUGIN_HTTP_PATH . 'assets/images/admin/timepad-logo.png';
            
            /**
             * Action about post trash.
             * If plugin data exist the TimePad event - the one need to be deleted from the option
             */
            $tmp_this = $this;
            add_action( 'wp_trash_post', function( $id ) use ( $tmp_this ) {
                $post_data = get_post_meta( $id, TIMEPADEVENTS_META, true );
                $tmp_events = isset( $tmp_this->_data['events'] ) ? $tmp_this->_data['events'] : array();
                if ( !empty( $tmp_events ) && is_array( $tmp_events ) && !empty( $post_data ) && is_array( $post_data ) ) {
                    foreach ( $tmp_events as $org_id => $event_array ) {
                        if ( isset( $event_array[$post_data['event_id']] ) ) {
                            unset( $tmp_this->_data['events'][$org_id][$post_data['event_id']] );
                        }
                        if ( empty( $tmp_this->_data['events'][$org_id] ) ) unset( $tmp_this->_data['events'][$org_id] );
                    }
                    
                    TimepadEvents_Helpers::update_option_key( TIMEPADEVENTS_OPTION, $tmp_this->_data['events'], 'events' );
                }
            } );
        }

        /**
         * This function returns category term object for given id
         * 
         * @param int $id ID of the term
         * @param tring $output Constant OBJECT, ARRAY_A, or ARRAY_N
         * @param string $filter Optional, default is raw or no WordPress defined filter will applied.
         * @access protected
         * @return object|array
         */
        protected function _get_category( $output = OBJECT, $filter = 'raw' ) {
            if ( !isset( $this->_data['autounsync'] ) || empty( $this->_data['autounsync'] ) ) {
                $category = get_term( $this->_data['category_id'], TIMEPADEVENTS_POST_TYPE . '_category', $output, $filter );
            } else {
                $category = get_category( $this->_data['category_id'], $output );
            }
            
            if ( is_wp_error( $category ) ) {
                return $category;
            }
            
            _make_cat_compat( $category );
            
            return $category;
        }

    }

endif;