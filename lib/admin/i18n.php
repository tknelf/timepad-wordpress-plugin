<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TimepadEvents_Admin_i18n' ) ) :

    final class TimepadEvents_Admin_i18n extends TimepadEvents_Admin_Base {

        public function __construct() {
            parent::__construct();
        }

        public function init() {
            add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
        }

        public function load_plugin_textdomain() {

            $current_locale = get_locale();

            if ( !empty( $current_locale ) ) {
                load_plugin_textdomain( 'timepad', false, TIMEPADEVENTS_FOLDER . '/languages/' );
            }
        }
    }

endif;