<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'TimepadEvents_Base' ) ) :

    /**
     * Base Admin abstract class
     *
     * @class       TimepadEvents_Admin_Base
     * @since       1.0.0
     * @package     TimepadEvents/Base
     * @author      Igor Sazonov <sovletig@yandex.ru>
     * @category    Admin Base Abstract class
     * @abstract
     */
    abstract class TimepadEvents_Base {
        
        /**
         * TimePad Events plugin configs from config.ini
         * @access protected
         * @var array
         */
        protected $_config = array();

        /**
         * This array collect all local TimepadEvents Classes of some part of the plugin
         *
         * @access protected
         * @var array
         */
        protected $_classes = array();

        /**
         * Current class handler taken from current called class name,
         * for example class name: TimepadEvents_Admin_Metaboxes; handler is 'metaboxes'
         * This option will help to access class data
         * This variable is very useful for developers
         * By the $handler you can get access to Class Name and to Class instance
         *
         * @access public
         * @var string Handler of current class taken from class name
         */
        public $handler;
        
        /**
         * Post type handler
         * @var string
         */
        public static $post_type = 'timepad-events';
        
        /**
         * Singleton
         */
        private static $_instances = array();

        public function __construct( $post = null ) {
            
            $this->_config = self::_get_config();
            
            $this->handler = TimepadEvents_Helpers::get_class_handler( $this->get_called_class() );

        }

        /**
         * This method returns current subclass @var $_classes array
         *
         * @uses @var $_classes
         * @access public
         * @return array
         */
        public function getClasses() {
            return $this->_classes;
        }

        //Singleton
        public static function getInstance() {
            $class = get_called_class();
            if ( !isset( self::$_instances[$class] ) ) {
                self::$_instances[$class] = new $class();
            }
            return self::$_instances[$class];
        }
        
        /**
         * This function parse config.ini file
         * @access private
         * @return array
         */
        private static function _get_config() {
            return parse_ini_file( TIMEPADEVENTS_PLUGIN_ABS_PATH . 'config.ini' );
        }

        /**
         * This function returns current class name
         *
         * @uses   get_called_class() function
         * @link   http://php.net/manual/ru/function.get-called-class.php
         * @since  1.0.0
         * @author Igor Sazonov <sovletig@yandex.ru>
         * @access public
         * @return string The name of called class
         */
        public function get_called_class() {
            return get_called_class();
        }

        /**
         * This function checks current class to be subclass of needle parent class
         *
         * @param  string $class_name_child Name of current (child) class
         * @param  string $class_name_parent Name of needle to check parent class
         * @since  1.0.0
         * @author Igor Sazonov <sovletig@yandex.ru>
         * @access public
         * @return boolean
         */
        public function check_class( $class_name_child, $class_name_parent = __CLASS__ ) {
            return is_subclass_of( $class_name_child, $class_name_parent );
        }

        /**
         * This function define internal plugin constants
         *
         * @since  1.0.0
         * @author Igor Sazonov <sovletig@yandex.ru>
         * @access public
         * @return void
         */
        public static function define_constants() {
            $config = self::_get_config();
            
            if ( ! defined( 'TIMEPADEVENTS_VERSION' ) ) {
                /**
                 * TimePadEvents version
                 *
                 * @var string
                 * @return string TimePadEvents version
                 */
                define( 'TIMEPADEVENTS_VERSION', $config['version'] );
            }

            if ( ! defined( 'TIMEPADEVENTS_SITEURL' ) ) {
                /**
                 * Current WordPress site URL
                 *
                 * @var     string
                 * @return  string Your site WordPress URL
                 * @example https://wordpress.google.com
                 */
                define( 'TIMEPADEVENTS_SITEURL', site_url() );
            }

            if ( ! defined( 'TIMEPADEVENTS_BASENAME' ) && defined( 'TIMEPADEVENTS_FILE' ) ) {
                /**
                 * Plugin basename (plugin relative path to main plugin file)
                 *
                 * @var     string
                 * @return  string Plugin basename (plugin relative path to main plugin file)
                 * @example my-plugin/my-plugin.php
                 */
                define( 'TIMEPADEVENTS_BASENAME', plugin_basename( TIMEPADEVENTS_FILE ) );
            }

            if ( ! defined( 'TIMEPADEVENTS_PLUGIN_HTTP_PATH' ) && defined( 'TIMEPADEVENTS_SITEURL' ) && defined( 'TIMEPADEVENTS_PLUGIN_ABS_PATH' ) ) {
                /**
                 * HTTP-path to plugin folder
                 *
                 * @var     string
                 * @return  string HTTP-path to plugin folder
                 * @example http://yoursite.com/wp-content/plugins/my-plugin/
                 */
                define( 'TIMEPADEVENTS_PLUGIN_HTTP_PATH', str_ireplace( ABSPATH, TIMEPADEVENTS_SITEURL . '/', TIMEPADEVENTS_PLUGIN_ABS_PATH ) );
            }

            if ( ! defined( 'TIMEPADEVENTS_SECURITY_NONCE' ) ) {
                /**
                 * Handler for security WordPress-based nonce
                 *
                 * @var string
                 * @return string Handler for security WordPress-based nonce
                 */
                define( 'TIMEPADEVENTS_SECURITY_NONCE', $config['security_nonce'] );
            }
        }
        
        /**
         * This function make remote request to TimePad API and return response as an array
         * 
         * @param string $url request URI
         * @param string $method request method, default is 'get'
         * @access protected
         * @return array Results of request
         */
        protected function _get_request_array( $url, $method = 'get' ) {
            $ret_array = array();
            $request = $method == 'get' ? wp_remote_get( $url, $this->_request_args ) : wp_remote_post( $url, $this->_request_args );
            if ( $request ) {
                $body = wp_remote_retrieve_body( $request );
                $ret_array = (array) json_decode( $body );
            }
            
            return $ret_array;
        }
        
        /**
         * This function adds to request body some params from $body
         * 
         * @param array $body params for request body
         * @access public
         */
        public function add_request_body( array $body ) {
            if ( !empty( $body ) && is_array( $body ) ) {
                $this->_request_args['body'] = $body;
            }
        }
        
        /**
         * This function removes request body
         * 
         * @access public
         */
        public function remove_request_body() {
            if ( isset( $this->_request_args['body'] ) ) {
                unset( $this->_request_args['body'] );
            }
        }

    }

endif;