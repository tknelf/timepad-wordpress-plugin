<?php

/**
 * 
 * TimePadEvents is free software: you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published by 
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 * 
 * @wordpress-plugin
 * Plugin Name: TimepadEvents
 * Plugin URI:  http://timepad.ru
 * Description: This plugin allows to integrate and synchronize your TimePad events as custom post types in your WordPress site
 * Version:     1.0.0
 * Author:      Igor Sazonov (@tigusigalpa)
 * Author URI:  http://wpspb.org
 * License:     GPL-2.0+
 * Text Domain: timepad
 * Domain Path: /languages
 *
*
* You should have received a copy of the GNU General Public License
* along with Timepad Events. If not, see <http://www.gnu.org/licenses/>.
*
* @package  TimepadEvents
* @category Core
* @author   Igor Sazonov
* @version  1.0.0
*/

//security check
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Base constants define
 */
if ( ! defined ( 'TIMEPADEVENTS_FILE' ) ) {
    /**
     * The main file basename of the plugin
     * 
     * @var string
     * @return string plugin basename
     */
    define( 'TIMEPADEVENTS_FILE',  __FILE__ );
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

if ( ! defined ( 'TIMEPADEVENTS_POST_TYPE' ) ) {
    /**
     * Post type slug for TimePad Events plugin
     * 
     * @var string
     * @return string
     */
    define( 'TIMEPADEVENTS_POST_TYPE',  'timepad-events' );
}

if ( ! defined ( 'TIMEPADEVENTS_POST_TYPE_CATEGORY' ) ) {
    /**
     * Post type category slug for TimePad Events plugin
     *
     * @var string
     * @return string
     */
    define( 'TIMEPADEVENTS_POST_TYPE_CATEGORY',  'timepad-events-category' );
}

if ( ! defined( 'TIMEPADEVENTS_PLUGIN_ABS_PATH' ) && defined( 'TIMEPADEVENTS_FILE' ) ) {
    /**
     * Path to plugin absolute path
     * 
     * @var string
     * @return string
     * @example /home/user/var/www/wordpress/wp-content/plugins/my-plugin/
     */
    define( 'TIMEPADEVENTS_PLUGIN_ABS_PATH', plugin_dir_path( TIMEPADEVENTS_FILE ) );
}

if ( ! defined( 'TIMEPADEVENTS_FOLDER' ) && defined( 'TIMEPADEVENTS_PLUGIN_ABS_PATH' ) ) {
    /**
     * Plugin folder name
     *
     * @var    string
     * @return string Plugin folder name
     */
    $arr = explode( '/', TIMEPADEVENTS_PLUGIN_ABS_PATH );
    define( 'TIMEPADEVENTS_FOLDER', $arr[count($arr) - 2] );
}

require_once( 'lib/helpers.php' );

/**
 * Base TimepadEvents Abstract Class
 */
require_once( TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/abstract.base.php' );
TimepadEvents_Base::define_constants();

/**
 * Adding new cron schedule hook to do cron once in three minutes
 */
add_filter( 'cron_schedules', function( $intervals ) {
    $intervals['once_three_mins'] = array(
        'interval' => HOUR_IN_SECONDS / 20
        ,'display' => __( 'Once in three minutes', 'timepad' )
    );
    
    return $intervals;
} );

/**
 * Base Admin Class
 */
require_once(TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/abstract.admin.base.php');

/**
 * Admin Settings Base class
 */
require_once(TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/abstract.admin.settings.base.php');

/**
 * Admin Setup File
 */
require_once(TIMEPADEVENTS_PLUGIN_ABS_PATH . 'lib/setup.admin.php');
add_action('plugins_loaded', array('TimepadEvents_Setup_Admin', 'getInstance'), 9999);

if ( ! is_admin() ) {
    
    /**
     * Hack to display events at general posts stock
     */
    add_filter( 'pre_get_posts', function( $query ) {
        if ( is_home() && $query->is_main_query() ) {
            $query->set( 'post_type', array( 'post', TIMEPADEVENTS_POST_TYPE ) );
        }

	return $query;
    } );
    
    /**
     * Enable new shortcode to display the one at site posts and pages
     */
    add_shortcode( 'timepadregistration' , function( array $atts ) {
        return "<script type=\"text/javascript\" defer=\"defer\" charset=\"UTF-8\" data-timepad-widget-v2=\"event_register\" src=\"https://timepad.ru/js/tpwf/loader/min/loader.js\">\n\t(function(){return {\"event\":{\"id\":\"" . $atts['id'] . "\"},\"bindEvents\":{\"preRoute\":\"TWFpreRouteHandler\"},\"isInEventPage\":true}; })();\n</script>";
    } );
    
}