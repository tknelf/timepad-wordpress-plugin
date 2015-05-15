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
         * This function get NetMolis class handler by $classname
         *
         * @param  string $classname Needle NetMolis class name
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
    }
endif;