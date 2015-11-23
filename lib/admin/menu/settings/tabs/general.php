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
        
        /**
         * Max Length of event name/title
         * 
         * @access protected
         * @var    int
         */
        protected $_title_maxlength = 250;
        
        /**
         * Max Length of event subdomain
         * 
         * @access protected
         * @var int
         */
        protected $_subdomain_maxlength = 25;

        public function __construct() {
            parent::__construct();
            
            //set the tab title
            $this->tab_title = __( 'General', 'timepad' );
        }
        
        /**
         * This function makes nice array with keys of [Organization ID] => Organization info at global key 'organizations'
         * 
         * @since  1.0.0
         * @param  array $organizations Organizations native array from TimePad response
         * @access private
         * @return array Organizations array with meta info plus key 'organizations' with keys of organization ID
         */
        private function _make_organizations_array( array $organizations, $single = false ) {
            if ( !empty( $organizations ) && is_array( $organizations ) ) {
                $ret_array = array();
                if ( !$single ) {
                    foreach ( $organizations['organizations'] as $organization ) {
                        $ret_array[$organization['id']] = $organization;
                    }
                } else {
                    $ret_array[$organizations['organizations']['id']] = $organizations['organizations'];
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
         * @since  1.0.0
         * @param  array $events Events array from TimePad API
         * @access private
         * @return array
         */
        private function _make_events_array( array $events ) {
            if ( !empty( $events ) && is_array( $events ) ) {
                $ret_array = array();
                foreach ( $events as $event ) {
                    $ret_array[$event['id']] = (array) $event;
                }
                
                return $ret_array;
            }
            
            return array();
        }
        
        /**
         * This function converts a given $date_array to pretty a date format to convert the one to time
         * 
         * @since  1.0.0
         * @param  array $date_array
         * @access private
         * @return string
         */
        private function _make_time_format( array $date_array ) {
            $months  = ( $date_array['month'] < 10 )  ? '0' . $date_array['month']  : $date_array['month'];
            $days    = ( $date_array['day'] < 10 )    ? '0' . $date_array['day']    : $date_array['day'];
            $hours   = ( $date_array['hour'] < 10 )   ? '0' . $date_array['hour']   : $date_array['hour'];
            $minutes = ( $date_array['minute'] < 10 ) ? '0' . $date_array['minute'] : $date_array['minute'];
            $seconds = ( $date_array['second'] < 10 ) ? '0' . $date_array['second'] : $date_array['second'];
            return $date_array['year'] . '-' . $months . '-' . $days . ' ' . $hours . ':' . $minutes . ':' . $seconds;
        }

        /**
         * This function make a WordPress needle format for a post (TimePad event)
         * If event is past - its own post/event time in WPDB will be as this time
         * If event will be in the future - its own WPDB time will be as now time 
         * but with rand interval to make some time different to fix prev/next post navigation
         * 
         * @since  1.0.0
         * @param  string $time time string from TimePad API to be converted
         * @access private
         * @return array Array with two keys for WordPress database: date and date_gmt
         */
        private function _make_post_time( $time ) {
            $date_parse     = date_parse( $time );
            $format         = $this->_make_time_format( $date_parse );
            $strtime        = strtotime( $format );
            
            $gmt_format     = get_gmt_from_date( $format );
            if ( time() < $strtime ) {
                $rand       = mt_rand( 5, 300 );
                
                $strtime    = time() - $rand;
                $format     = date( 'Y-m-d H:i:s', $strtime );
                
                $gmt_format = get_gmt_from_date( $format );
            }
            
            return array(
                'date'      => $format
                ,'date_gmt' => $gmt_format
            );
        }
        
        /**
         * This function get post/posts by meta with interanal TimePad event ID
         * 
         * @since  1.0.0
         * @param  int $event_id Internal TimePad event ID
         * @param  boolean $single
         * @param  string $status
         * @param  int $organization_id
         * @access protected
         * @return object|array single post object or array of WP posts objects
         */
        protected function _get_posts_by_timepad_event_id( $event_id, $single = true, $status = 'publish', $organization_id = false ) {
            global $wpdb;

            $org_id = $organization_id ? $organization_id : $this->_data['current_organization_id'];
            $meta_array = array(
                'event_id'         => intval( $event_id )
                ,'organization_id' => intval( $org_id )
            );

            $meta_str    = serialize( $meta_array );
            $sql_prepare = "SELECT * FROM {$wpdb->posts} LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id WHERE {$wpdb->postmeta}.meta_value = %s";
            $posts = $wpdb->get_results( $wpdb->prepare( $sql_prepare, $meta_str ) );

            return ( $single && isset( $posts[0] ) ) ? $posts[0] : $posts;
        }
        
        /**
         * Set post thumbnail from TimePad API response
         * 
         * @since  1.1
         * @param  int $post_id
         * @param  array $timepad_data
         * @access protected
         * @return boolean
         */
        protected function _set_post_thumbnail( $post_id, $timepad_data ) {
            if ( isset( $timepad_data['poster_image']['uploadcare_url'] ) && !empty( $timepad_data['poster_image']['uploadcare_url'] ) ) {
                $new_thumb = false;
                $timepad_api_thumbnail = $timepad_data['poster_image']['uploadcare_url'];
                $timepad_api_thumbnail = !stripos( $timepad_api_thumbnail, 'http:' ) ? 'http:' . $timepad_api_thumbnail : $timepad_api_thumbnail;
                $thumb_data = TimepadEvents_Helpers::get_api_cover_data( $timepad_api_thumbnail );
                $post_thumb_id = get_post_thumbnail_id( $post_id );
                if ( $post_thumb_id ) {
                    $thumb_meta = wp_get_attachment_metadata( $post_thumb_id, true );
                    if ( isset( $thumb_meta['file'] ) ) {
                        $thumb_filename = TimepadEvents_Helpers::get_filename( $thumb_meta['file'] );
                        if ( $thumb_filename != $thumb_data['basename'] ) {
                            if ( wp_delete_attachment( $post_thumb_id ) ) {
                                @delete_post_meta( $post_id, '_thumbnail_id' );
                                $new_thumb = true;
                            }
                        }
                    }
                } else {
                    $new_thumb = true;
                }
                
                if ( $new_thumb ) {
                    if ( $file_arr = TimepadEvents_Helpers::copy_file_to_wp_dir( $timepad_api_thumbnail, $thumb_data ) ) {
                        $attachment = array(
                            'post_mime_type' => $file_arr['type']
                            ,'guid'          => $file_arr['url']
                            ,'post_parent'   => $post_id
                            ,'post_title'    => $timepad_data['name']
                            ,'post_content'  => wp_trim_words( $timepad_data['description_html'], 20 )
                        );
                        $id = wp_insert_attachment( $attachment, $file_arr['file'], $post_id );
                        if ( !is_wp_error( $id ) ) {
                            if ( wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file_arr['file'] ) ) ) {
                                if ( current_theme_supports( 'post-thumbnails' ) ) {
                                    return set_post_thumbnail( $post_id, $id );
                                }
                            }
                        }
                    }
                }
            }
            
            return false;
        }

        /**
         * This function insert custom post types from Timpad events array
         * 
         * @since  1.0.0
         * @param  array $events Events array from TimePad response
         * @access private
         * @return void
         */
        private function _make_posts_from_events( array $events ) {
            
            //if exist category and current organization - let work!
            if ( isset( $this->_data['category_id'] ) && !empty( $this->_data['category_id'] ) && isset( $this->_data['current_organization_id'] ) && !empty( $this->_data['current_organization_id'] ) ) {
                foreach ( $events as $event ) {
                    $meta_array = array(
                        'event_id'           => intval( $event['id'] )
                        ,'organization_id'   => intval( $this->_data['current_organization_id'] )
                    );
                    $content = ( ( isset( $event['description_html'] ) && !empty( $event['description_html'] ) ) ? $event['description_html'] . '<br />' : '' ) . '[timepadregistration eventid="' . $event['id'] . '"]';
                    $date = $this->_make_post_time( $event['starts_at'] );
                    $insert_args = array(
                        'post_title'         => sanitize_text_field( $event['name'] )
                        ,'post_content'      => $content
                        ,'post_status'       => 'publish'
                        ,'post_category'     => array( $this->_data['category_id'] )
                        ,'post_type'         => TIMEPADEVENTS_POST_TYPE
                        ,'post_date'         => $date['date']
                        ,'post_date_gmt'     => $date['date_gmt']
                        ,'post_modified'     => $date['date']
                        ,'post_modified_gmt' => $date['date_gmt']
                    );
                    
                    $check_post = $this->_get_posts_by_timepad_event_id( $event['id'] );
                    if ( empty( $check_post ) ) {
                        //if post not exists - insert new post
                        if ( $id = wp_insert_post( $insert_args ) ) {
                            update_post_meta( $id, 'timepad_meta', $meta_array );
                            $this->_set_post_thumbnail( $id, $event );
                            wp_set_post_terms( $id, array( $this->_data['category_id'] ), TIMEPADEVENTS_POST_TYPE . '_category', true );
                        }
                    } else {
                        $update_args = array(
                            'ID'           => $check_post->ID
                            ,'post_status' => 'publish'
                        );
                        wp_update_post( $update_args );
                        $this->_set_post_thumbnail( $check_post->ID, $event );
                    }
                }
            }
        }
        
        /**
         * This function makes unvisible events in WordPress as private
         * 
         * @since  1.0.0
         * @param  int $post_id
         * @access protected
         * @return wp_update_post function: id of updated post
         */
        protected function _make_wp_event_status( $post_id, $status ) {
            $update_args = array(
                'ID'           => $post_id
                ,'post_status' => $status
            );
            return wp_update_post( $update_args );
        }

        /**
         * This function make update for all exist events. Part of syncronize scope
         * 
         * @since  1.0.0
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
                    $content = $event['description_html'] . '<br />[timepadregistration eventid="' . $event['id'] . '"]';
                    $date = $this->_make_post_time( $event['starts_at'] );
                    $update_args = array(
                        'ID'                 => $event_post->ID
                        ,'post_title'        => sanitize_text_field( $event['name'] )
                        ,'post_content'      => $content
                        ,'post_date'         => $date['date']
                        ,'post_date_gmt'     => $date['date_gmt']
                        ,'post_modified'     => $date['date']
                        ,'post_modified_gmt' => $date['date_gmt']
                    );
                    wp_update_post( $update_args );
                    
                    $this->_set_post_thumbnail( $event_post->ID, $event );
                }
            }
        }
        
        /**
         * This function checks possible subdomain errors
         * 
         * @since  1.0.0
         * @param  string $subdomain
         * @access protected
         * @return string Sanitized subdomain string
         */
        protected function _sanitize_new_organization( $subdomain ) {
            $subdomain = substr( sanitize_title( str_ireplace( '.' , '', trim( $subdomain, '-' ) ) ), 0, $this->_subdomain_maxlength );
            if ( is_numeric( $subdomain ) ) {
                $subdomain = 'ip' . $subdomain;
            }
            
            return $subdomain;
        }
        
        /**
         * This function adds new organization by Site name as Organization name and subdomain as TimePad subdomain
         * 
         * @since  1.0.0
         * @param  string $site_name Organization name
         * @param  string $subdomain Subdomain name
         * @access protected
         * @return array with keys 'organizations' with API response and 'errors_handle' width possible errors
         */
        protected function _add_new_organization( $site_name, $subdomain ) {
            $this->add_request_body( json_encode (
                array( 
                    'name'       => $site_name
                    ,'subdomain' => $subdomain
                    ,'phone'     => '0000000000'
                ) )
            );

            $organizations = array(
                'organizations' => $this->_get_request_array( $this->_config['create_organization_url'], 'post' )
            );
            $organizations = TimepadEvents_Helpers::object_to_array( $organizations );
            $errors_handle = $this->_errors_handle( $organizations['organizations'] );
            
            return array(
                'organizations'  => $organizations
                ,'errors_handle' => $errors_handle
            );
        }
        
        /**
         * This function handles possible errors at $response
         * 
         * @since  1.0.0
         * @param  array $response
         * @access protected
         * @return array|boolean If errors exists returns array, if all OK returns false
         */
        protected function _errors_handle( array $response ) {
            if ( isset( $response['response_status']['error_code'] ) && $response['response_status']['error_code'] === 422 ) {
                $message = '[' . $response['response_status']['error_code'] . '] ' . $response['response_status']['message'];
                if ( !empty( $response['response_status']['errors'] ) && is_array( $response['response_status']['errors'] ) ) {
                    $message .= ': ';
                    $not_unique = false;
                    $errors_message_array = array();
                    foreach ( $response['response_status']['errors'] as $error_array ) {
                        $errors_message_array[] = '[' . $error_array['field_name'] . ': ' . $error_array['message'] . ']';
                        if ( $error_array['field_name'] == 'subdomain' && $error_array['error_code'] == 'not_unique' ) {
                            $not_unique = true;
                        }
                    }
                    $message .= join( ', ', $errors_message_array );
                }
                
                return array(
                    'message'     => $message
                    ,'response'   => $response
                    ,'not_unique' => $not_unique
                );
            }
            
            return false;
        }

        /**
         * This function makes some prepare job before settings page loads
         * 
         * @since  1.0.0
         * @access private
         * @return void
         */
        private function _prepare() {
            /**
             * If we have token but no organizations, we make first request to get organizations list
             */
            if ( !empty( $this->_token ) ) {
                if ( !isset( $this->_data['organizations'] ) || empty( $this->_data['organizations']['organizations'] ) ) {
                    //request about getting organizations list
                    $organizations = $this->_get_request_array( $this->_config['organizer_request_url'] . '?token=' . $this->_token );
                    if ( isset( $organizations['organizations'] ) && !empty( $organizations['organizations'] ) ) {
                        $this->_data['organizations'] = $this->_make_organizations_array( $organizations );
                        
                        //If we have only one organization - let's make the one is default!
                        if ( count( $this->_data['organizations']['organizations'] ) == 1 ) {
                            $keys = array_keys( $this->_data['organizations']['organizations'] );
                            $this->_data['current_organization_id'] = intval( $keys[0] );
                            TimepadEvents_Helpers::update_option_key( $this->_config['optionkey'], $this->_data['current_organization_id'], 'current_organization_id' );
                        }
                    } else {
                        //if we hasn't yet organizations - make the one!
                        $site_name = sanitize_text_field( substr( get_bloginfo( 'name' ), 0, $this->_title_maxlength ) );
                        $subdomain = $this->_sanitize_new_organization( $_SERVER['HTTP_HOST'] );
                        $this->add_request_headers( array( 'Authorization' => 'Bearer ' . $this->_token ) );
                        $new_organization = $this->_add_new_organization( $site_name, $subdomain );
                        if ( isset( $new_organization['errors_handle'] ) && !empty( $new_organization['errors_handle'] ) ) {
                            if ( $new_organization['errors_handle']['not_unique'] === true ) {
                                $i = 2;
                                while ( $new_organization['errors_handle']['not_unique'] === true ) {
                                    $new_subdomain = $subdomain . $i;
                                    $new_organization = $this->_add_new_organization( $site_name, $new_subdomain );
                                    $i++;
                                    //just for security
                                    if ( $i == 30 ) {
                                        wp_die( $new_organization['errors_handle']['message'] );
                                        break;
                                    }
                                }
                            } else {
                                wp_die( $new_organization['errors_handle']['message'] );
                            }
                        }
                        
                        $this->_data['organizations'] = array_merge( $organizations, $this->_make_organizations_array( $new_organization['organizations'], true ) );
                        
                        if ( count( $this->_data['organizations']['organizations'] ) == 1 ) {
                            $keys = array_keys( $this->_data['organizations']['organizations'] );
                            $this->_data['current_organization_id'] = intval( $keys[0] );
                            TimepadEvents_Helpers::update_option_key( $this->_config['optionkey'], $this->_data['current_organization_id'], 'current_organization_id' );
                        }
                        $this->remove_request_headers( 'Authorization' );
                        $this->remove_request_body();
                        
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
         * @since  1.0.0
         * @param  array $events Events array from TimePad API
         * @access private
         * @return array
         */
        private function _prepare_events( array $events, $organization_id, $return_exists = false ) {
            if ( is_array( $events ) && !empty( $organization_id ) ) {
                $current_events_ids           = array();
                $ret_array                    = array();
                $ret_array_exists             = array();
                $ret_array_excluded           = array();
                $ret_array_exist_not_excluded = array();
                $exist_events                 = isset( $this->_data['events'][$organization_id] ) ? $this->_data['events'][$organization_id] : array();
                if ( !empty( $exist_events ) && is_array( $exist_events ) ) {
                    $ret_array_excluded       = @array_diff( $exist_events, $events );
                    
                    $current_events_ids       = array_keys( $exist_events );
                    if ( !empty( $events ) && !empty( $current_events_ids ) ) {
                        $excluded_from_api    = TimepadEvents_Helpers::get_excluded_from_api_events();
                        foreach ( $events as $event ) {
                            if ( !isset( $excluded_from_api[$event['id']] ) ) {
                                if ( !in_array( $event['id'], $current_events_ids ) ) {
                                    $ret_array[] = $event;
                                } else {
                                    $ret_array_exists[] = $event;
                                }
                            }
                        }
                    }
                    $ret_array_exist_not_excluded = @array_diff( $ret_array_exists, $ret_array_excluded );
                } else {
                    $ret_array = $events;
                }
                
                return !$return_exists ? $ret_array : array( 'new' => $ret_array, 'excluded' => $ret_array_excluded, 'exist' => $ret_array_exists, 'exist_not_excluded' => $ret_array_exist_not_excluded, 'all' => $events );
            }
            
            return array();
        }

        /**
         * This function insterts in WPDB events that need to be inserted 
         * for given organization: all of prepare job is given
         * 
         * @since  1.0.0
         * @param  type $organization_id ID of needle organization
         * @access public
         * @return array|boolean
         */
        public function post_events( $organization_id, $redirect_sub_str = '' ) {
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
            if ( isset( $events['values'] ) ) {
                $events = $this->_prepare_events( $events['values'], $organization_id, true );
                $this->_data['events'][$organization_id] = $this->_make_events_array( $events['all'] );
                if ( !empty( $events['exist'] ) && is_array( $events['exist'] ) ) {
                    $events_exist = $this->_make_events_array( $events['exist'] );
                    $this->_update_events_content( $events_exist );
                }
                if ( !empty( $events['excluded'] ) && is_array( $events['excluded'] ) ) {
                    foreach ( $events['excluded'] as $org_id => $excl_event ) {
                        $excluded_post = $this->_get_posts_by_timepad_event_id( $excl_event['id'] );
                        if ( !empty( $excluded_post ) ) {
                            $this->_make_wp_event_status( $excluded_post->ID, 'private' );
                        }
                    }
                }
                if ( !empty( $events['exist_not_excluded'] ) && is_array( $events['exist_not_excluded'] ) ) {
                    foreach ( $events['exist_not_excluded'] as $org_id => $not_excl_event ) {
                        $exist_not_excluded_post = $this->_get_posts_by_timepad_event_id( $not_excl_event['id'] );
                        if ( !empty( $exist_not_excluded_post ) ) {
                            $this->_make_wp_event_status( $exist_not_excluded_post->ID, 'publish' );
                        }
                    }
                }
                if ( !empty( $events['new'] ) && is_array( $events['new'] ) ) {
                    $events_new = $this->_make_events_array( $events['new'] );
                    $this->_make_posts_from_events( $events_new );
                } else {
                    return $events;
                }
            }
            
            return TimepadEvents_Helpers::update_option_key( $this->_config['optionkey'], isset( $this->_data['events'] ) ? $this->_data['events'] : array(), 'events' );
        }

        /**
         * This function make a display control for settings page
         * 
         * @since  1.0.0
         * @access public
         * @return void
         */
        public function display() {
            $this->_prepare();
            if ( $this->_data ) {
                if ( is_array( $this->_data ) && count( $this->_data ) == 1 && isset( $this->_data['category_id'] ) ) {
                    $config     = $this->_config;
                    include_once TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu/views/settings-general.php';
                } else {
                    $data       = $this->_data;
                    $category   = isset( $this->_data['category_id'] ) ? $this->_get_category( $this->_data['category_id'] ) : array();
                    include_once TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu/views/settings-general-data.php';
                }
            } else {
                $config         = $this->_config;
                $error_response = $this->_error_response;
                include_once TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/admin/menu/views/settings-general.php';
            }
        }
        
    }
    
endif;