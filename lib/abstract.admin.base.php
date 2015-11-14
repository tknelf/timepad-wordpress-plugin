<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'TimepadEvents_Admin_Base' ) ) :

    /**
     * Base Admin abstract class
     *
     * @class       TimepadEvents_Admin_Base
     * @since       1.0.0
     * @package     TimepadEvents/Admin
     * @author      Igor Sazonov <sovletig@yandex.ru>
     * @category    Admin Base Abstract class
     * @abstract
     */
    abstract class TimepadEvents_Admin_Base extends TimepadEvents_Base {
        
        /**
         * @var array The plugin data array
         */
        protected $_data = array();

        /**
         * @var array Plugin Admin JavaScript variables
         */
        protected $_timepadevents_js_array = array();
        
        /**
         * @var int Current native WordPress User ID
         */
        protected $_current_user_id;
        
        /**
         * @var string|float Current site WordPress version
         */
        protected $_wp_version;

        public function __construct() {
            parent::__construct();
            
            $this->_data = get_option( $this->_config['optionkey'] );
            
            $this->_current_user_id = get_current_user_id();
            
            global $wp_version;
            $this->_wp_version = $wp_version;
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
        
        /**
         * 
         * @since  1.0.5
         * @global string $pagenow WordPress native global var about admin page now
         * @return boolean
         */
        protected function _is_plugin_admin_page() {
            if ( is_admin() ) {
                global $pagenow;
                
                if ( $pagenow == 'post.php' ) {
                    if ( $this->_get_post()->post_type == $this->_config['type'] ) {
                        return true;
                    }
                }
                
                if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == TIMEPADEVENTS_POST_TYPE ) {
                    return true;
                }
            }

            return false;
        }
        
        /**
         * Check for base plugin requirements.
         * If errors found, the message(s) will be displayed
         * 
         * @since  1.0.5
         * @author Igor Sazonov <sovletig@yandex.ru>
         * @access protected
         * @return string Plugin requirements errors string
         */
        protected function _requirements_messages() {
            $errors = array();
            if ( version_compare( phpversion(), $this->requirements['php'], '<' ) ) {
                $errors[] = '- ' . sprintf( __( 'Your PHP version is less than %s', 'timepad' ), '<strong>' . $this->requirements['php'] . '</strong>' );
            }
            
            if ( version_compare( $this->_wp_version, $this->requirements['wp'], '<' ) ) {
                $errors[] = '- ' . sprintf( __( 'Your website WordPress version is less than %s', 'timepad' ), '<strong>' . $this->requirements['wp'] . '</strong>' );
            }
            
            if ( !empty( $errors ) ) {
                array_unshift( $errors, __( 'TimePad Events error messages:', 'timepad' ) );
                return implode( '<br />', $errors );
            }
            
            return '';
        }

    }

endif;