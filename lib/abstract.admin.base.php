<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'TimepadEvents_Admin_Base' ) ) :

    /**
     * Base Admin abstract class
     *
     * @class       TimepadEvents_Admin_Base
     * @since       1.0.0
     * @package     NetMolis/Admin
     * @author      Igor Sazonov <sovletig@yandex.ru>
     * @category    Admin Base Abstract class
     * @abstract
     */
    abstract class TimepadEvents_Admin_Base extends TimepadEvents_Base {

        public $self_url;
        
        protected $_data = array();
        
        protected $_category_id = 0;
        
        protected $_current_organization_id = 0;

        protected $_timepadevents_settings = array();

        /**
         * @var array Plugin Admin JavaScript variables
         */
        protected $_timepadevents_js_array = array();

        protected $_timepadevents_js_messages_array = array();

        public function __construct() {
            parent::__construct();
            
            $this->_data = get_option( 'timepad_data' );
            
            $this->_category_id     = isset( $this->_data['category_id'] ) ? intval( $this->_data['category_id'] ) : 0;
            $this->_current_organization_id = isset( $this->_data['current_organization_id'] ) ? intval( $this->_data['current_organization_id'] ) : 0;
            
            $this->_menu_pages = array(
                'settings' => array(
                    'handler' => 'timepadevents-settings-page'
                )
            );

        }

        /**
         * This function returns current post/item object by given post id
         *
         * @uses   get_post() function
         * @link   http://codex.wordpress.org/Function_Reference/get_post
         * @since  1.0.0
         * @author Igor Sazonov <sovletig@yandex.ru>
         * @access protected
         * @return object Post object by given id
         */
        protected function _get_post() {
            return get_post( $this->_id );
        }

        protected function _is_plugin_admin_page() {
            if ( is_admin() ) {
                global $pagenow;
                if ( $pagenow == 'post.php' ) {
                    if ( $this->_get_post()->post_type == $this->_config['type'] ) {
                        return true;
                    }
                }
                if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] = $this->_config['type'] && isset( $_GET['page'] ) ) {
                    return true;
                }
            }

            return false;
        }

    }

endif;