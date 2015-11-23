<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! defined( 'TIMEPADEVENTS_FILE' ) ) exit;

if ( ! class_exists( 'TimepadEvents_Helpers' ) ) :
    
    /**
     * TimepadEvents_Helpers Class
     */
    class TimepadEvents_Helpers {
        
        public static function get_file_extension($path) {
            return strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
        }
        
        /**
         * Function to get extension from mime-type of file path
         * 
         * @since 1.0.0
         * @param  string $path
         * @access public
         * @return string File extension from the $path
         */
        public static function get_file_extension_mime_by_path( $path ) {
            $ext = '';
            $mime = '';
            if ( $path ) {
                $image_info = getimagesize( $path );
                if ( $image_info['mime'] ) {
                    $mime = $image_info['mime'];
                    switch ( $image_info['mime'] ) {
                        case 'image/jpeg':
                            $ext = 'jpg';
                        case 'image/png':
                            $ext = 'png';
                        case 'image/gif':
                            $ext = 'gif';
                    }
                }
            }
            
            if ( !empty( $ext ) && !empty( $mime ) ) {
                return array(
                    'ext'   => $ext
                    ,'mime' => $mime
                );
            }
            
            return array();
        }

        public static function hash() {
            return substr( md5( microtime() . mt_rand( 10000, 9999999999 ) ), 3, 10 );
        }
        
        public static function allowed_option( $option ) {
            return stristr( $option, 'timepad' );
        }

        public static function get_option( $option_name, $key1 = false, $key2 = false ) {
            if ( self::allowed_option( $option_name ) ) {
                $option = get_option($option_name, array());
                if ( !empty( $option ) && is_array( $option ) ) {
                    if ( !empty( $key1 ) ) {
                        if ( !empty( $key2 ) ) {
                            $option = isset( $option[$key1][$key2] ) ? $option[$key1][$key2] : $option;
                        } else {
                            $option = isset( $option[$key1] ) ? $option[$key1] : $option;
                        }
                    }
                }
                return $option;
            }

            return false;
        }
        
        public static function update_option_key( $option_name, $value, $key1 = false, $key2 = false ) {
            if ( self::allowed_option( $option_name ) ) {
                $option = get_option( $option_name, array() );
                if ( !empty( $key1 ) ) {
                    if ( !empty( $key2 ) ) {
                        $option[$key1][$key2] = $value;
                    } else {
                        $option[$key1] = $value;
                    }
                } else {
                    $option = $value;
                }
                
                return @update_option( $option_name, $option );
            }
            
            return false;
        }
        
        public static function object_to_array( $obj ) {
            if ( is_object( $obj ) ) {
                $obj = (array) $obj;
            }
            
            if ( is_array( $obj ) ) {
                $new = array();
                foreach( $obj as $key => $val ) {
                    $new[$key] = self::object_to_array( $val );
                }
            } else {
                $new = $obj;
            }
            
            return $new;
        }

        public static function update_option( $option_name, $value ) {
            return self::allowed_option( $option_name ) ? @update_option( $option_name, $value ) : false;
        }
        
        public static function delete_option_key( $option_name, $key1, $key2 = false ) {
            if ( self::allowed_option( $option_name ) ) {
                $option = get_option( $option_name, array() );
                if ( !empty( $key1 ) ) {
                    if ( !empty( $key2 ) ) {
                        if ( isset( $option[$key1][$key2] ) ) unset( $option[$key1][$key2] );
                    } else {
                        if ( isset( $option[$key1] ) ) unset( $option[$key1] );
                    }
                    
                    self::update_option( $option_name, $option );
                }
            }
            
            return false;
        }

        public static function delete_option( $option_name ) {
            return self::allowed_option( $option_name ) ? delete_option( $option_name ) : false;
        }


        /**
         * This function get TimePadEvents class handler by $classname
         * 
         * @since  1.0.0
         * @param  string $classname Needle TimePadEvents class name
         * @access public
         * @return string Handler of needle class
         */
        public static function get_class_handler( $classname ) {
            $arr = explode( '_', $classname );
            return strtolower( $arr[ count( $arr ) - 1 ] );
        }
        
        public static function get_organization_info_by_id( $id, array $array ) {
            foreach ( $array as $k => $v ) {
                if ( $id == $v->id ) {
                    return $array[$k];
                }
            }
            
            return false;
        }
        
        /**
         * This function get sanitized filename without extension
         * @example /public_html/wp-content/uploads/2015/11/file.jpg -> file
         * 
         * @since  1.1
         * @param  string $path Any path to a file
         * @access public
         * @return string Filename without extension
         */
        public static function get_filename( $path ) {
            $basename       = wp_basename( $path );
            $basename_array = explode( '.', $basename );
            unset( $basename_array[count( $basename_array ) - 1] );
            
            return join( '.', $basename_array );
        }
        
        /**
         * Get data from the TimePad API cover image path
         * 
         * @since  1.1
         * @param  string $timepad_api_link
         * @access public
         * @return array Array of file data basename => filename without ext, ext => extension, mime => file mime-type
         */
        public static function get_api_cover_data( $timepad_api_link, $http = false ) {
            $path_arr = explode( '/', $timepad_api_link );
            if ( !empty( $path_arr ) && is_array( $path_arr ) && isset( $path_arr[3] ) ) {
                if ( !$http ) {
                    $ext_mime     = self::get_file_extension_mime_by_path( $timepad_api_link );
                    if ( !empty( $ext_mime['ext'] ) && !empty( $ext_mime['mime'] ) ) {
                        return array(
                            'basename' => $path_arr[3]
                            ,'ext'     => $ext_mime['ext']
                            ,'mime'    => $ext_mime['mime']
                        );
                    }
                } else {
                    return array(
                        'basename' => $path_arr[3]
                    );
                }
            }
            
            return array();
        }
        
        /**
         * The function get TimePad API event banner link 
         * and copy the one to WordPress native upload folder
         * 
         * @since  1.1
         * @param  string $timepad_api_link
         * @access public
         * @return array
         */
        public static function copy_file_to_wp_dir( $timepad_api_link, $data ) {
            $filename      = $data['basename'] . '.' . $data['ext'];
            $wp_upload_dir = wp_upload_dir();
            $abs_path      = $wp_upload_dir['path'] . '/' . $filename;
            if ( copy( $timepad_api_link, $abs_path ) ) {
                return array(
                    'file'  => $abs_path
                    ,'url'  => $wp_upload_dir['url'] . '/' . $filename
                    ,'type' => $data['mime']
                );
            }
            
            return array();
        }
        
        /**
         * Get excluded from syncronize TimePad events by post ID or event ID or just an array
         * 
         * @since  1.1
         * @param  int $event_id TimePad event ID from syncronize to WordPress
         * @param  int $post_id WordPress post ID
         * @access public
         * @return boolean|array|int
         */
        public static function get_excluded_from_api_events( $event_id = false, $post_id = false, $action = false ) {
            $excluded_events = get_option( 'timepad_excluded_from_api' );
            if ( empty( $action ) ) {
                if ( !empty( $excluded_events ) && is_array( $excluded_events ) ) {
                    if ( empty( $post_id ) && empty( $event_id ) ) {
                        return $excluded_events;
                    } else {
                        if ( !empty( $post_id ) ) {
                            if ( !empty( $event_id ) ) {
                                return ( isset( $excluded_events[$event_id] ) && $excluded_events[$event_id] == $post_id );
                            } else {
                                return array_search( $post_id, $excluded_events );
                            }
                        } else {
                            if ( !empty( $event_id ) ) {
                                return isset( $excluded_events[$event_id] ) ? $excluded_events[$event_id] : false;
                            }
                        }
                    }
                }
            } else {
                switch ( $action ) {
                    case 'delete':
                        if ( !empty( $post_id ) ) {
                            $event_id = intval( array_search( $post_id, $excluded_events ) );
                            unset( $excluded_events[$event_id] );
                            return update_option( 'timepad_excluded_from_api', $excluded_events );
                        }
                        break;
                }
            }
            
            return false;
        }
    }
endif;