<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TimepadEvents_Admin_Settings_General' ) ) :
    
    class TimepadEvents_Admin_Settings_General extends TimepadEvents_Admin_Settings_Base {

        public function __construct() {
            parent::__construct();
            
            $this->tab_title = __( 'General', TIMEPADEVENTS_DOMAIN );
            $this->action    = TIMEPADEVENTS_PLUGIN_HTTP_PATH . 'lib/admin/menu/settings/save.php';

            add_action( 'timepad_cron', function() {
                if ( $this->_data['autoimport'] ) {
                    $this->post_events($this->_data['current_organization_id']);
                }
            } );
        }
        
        /**
         * This function makes nice array with keys of [Organization ID] => Organization info at global key 'organizations'
         * 
         * @param array $organizations Organizations native array from TimePad response
         * @return type array Organizations array with meta info plus key 'organizations' with keys of organization ID
         */
        private function _make_organizations_array( array $organizations ) {
            if ( !empty( $organizations ) && is_array( $organizations ) ) {
                $ret_array = array();
                foreach ( $organizations['organizations'] as $organization ) {
                    $ret_array[$organization->id] = (array) $organization;
                }
                $organizations['organizations'] = $ret_array;
                
                return $organizations;
            }
            
            return false;
        }
        
        private function _make_events_array( array $events ) {
            if ( !empty( $events ) && is_array( $events ) ) {
                $ret_array = array();
                foreach ( $events as $event ) {
                    $ret_array[$event->id] = (array) $event;
                }
                
                return $ret_array;
            }
            
            return false;
        }

        private function _make_time_format( array $date_array ) {
            return $date_array['year'] . '-' . $date_array['month'] . '-' . $date_array['day'] . ' ' . $date_array['hour'] . ':' . $date_array['minute'] . ':' . $date_array['second'];
        }


        private function _make_post_time( $time ) {
            $date_parse = date_parse( $time );
            $format = $this->_make_time_format( $date_parse );
            $strtime = strtotime( $format );
            
            $gmt_time = $strtime + $date_parse['zone'] * 60;
            $gmt_date_parse = date_parse( $gmt_time );
            $gmt_format = $this->_make_time_format( $gmt_date_parse );
            if ( time() < $strtime ) {
                $rand = mt_rand( 5, 300 );
                
                $strtime = time() - $rand;
                $format = date( 'Y-m-d H:i:s', $strtime );
                
                $gmt_time = $strtime + $date_parse['zone'] * 60 - $rand;
                $gmt_format = date( 'Y-m-d H:i:s', $gmt_time );
            }
            
            return array(
                'date'      => $format
                ,'date_gmt' => $gmt_format
            );
        }

        /**
         * This function insert custom post types from Timpad events array
         * @param array $events Events array from TimePad response
         * @return void
         */
        private function _make_posts_from_events( array $events ) {
            
            //if exist category and current organization - let work!
            if ( isset( $this->_data['category_id'] ) && !empty( $this->_data['category_id'] ) && isset( $this->_data['current_organization_id'] ) && !empty( $this->_data['current_organization_id'] ) ) {
                foreach ( $events as $event ) {
                    $content = $event['description_html'] . '<br />[timepadevent id="' . $event['id'] . '"]';
                    $date = $this->_make_post_time( $event['starts_at'] );
                    $insert_args = array(
                        'post_title'     => sanitize_text_field( $event['name'] )
                        ,'post_content'  => $content
                        ,'post_status'   => 'publish'
                        ,'post_category' => array( $this->_data['category_id'] )
                        ,'post_type'     => parent::$post_type
                        ,'post_date'     => $date['date']
                        ,'post_date_gmt' => $date['date_gmt']
                        ,'post_modified' => $date['date']
                        ,'post_modified_gmt' => $date['date_gmt']
                    );
                    
                    //check for exist post
                    $check_args = array(
                        'meta_key'        => 'timepad_meta'
                        ,'meta_value'     => intval( $event['id'] )
                        ,'posts_per_page' => -1
                        ,'post_status'    => 'publish'
                    );
                    $check_posts = get_posts( $check_args );

                    if ( empty( $check_posts ) ) {
                        //if post not exists - insert new post
                        if ( $id = wp_insert_post( $insert_args ) ) {
                            $meta_array = array(
                                'event_id'         => intval( $event['id'] )
                                ,'organization_id' => intval( $this->_data['current_organization_id'] )
                            );
                            update_post_meta( $id, 'timepad_meta', $meta_array );

                            wp_set_post_terms( $id, array( $this->_data['category_id'] ), parent::$post_type . '_category', true );
                        }
                    }
                }
            }
        }

        private function _prepare() {
            /**
             * If we have token but no organizations, we make first request to get organizations list
             */
            if ( !empty( $this->_token ) ) {
                
                if ( isset( $_GET['syncronize'] ) && $_GET['syncronize'] == 1 && !empty( $this->_data['current_organization_id'] ) ) {
                    $this->post_events( $this->_data['current_organization_id'] );
                }
                
                if ( !isset( $this->_data['organizations'] ) ) {
                    //request about getting organizations list
                    $organizations = $this->_get_request_array( $this->_config['organizer_request_url'] . '?token=' . $this->_token );

                    if ( $organizations ) {
                        //if we already has some organizations - save the ones to options ( $this->_data variable )
                        $this->_data['organizations'] = $organizations = $this->_make_organizations_array( $organizations );

                        //If we have only one organization - let's make the one is default!
                        if ( count( $organizations['organizations'] ) == 1 ) {
                            $keys = array_keys( $organizations['organizations'] );
                            $this->_data['current_organization_id'] = $keys[0];
                            TimepadEvents_Helpers::update_option_key( 'timepad_data', intval( $keys[0] ), 'current_organization_id' );
                        }
                    } else {
                        //if we hasn't yet organizations - make the one!
                        $site_name = get_bloginfo( 'name' );
                        $this->add_request_body( 
                                array( 
                                    'name'      => sanitize_text_field( $site_name ), 
                                    'subdomain' => sanitize_title( str_ireplace( '.' , '', $_SERVER['HTTP_HOST'] ) ),
                                    'phone'     => '0000000000' 
                                )
                        );
                        //@tocheck
                        $this->_get_request_array( $this->_config['create_organization_url'], 'post' );

                    }
                    
                    TimepadEvents_Helpers::update_option_key( 'timepad_data', $organizations, 'organizations' );
                }
                
                /**
                 * If we have token, have organizations list and current organization - let make posts for events
                 * First we need to do is create a new category for events
                 */
                if ( isset( $this->_data['organizations'] ) && !empty( $this->_data['organizations'] ) ) {
                    if ( isset( $this->_data['current_organization_id'] ) && !empty( $this->_data['current_organization_id'] ) ) {
                        if ( !isset( $this->_data['category_id'] ) || empty( $this->_data['category_id'] ) ) {
                            $cat_name     = __( 'TimePad Events', 'timepad' );
                            $cat_nicename = 'timepad-events';
                            $cat_taxonomy = parent::$post_type . '_category';
                            $cat_i        = 1;
                            
                            /**
                             * Check for category exists, maybe user enter the one by himself...
                             */
                            if ( term_exists( $cat_name, $cat_taxonomy ) ) {
                                $category = get_term_by( 'name', $cat_name, $cat_taxonomy, ARRAY_A );
                                if ( isset( $category['term_id'] ) ) {
                                    $this->_data['category_id'] = intval( $category['term_id'] );
                                    TimepadEvents_Helpers::update_option_key( 'timepad_data', $this->_data['category_id'], 'category_id' );
                                    $this->post_events( $this->_data['current_organization_id'] );
                                }
                            } else {
                                while ( !term_exists( $cat_name, $cat_taxonomy ) ) {

                                    //insert new category for events
                                    if ( $category_id = wp_insert_category( array( 
                                        'cat_name'          => $cat_name, 
                                        'category_nicename' => $cat_nicename, 
                                        'taxonomy'          => $cat_taxonomy ) ) 
                                    ) {
                                        $this->_data['category_id'] = intval( $category_id );
                                        TimepadEvents_Helpers::update_option_key( 'timepad_data', $this->_data['category_id'], 'category_id' );
                                        $this->post_events( $this->_data['current_organization_id'] );
                                    } else {
                                        //security for hacks with unlimited requests
                                        if ( $cat_i == 20 ) break;

                                        $cat_i++;
                                        $cat_name     += ' ' . $cat_i;
                                        $cat_taxonomy += ' ' . $cat_i;
                                    }
                                }
                            }
                        } else {
                            $this->post_events( $this->_data['current_organization_id'] );
                        }
                    }
                }
            }
        }
        
        private function _prepare_events( array $events ) {
            if ( !empty( $events ) && is_array( $events ) ) {
                $current_events_ids = array();
                $ret_array = array();
                if ( !empty( $this->_data['events'][$this->_data['current_organization_id']] ) && is_array( $this->_data['events'][$this->_data['current_organization_id']] ) ) {
                    $current_events_ids = array_keys( $this->_data['events'][$this->_data['current_organization_id']] );

                    if ( !empty( $current_events_ids ) && is_array( $this->_data['events'][$this->_data['current_organization_id']] ) ) {
                        foreach ( $events as $event ) {
                            if ( !in_array( $event->id, $current_events_ids ) ) {
                                $ret_array[] = $event;
                            }
                        }
                        
                        return $ret_array;
                    }
                } else {
                    return $events;
                }
            }
            
            return false;
        }


        public function post_events( $organization_id ) {
            //get all events for current organization
            //@todo - убрать starts at min
            $events = $this->_get_request_array( $this->_config['events_request_url'] . '?organization_ids=' . $organization_id . '&moderation_statuses=hidden,not_moderated,shown,featured&starts_at_min=1970-01-01' );
            if ( isset( $events['values'] ) && !empty( $events['values'] ) ) {
                $events = $this->_prepare_events( $events['values'] );
                if ( !empty( $events ) && is_array( $events ) ) {
                    $events = $this->_make_events_array( $events );
                    $this->_data['events'][$organization_id] = $events;
                    $this->_make_posts_from_events( $events );
                }
            }

            return TimepadEvents_Helpers::update_option_key( 'timepad_data', $events, 'events', $organization_id );
        }

        public function display() {
            $this->_prepare();
            if ( $this->_data ) {
                if (is_array( $this->_data ) && count( $this->_data ) == 1 && isset( $this->_data['category_id'] ) ) {
                    $config = $this->_config;
                    include_once TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu/views/settings-general.php';
                } else {
                    $data = $this->_data;
                    $category = isset( $this->_data['category_id'] ) ? $this->_get_category( $this->_data['category_id'] ) : array();
                    include_once TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu/views/settings-general-data.php';
                }
            } else {
                $config = $this->_config;
                $error_response = $this->_error_response;
                include_once TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu/views/settings-general.php';
            }
        }
        
    }
    
endif;