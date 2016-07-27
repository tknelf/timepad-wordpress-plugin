<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Main Setup Class for init plugin and admin
 *
 * Main Frontend class that prepares all TimepadEvents frontend
 *
 * @class       TimepadEvents_Setup_Admin
 * @version     1.1.5
 * @package     TimepadEvents/Admin
 * @author      TimepadEvents Team
 * @extends     TimepadEvents_Admin_Base
 * @category    Main Class / Admin Class
 */

if ( ! class_exists( 'TimepadEvents_Setup_Admin' ) ) :

    class TimepadEvents_Setup_Admin extends TimepadEvents_Admin_Base {

        /**
         * @var string Name of user meta of requirements show
         */
        protected static $_user_plugin_requirements_meta = 'timepadevents_requirements';

        public function __construct() {
            parent::__construct();

            //core init classes store
            $this->_classes = array(
                'TimepadEvents_Admin_i18n'       => TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/i18n.php'
                ,'TimepadEvents_Admin_Post_Type' => TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/post-type.php'

                ,'TimepadEvents_Admin_Settings'  => TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu/settings.php'

                ,'TimepadEvents_Admin_Menu'      => TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu.php'

                ,'TimepadEvents_Admin_Scripts'   => TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/scripts.php'
                ,'TimepadEvents_Admin_Styles'    => TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/styles.php'
            );

            if ( is_admin() ) {

                add_filter( 'display_post_states', function( $post_states, $post ) {
                    $post_meta = get_post_meta( $post->ID, TIMEPADEVENTS_META, true );
                    if ( !empty( $post_meta ) && $post->post_type != TIMEPADEVENTS_POST_TYPE ) {
                        $post_states['timepad_event'] = __( 'Event from TimePad', 'timepad' );
                    }

                    return $post_states;
                }, 999, 2 );

                add_action( 'wp_ajax_timepad_dismiss_requirements', array( $this, 'timepadevents_dismiss_requirements' ) );

                add_action( 'wp_ajax_timepad_unbind_from_api', array( $this, 'unsyncronize_event_to_post_ajax' ) );

                add_action( 'wp_ajax_timepad_get_post_type_categories', array( $this, 'timepadevents_get_post_type_categories' ) );

                add_action( 'delete_post', function( $post_id ) {
                    TimepadEvents_Helpers::get_excluded_from_api_events( false, $post_id, 'delete' );
                } );
            }

            //init core hooks
            register_activation_hook(   TIMEPADEVENTS_BASENAME, array(  $this,    'timepadevents_register_activation_hook' ) );
            register_deactivation_hook( TIMEPADEVENTS_BASENAME, array( __CLASS__, 'timepadevents_plugin_deactivate' ) );
            register_uninstall_hook(    TIMEPADEVENTS_BASENAME, array( __CLASS__, 'timepadevents_plugin_uninstall' ) );

            //init
            foreach( $this->_classes as $class_name => $path ) {
                if ( ! class_exists( $class_name ) && file_exists( $path ) ) {
                    require_once( $path );
                }
                if ( class_exists( $class_name ) ) {
                    if ( $this->check_class( $class_name ) ) {
                        $instance = $class_name::getInstance();
                        if ( method_exists( $class_name, 'init' ) && is_callable( $class_name, 'init' ) ) {
                            $instance->init();
                        } else {
                            if ( has_action( 'timepadevents_admin_init_' . $instance->handler ) ) {
                                do_action( 'timepadevents_admin_init_' . $instance->handler );
                            }
                            if ( has_action( 'timepadevents_frontend_init_' . $instance->handler ) ) {
                                do_action( 'timepadevents_frontend_init_' . $instance->handler );
                            }
                        }
                    }
                }
            }

            //init TimePadEvents cron functionality
            $data = $this->_data;
            add_action( 'timepad_cron', function() use ( $data ) {
                if ( $data['autoimport'] ) {
                    require_once TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu/settings/tabs/general.php';
                    TimepadEvents_Admin_Settings_General::getInstance()->post_events( $data['current_organization_id'] );
                }
            } );

            add_action( 'admin_bar_menu', function( $wp_admin_bar ) {
                $wp_admin_bar->add_menu(
                    array(
                        'parent' => 'appearance'
                        ,'id'    => 'timepad'
                        ,'title' => __( 'TimePad Settings', 'timepad' )
                        ,'href'  => TIMEPADEVENTS_SETTINGS_HTTP_URL
                    )
                );
            }, 999 );

            if ( $message = $this->_requirements_messages() ) :
                if ( $this->_is_plugin_admin_page() ) :
                    if ( get_user_meta( $this->_current_user_id, self::$_user_plugin_requirements_meta, true ) == 1 ) :
                        $tmp_this = $this;
                        add_action( 'admin_notices', function() use ( $tmp_this, $message ) {
                            $tmp_this->timepadevents_admin_requirements_notice( $message );
                        } );
                    endif;
                endif;
            endif;

        }

        /**
         * Register plugin activation hook
         *
         * @since  1.0.0
         * @access public
         * @return void
         */
        public function timepadevents_register_activation_hook() {
            if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }

            self::_timepadevents_set_admin_requirements_meta();
        }

        /**
         * Register plugin deactivation hook
         *
         * @since  1.0.0
         * @access public
         * @return void
         */
        public static function timepadevents_plugin_deactivate() {
            if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }

            $timestamp = wp_next_scheduled( 'timepad_cron' );
            wp_unschedule_event( $timestamp, 'timepad_cron' );

            self::_timepadevents_set_admin_requirements_meta( 'update' );
        }

        /**
         * Uninstall plugin hook
         *
         * @since  1.0.0
         * @access public
         * @return void
         */
        public static function timepadevents_plugin_uninstall() {
            if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }

            $timepad_posts = get_posts(array(
                'post_type'       => TIMEPADEVENTS_POST_TYPE
                ,'posts_per_page' => -1
            ));
            if ( !empty( $timepad_posts ) && is_array( $timepad_posts ) ) {
                foreach ( $timepad_posts as $timepad_post ) {
                    wp_delete_post( $timepad_post->ID );
                }
            }

            $timepad_terms = get_terms( TIMEPADEVENTS_POST_TYPE . '_category', array( 'hide_empty' => false ) );
            if ( !empty( $timepad_terms ) && is_array( $timepad_terms ) ) {
                foreach ( $timepad_terms as $timepad_term ) {
                    wp_delete_term( $timepad_term->term_id, TIMEPADEVENTS_POST_TYPE . '_category' );
                }
            }

            delete_option( 'timepad_data' );
            delete_option( 'timepad_flushed' );
            delete_option( 'timepad_excluded_from_api' );

            setcookie( 'timepad_token', null, -1, '/' );

            wp_clear_scheduled_hook( 'timepad_cron' );

            self::_timepadevents_set_admin_requirements_meta( 'delete' );
        }

        /**
         * This function add/update/delete user meta about show requirements
         *
         * @since  1.1
         * @access protected
         * @param  string $action add/update/delete action of user meta
         * @param  int    $meta_value Meta value
         * @return boolean|void
         */
        protected static function _timepadevents_set_admin_requirements_meta( $action = 'add', $meta_value = 1 ) {
            $users = get_users( array( 'role' => 'administrator' ) );
            if ( !empty( $users ) && is_array( $users ) ) {
                foreach ( $users as $user ) {
                    switch ( $action ) {
                        case 'add':
                            add_user_meta( $user->ID, self::$_user_plugin_requirements_meta, $meta_value, true );
                            break;
                        case 'update':
                            update_user_meta( $user->ID, self::$_user_plugin_requirements_meta, $meta_value );
                            break;
                        case 'delete':
                            delete_user_meta( $user->ID, self::$_user_plugin_requirements_meta );
                            break;
                    }
                }
            }

            return false;
        }

        /**
         * The function returns output of the div with notice message
         *
         * @since  1.1
         * @access public
         * @param  string $message Notice message to show
         * @return void
         */
        public function timepadevents_admin_requirements_notice( $message = '' ) {
            if ( $message ) :?>
                <div class="timepad-events-notice timepad-events-notice-requirements error">
                    <p><?php echo $message; ?></p>
                    <a href="javascript:;" class="timepad-events-dismiss dismiss-requirements"></a>
                </div>
            <?php endif;
        }

        /**
         * AJAX handler function of dismiss user requirements notice
         *
         * @since  1.1
         * @access public
         * @return void
         */
        public function timepadevents_dismiss_requirements() {
            check_ajax_referer( TIMEPADEVENTS_SECURITY_NONCE, 'security' );

            if ( @delete_user_meta( $this->_current_user_id, self::$_user_plugin_requirements_meta ) ) {
                wp_die(1);
            } else {
                wp_die(0);
            }
        }

        /**
         * AJAX get categories by given post type
         *
         * @since  1.1
         * @access public
         * @return void
         */
        public function timepadevents_get_post_type_categories() {
            check_ajax_referer( TIMEPADEVENTS_SECURITY_NONCE, 'security' );

            $ret_array = array();
            $post_type = sanitize_text_field( $_POST['post_type'] );
            $categories = get_categories(
                array(
                    'hide_empty' => false
                    ,'type'      => $post_type
                )
            );
            $ret_array['res'] = '';
            if ( $categories ) {
                $ret_array['res'] .= '<select name="timepad_autounsync_to_post_category" id="timepad_autounsync_to_post_category">';
                foreach ( $categories as $category ) {
                    $cat_id = intval( $category->term_id );
                    if ( $category->slug != TIMEPADEVENTS_POST_TYPE_CATEGORY ) {
                        $ret_array['res'] .= '<option value="' . $cat_id . '">' . $category->name . '</option>';
                    }
                }
                $ret_array['res'] .= '</select>';
                wp_die( json_encode( $ret_array ) );
            }
            wp_die(0);
        }

    }

endif;
