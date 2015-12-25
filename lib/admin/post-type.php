<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'TimepadEvents_Admin_Post_Type' ) ) :
    
    class TimepadEvents_Admin_Post_Type extends TimepadEvents_Admin_Base {
    
        public function __construct() {
            parent::__construct();

            //add_action( 'init', array( $this, 'init_post_type' ) );
        }
        
        public function init() {
            add_action( 'init', array( $this, 'init_post_type' ) );
            
            $data = $this->_data;
            add_filter( 'post_row_actions', function( $actions, $post ) use ( $data ) {
                if ( $post->post_type == TIMEPADEVENTS_POST_TYPE ) {
                    $post_meta         = get_post_meta( $post->ID, TIMEPADEVENTS_META, true );
                    if ( !empty( $post_meta ) ) {
                        $event_id          = isset( $post_meta['event_id'] ) ? intval( $post_meta['event_id'] ) : 0;
                        $organization_id   = isset( $post_meta['organization_id'] ) ? intval( $post_meta['organization_id'] ) : 0;
                        unset( $actions['edit'], $actions['inline hide-if-no-js'] );
                        if ( isset( $data['current_organization_id'] ) && $organization_id == $data['current_organization_id'] ) {
                            unset( $actions['trash'] );
                        }
                        $organization_info = isset( $data['organizations']['organizations'][$organization_id] ) ? $data['organizations']['organizations'][$organization_id] : array();
                        if ( isset( $organization_info ) && !empty( $organization_info ) ) {
                            $actions = array_merge( $actions, 
                                array(
                                    'event_edit'                  => '<a href="' . esc_url( $organization_info['url'] ) . 'event/edit/' . $event_id . '/" target="_blank">' . __( 'Edit events at TimePad', 'timepad' ) . '</a>'
                                    ,'event_members'              => '<a href="' . esc_url( $organization_info['url'] ) . 'event/export/' . $event_id . '/html/" target="_blank">' . __( 'Members', 'timepad' ) . '</a>'
                                    ,'event_view'                 => '<a href="' . esc_url( $organization_info['url'] ) . 'event/' . $event_id . '/" target="_blank">' . __( 'View on TimePad', 'timepad' ) . '</a>'
                                    ,'trash timepad_event_unbind' => '<a href="javascript:;" data-postid="' . intval( $post->ID ) . '" data-eventid="' . $event_id . '" data-organizationid="' . intval( $organization_id ) . '">' . __( 'Unbind from API synzronize and make as post', 'timepad' ) . '</a>'
                                )
                            );
                        }
                    }
                    
                }
                return $actions;
            }, 0 ,2 );
            
            add_filter( 'get_edit_post_link', array( $this, 'timepadevents_edit_post_link' ), 0 ,3 );
            
            add_filter( 'bulk_actions-edit-' . TIMEPADEVENTS_POST_TYPE, function( $actions ) {
                unset( $actions['trash'] );
                
                return $actions;
            } );
        }
        
        public function timepadevents_edit_post_link( $url, $id, $text ) {
            $post_type = get_post_type( $id );
            if ( $post_type == TIMEPADEVENTS_POST_TYPE ) {
                $post_meta       = get_post_meta( $id, TIMEPADEVENTS_META, true );
                $organization_id = isset( $post_meta['organization_id'] ) ? intval( $post_meta['organization_id'] ) : 0;
                if ( isset( $this->_data['current_organization_id'] ) && $organization_id == $this->_data['current_organization_id'] ) {
                    $event_id          = isset( $post_meta['event_id'] ) ? intval( $post_meta['event_id'] ) : 0;
                    $organization_info = $this->_data['organizations']['organizations'][$organization_id];
                    $url               = esc_url( $organization_info['url'] ) . 'event/' . $event_id . '/';
                }
            }

            return $url;
        }

        private function _get_default_labels() {
            $defaults = array(
                'singular' => __( 'Event',  'timepad' ),
                'plural'   => __( 'Events', 'timepad' )
            );
            return apply_filters( 'timepadevents_default_items_name', $defaults );
        }
        
        private function _get_label_singular( $lowercase = false ) {
            $defaults = $this->_get_default_labels();
            return ( $lowercase ) ? strtolower( $defaults['singular'] ) : $defaults['singular'];
        }
        
        private function _get_label_plural( $lowercase = false ) {
            $defaults = $this->_get_default_labels();
            return ( $lowercase ) ? strtolower( $defaults['plural'] ) : $defaults['plural'];
        }
        
        public function init_post_type() {
            /**
             * @link http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
             */
            $custom_post_type_labels =  apply_filters( 'timepadevents_labels', array(
                'name' 		     => __( 'Events',                 'timepad' ),
                'singular_name'      => '%1$s',
                'add_new'            => __( 'Add New',                'timepad' ),
                'add_new_item' 	     => __( 'Add New %1$s',           'timepad' ),
                'edit_item' 	     => __( 'Edit %1$s',              'timepad' ),
                'new_item' 	     => __( 'New %1$s',               'timepad' ),
                'all_items' 	     => __( 'All %2$s',               'timepad' ),
                'view_item' 	     => __( 'View %1$s',              'timepad' ),
                'search_items' 	     => __( 'Search %2$s',            'timepad' ),
                'not_found' 	     => __( 'No %2$s found',          'timepad' ),
                'not_found_in_trash' => __( 'No %2$s found in Trash', 'timepad' ),
                'parent_item_colon'  => '',
                'menu_name' 	     => __( 'Events',                 'timepad' )
            ) );
            /**
             * @todo может и не надо так исхитряться, сделал на всякий случай, надо будет проверить
             */
            $custom_post_type_labels_tmp = array();
            foreach ( $custom_post_type_labels as $key => $value ) {
                $custom_post_type_labels_tmp[ $key ] = sprintf( $value,
                    $this->_get_label_singular(),
                    $this->_get_label_plural() );
            }
            $custom_post_type_labels = $custom_post_type_labels_tmp;
            $custom_post_type_args   = array(
                'labels'             => $custom_post_type_labels,
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu' 	     => true,
                //'query_var' 	     => true,
                'rewrite'            => array(
                    'slug'           => TIMEPADEVENTS_POST_TYPE,
                    'with_front'     => true
                ),
                'capability_type'    => 'post',
                /*'capabilities' => array(
                    'create_posts' => false
                ),*/
                //'map_meta_cap'       => true,
                'has_archive' 	     => true,
                'hierarchical' 	     => false,
                'supports'           => apply_filters(
                    'timepadevents_item_supports',
                    array( 'title', 'editor', 'excerpt' )
                )
            );
            register_post_type( TIMEPADEVENTS_POST_TYPE, apply_filters(
                'timepadevents_item_post_type_args', $custom_post_type_args
            ) );
            
            
            $custom_post_type_category_labels = array(
                'name'              => sprintf( _x( '%s Categories', 'taxonomy general name', 'timepad' ), $this->_get_label_singular() ),
                'singular_name'     => _x( 'Category', 'taxonomy singular name',              'timepad' ),
                'search_items' 	    => __( 'Search Categories',                               'timepad' ),
                'all_items' 	    => __( 'All Categories',                                  'timepad' ),
                'parent_item' 	    => __( 'Parent Category',                                 'timepad' ),
                'parent_item_colon' => __( 'Parent Category:',                                'timepad' ),
                'edit_item' 	    => __( 'Edit Category',                                   'timepad' ),
                'update_item' 	    => __( 'Update Category',                                 'timepad' ),
                'add_new_item'      => __( 'Add New Category',                                'timepad' ),
                'new_item_name'     => __( 'New Category Name',                               'timepad' ),
                'menu_name' 	    => __( 'Categories',                                      'timepad' )
            );
            $custom_post_type_category_args = apply_filters( 'timepadevents_item_category_args', array(
                //'hierarchical' => true,
                'labels'            => apply_filters( 'timepadevents_item_category_labels', $custom_post_type_category_labels )
                ,'show_ui'          => true,
                'query_var'         => 'timepadevents_category'
                ,'rewrite'          => array(
                    'slug'          => TIMEPADEVENTS_POST_TYPE_CATEGORY
                    ,'with_front'   => false
                    ,'hierarchical' => true
                )
            ));
            register_taxonomy( TIMEPADEVENTS_POST_TYPE . '_category',  TIMEPADEVENTS_POST_TYPE, $custom_post_type_category_args );
            
            $is_flush = get_option( 'timepad_flushed', '' );
            if ( empty( $is_flush ) ) {
                flush_rewrite_rules();
                update_option( 'timepad_flushed', 1 );
            }
        }
    }
endif;