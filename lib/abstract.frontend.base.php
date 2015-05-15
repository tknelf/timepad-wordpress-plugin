<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Abstract Frontend Class
 *
 * Base abstract class for all frontend NetMolis classes
 *
 * @class       NetMolis_Frontend_Base
 * @version     1.0.0
 * @package     NetMolis/Frontend
 * @author      NetMolis Team
 * @category    Abstract Class
 */

if ( ! class_exists( 'TimepadEvents_Frontend_Base' ) ) :

    abstract class TimepadEvents_Frontend_Base extends TimepadEvents_Base {

        protected $_post = null;

        /**
         * All of NetMolis post type data, like atributes, price etc
         * Keys of this array are handlers of classes that has access to update
         * info about NetMolis items
         * For example, keys can be general, attachments, videos
         *
         * @access protected
         * @var array
         */
        protected $_item_data = array();

        /**
         * All of global NetMolis settings array
         *
         * @access protected
         * @var array
         */
        protected $_netmolis_settings = array();

        /**
         * All NetMolis post type data by handler of current item at frontend
         * For example, you need to get all of attachments of current NetMolis item
         * This variable stores all this data
         * NOTE: you can use this @var only at development classes, not at frontend
         * because you have handler only at development, so you no need to use it at frontend
         *
         * @access protected
         * @var array
         */
        protected $_item_data_handler = array();

        /**
         * This @var returns HTTP Path to current theme
         *
         * @example http://yoursite.com/wp-content/themes/twentyfourteen
         * @access protected
         * @var string
         */
        protected $_template_directory_uri;

        public function __construct() {

            
        }

    }

endif;