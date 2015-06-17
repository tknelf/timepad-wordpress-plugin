<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TimepadEvents_Admin_Settings_General' ) ) :
    
    class TimepadEvents_Admin_Settings_General extends TimepadEvents_Admin_Settings_Base {
         
        /**
         * default limit for events response
         * 
         * @access protected
         * @var int
         */
        protected $_default_limit = 10;
        
        /**
         * moderation statues for events request
         * 
         * @access protected
         * @var string
         */
        protected $_moderation_statuses = 'hidden,not_moderated,shown,featured';
        
        /**
         * from date string for events request
         * 
         * @access protected
         * @var string
         */
        protected $_starts_at_min = '1970-01-01';
        
        /**
         * fields need to parse from TimePad API
         * 
         * @access protected
         * @var    string
         */
        protected $_fields = 'description_html';

        public function __construct() {
            parent::__construct();
            
            //set the tab title
            $this->tab_title = __( 'General', 'timepad' );
        }
        
        /**
         * This function makes nice array with keys of [Organization ID] => Organization info at global key 'organizations'
         * 
         * @param array $organizations Organizations native array from TimePad response
         * @access private
         * @return array Organizations array with meta info plus key 'organizations' with keys of organization ID
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
            
            return array();
        }
        
        /**
         * This function make a pretty key=>value array for events info
         * Every event item has an own key is an event id
         * 
         * @param array $events Events array from TimePad API
         * @access private
         * @return array
         */
        private function _make_events_array( array $events ) {
            if ( !empty( $events ) && is_array( $events ) ) {
                $ret_array = array();
                foreach ( $events as $event ) {
                    $ret_array[$event->id] = (array) $event;
                }
                
                return $ret_array;
            }
            
            return array();
        }
        
        /**
         * This function converts a given $date_array to pretty a date format to convert the one to time
         * 
         * @param array $date_array
         * @access private
         * @return string
         */
        private function _make_time_format( array $date_array ) {
            $months = ( $date_array['month'] < 10 ) ? '0' . $date_array['month'] : $date_array['month'];
            $days = ( $date_array['day'] < 10 ) ? '0' . $date_array['day'] : $date_array['day'];
            $hours = ( $date_array['hour'] < 10 ) ? '0' . $date_array['hour'] : $date_array['hour'];
            $minutes = ( $date_array['minute'] < 10 ) ? '0' . $date_array['minute'] : $date_array['minute'];
            $seconds = ( $date_array['second'] < 10 ) ? '0' . $date_array['second'] : $date_array['second'];
            return $date_array['year'] . '-' . $months . '-' . $days . ' ' . $hours . ':' . $minutes . ':' . $seconds;
        }

        /**
         * This function make a WordPress needle format for a post (TimePad event)
         * If event is past - its own post/event time in WPDB will be as this time
         * If event wiil be in the future - its own WPDB time will be as now time 
         * but with rand interval to make some time different to fix prev/next post navigation
         * 
         * @param string $time time string from TimePad API to be converted
         * @access private
         * @return array Array with two keys for WordPress database: date and date_gmt
         */
        private function _make_post_time( $time ) {
            $date_parse = date_parse( $time );
            $format = $this->_make_time_format( $date_parse );
            $strtime = strtotime( $format );
            
            $gmt_format = get_gmt_from_date( $format );
            if ( time() < $strtime ) {
                $rand = mt_rand( 5, 300 );
                
                $strtime = time() - $rand;
                $format = date( 'Y-m-d H:i:s', $strtime );
                
                $gmt_format = get_gmt_from_date( $format );
            }
            
            return array(
                'date'      => $format
                ,'date_gmt' => $gmt_format
            );
        }

        /**
         * This function insert custom post types from Timpad events array
         * 
         * @param  array $events Events array from TimePad response
         * @access private
         * @return void
         */
        private function _make_posts_from_events( array $events ) {
            
            //if exist category and current organization - let work!
            if ( isset( $this->_data['category_id'] ) && !empty( $this->_data['category_id'] ) && isset( $this->_data['current_organization_id'] ) && !empty( $this->_data['current_organization_id'] ) ) {
                foreach ( $events as $event ) {
                    $meta_array = array(
                        'event_id'         => intval( $event['id'] )
                        ,'organization_id' => intval( $this->_data['current_organization_id'] )
                    );
                    $content = ( ( isset( $event['description_html'] ) && !empty( $event['description_html'] ) ) ? $event['description_html'] . '<br />' : '' ) . '[timepadevent id="' . $event['id'] . '"]';
                    $date = $this->_make_post_time( $event['starts_at'] );
                    $insert_args = array(
                        'post_title'     => sanitize_text_field( $event['name'] )
                        ,'post_content'  => $content
                        ,'post_status'   => 'publish'
                        ,'post_category' => array( $this->_data['category_id'] )
                        ,'post_type'     => TIMEPADEVENTS_POST_TYPE
                        ,'post_date'     => $date['date']
                        ,'post_date_gmt' => $date['date_gmt']
                        ,'post_modified' => $date['date']
                        ,'post_modified_gmt' => $date['date_gmt']
                    );
                    
                    //check for exist post
                    $check_args = array(
                        'meta_key'        => 'timepad_meta'
                        ,'meta_value'     => $meta_array
                        ,'posts_per_page' => -1
                        ,'post_status'    => 'publish'
                    );
                    $check_posts = get_posts( $check_args );

                    if ( empty( $check_posts ) ) {
                        //if post not exists - insert new post
                        if ( $id = wp_insert_post( $insert_args ) ) {
                            update_post_meta( $id, 'timepad_meta', $meta_array );

                            wp_set_post_terms( $id, array( $this->_data['category_id'] ), TIMEPADEVENTS_POST_TYPE . '_category', true );
                        }
                    }
                }
            }
        }
        
        /**
         * This function make update for all exist events. Part of syncronize scope
         * 
         * @param  array $events Prepared array of exist events in WP DB
         * @access private
         * @return void
         */
        private function _update_events_content( array $events ) {
            global $wpdb;
            
            foreach ( $events as $event ) {
                $meta_array = array(
                    'event_id'         => intval( $event['id'] )
                    ,'organization_id' => intval( $this->_data['current_organization_id'] )
                );
                $sql = "SELECT * FROM `{$wpdb->posts}` LEFT JOIN `{$wpdb->postmeta}` ON `{$wpdb->posts}`.`ID` = `{$wpdb->postmeta}`.`post_id` WHERE 1=1 AND `{$wpdb->postmeta}`.`meta_value` LIKE '%s'";
                $event_post = $wpdb->get_row( $wpdb->prepare( $sql, serialize( $meta_array ) ) );
                if ( !empty( $event_post ) ) {
                    $content = $event['description_html'] . '<br />[timepadevent id="' . $event['id'] . '"]';
                    $date = $this->_make_post_time( $event['starts_at'] );
                    $update_args = array(
                        'ID'             => $event_post->ID
                        ,'post_title'    => sanitize_text_field( $event['name'] )
                        ,'post_content'  => $content
                        ,'post_date'     => $date['date']
                        ,'post_date_gmt' => $date['date_gmt']
                        ,'post_modified' => $date['date']
                        ,'post_modified_gmt' => $date['date_gmt']
                    );
                    wp_update_post( $update_args );
                }
            }
        }

        /**
         * This function makes some prepare job before settings page loads
         * 
         * @access private
         */
        private function _prepare() {
            /**
             * If we have token but no organizations, we make first request to get organizations list
             */
            if ( !empty( $this->_token ) ) {
                
                if ( !isset( $this->_data['organizations'] ) ) {
                    //request about getting organizations list
                    $organizations = $this->_get_request_array( $this->_config['organizer_request_url'] . '?token=' . $this->_token );

                    if ( $organizations ) {
                        $this->_data['organizations'] = $this->_make_organizations_array( $organizations );

                        //If we have only one organization - let's make the one is default!
                        if ( count( $this->_data['organizations']['organizations'] ) == 1 ) {
                            $keys = array_keys( $this->_data['organizations']['organizations'] );
                            $this->_data['current_organization_id'] = intval( $keys[0] );
                            TimepadEvents_Helpers::update_option_key( $this->_config['optionkey'], $this->_data['current_organization_id'], 'current_organization_id' );
                        }
                    } else {
                        //if we hasn't yet organizations - make the one!
                        $site_name = get_bloginfo( 'name' );
                        $this->add_request_body( 
                            array( 
                                'name'       => sanitize_text_field( $site_name )
                                ,'subdomain' => sanitize_title( str_ireplace( '.' , '', $_SERVER['HTTP_HOST'] ) )
                                ,'phone'     => '0000000000'
                            )
                        );
                        
                        $organizations = $this->_get_request_array( $this->_config['create_organization_url'], 'post' );
                        if ( $organizations ) {
                            $this->_data['organizations'] = $this->_make_organizations_array( $organizations );

                            //If we have only one organization - let's make the one is default!
                            if ( count( $this->_data['organizations']['organizations'] ) == 1 ) {
                                $keys = array_keys( $this->_data['organizations']['organizations'] );
                                $this->_data['current_organization_id'] = intval( $keys[0] );
                                TimepadEvents_Helpers::update_option_key( $this->_config['optionkey'], $this->_data['current_organization_id'], 'current_organization_id' );
                            }
                        }

                    }
                    
                    TimepadEvents_Helpers::update_option_key( $this->_config['optionkey'], $this->_data['organizations'], 'organizations' );
                }
                
                /**
                 * If we have token, have organizations list and current organization - let make posts for events
                 * First we need to do is create a new category for events
                 */
                if ( isset( $this->_data['organizations'] ) && !empty( $this->_data['organizations'] ) ) {
                    if ( isset( $this->_data['current_organization_id'] ) && !empty( $this->_data['current_organization_id'] ) ) {
                        if ( !isset( $this->_data['category_id'] ) || empty( $this->_data['category_id'] ) ) {
                            $cat_name     = __( 'TimePad Events', 'timepad' );
                            $cat_nicename = TIMEPADEVENTS_POST_TYPE_CATEGORY;
                            $cat_taxonomy = TIMEPADEVENTS_POST_TYPE . '_category';
                            $cat_i        = 1;
                            
                            /**
                             * Check for category exists, maybe user enter the one by himself...
                             */
                            if ( term_exists( $cat_name, $cat_taxonomy ) ) {
                                $category = get_term_by( 'name', $cat_name, $cat_taxonomy, ARRAY_A );
                                if ( isset( $category['term_id'] ) ) {
                                    $this->_data['category_id'] = intval( $category['term_id'] );
                                    TimepadEvents_Helpers::update_option_key( $this->_config['optionkey'], $this->_data['category_id'], 'category_id' );
                                    $this->post_events( $this->_data['current_organization_id'] );
                                }
                            } else {
                                while ( !term_exists( $cat_name, $cat_taxonomy ) ) {

                                    //insert new category for events
                                    if ( $category_id = wp_insert_category( array( 
                                        'cat_name'           => $cat_name
                                        ,'category_nicename' => $cat_nicename
                                        ,'taxonomy'          => $cat_taxonomy ) ) 
                                    ) {
                                        $this->_data['category_id'] = intval( $category_id );
                                        TimepadEvents_Helpers::update_option_key( $this->_config['optionkey'], $this->_data['category_id'], 'category_id' );
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
                            if ( isset( $_GET['syncronize'] ) && intval( $_GET['syncronize'] ) == 1 ) {
                                $this->post_events( $this->_data['current_organization_id'] );
                            }
                        }
                    }
                }
            }
        }
        
        /**
         * This function checks for exist posts/events in WPDB against given array $events
         * If some of array items are not exists in WPDB - it will be returns in result array of the function
         * 
         * @param  array $events Events array from TimePad API
         * @access private
         * @return array
         */
        private function _prepare_events( array $events, $organization_id, $return_exists = false ) {
            if ( !empty( $events ) && is_array( $events ) && !empty( $organization_id ) ) {
                $current_events_ids = array();
                $ret_array = array();
                $ret_array_exists = array();
                if ( !empty( $this->_data['events'][$organization_id] ) && is_array( $this->_data['events'][$organization_id] ) ) {
                    $current_events_ids = array_keys( $this->_data['events'][$organization_id] );
                    if ( !empty( $current_events_ids ) && is_array( $this->_data['events'][$organization_id] ) ) {
                        foreach ( $events as $event ) {
                            if ( !in_array( $event->id, $current_events_ids ) ) {
                                $ret_array[] = $event;
                            } else {
                                $ret_array_exists[] = $event;
                            }
                        }
                    }
                } else {
                    $ret_array = $events;
                }
                
                return !$return_exists ? $ret_array : array( 'new' => $ret_array, 'exist' => $ret_array_exists, 'all' => $events );
            }
            
            return array();
        }

        /**
         * This function insterts in WPDB events that need to be inserted 
         * for given organization: all of prepare job is given
         * 
         * @param type $organization_id ID of needle organization
         * @access public
         * @return array|boolean
         */
        public function post_events( $organization_id, $fullsynchronize = false ) {
            //get all events for current organization
            $query_args = array(
                'organization_ids'     => $organization_id
                ,'moderation_statuses' => $this->_moderation_statuses
                ,'starts_at_min'       => $this->_starts_at_min
                ,'fields'              => $this->_fields
                ,'limit'               => $this->_default_limit
            );
            $query_str = $this->_config['events_request_url'] . '?' . http_build_query( $query_args );
            $events = $this->_get_request_array( $query_str );
            if ( isset( $events['total'] ) ) {
                $events_count = intval( $events['total'] );
                if ( $events_count > $this->_default_limit ) {
                    
                    //make paging
                    $pages_count = ceil( $events_count / $this->_default_limit ) - 1;
                    for ( $i = 1; $i <= $pages_count; $i++ ) {
                        $offset_events = $this->_get_request_array( $query_str . '&skip=' . $i * $this->_default_limit );
                        array_push( $events['values'], $offset_events['values'] );
                    }
                }
            }
            if ( isset( $events['values'] ) && !empty( $events['values'] ) ) {
                $events = $this->_prepare_events( $events['values'], $organization_id, true );
                $this->_data['events'][$organization_id] = $this->_make_events_array( $events['all'] );
                if ( !empty( $events['exist'] ) && is_array( $events['exist'] ) ) {
                    $events_exist = $this->_make_events_array( $events['exist'] );
                    $this->_update_events_content( $events_exist );
                }
                if ( !empty( $events['new'] ) && is_array( $events['new'] ) ) {
                    $events_new = $this->_make_events_array( $events['new'] );
                    $this->_make_posts_from_events( $events_new );
                } else {
                    return $events;
                }
            }
            
            return TimepadEvents_Helpers::update_option_key( $this->_config['optionkey'], $this->_data['events'], 'events' );
        }

        /**
         * This function make a display control for settings page
         * 
         * @access public
         */
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