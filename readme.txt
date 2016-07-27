=== TimePad Events ===
Contributors: tigusigalpa, hemantic_timepad
Tags: events, api, timepad
Requires at least: 4.0
Tested up to: 4.3
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

TimePad Events for WordPress is the easiest way to start selling tickets on your site using the full stack of TimePad technologies.

== Description ==

After installing the plugin and connecting your TimePad account you're gonna be able to:

* Automatically publish the events you create in TimePad, on your site. You won't have to transfer the events descriptions and formatting manually, paste the registration forms and break your events down into categories. A newly created event will automatically appear on your site, right where you want it.
* A full-feature registration and ticket selling form will appear automatically in every published event. You don't have to paste it manually or customize your theme's templates: the TimePad Events plugin is compatible with any third-party theme or plugin for WordPress.
* The list of your events will be available right in your WordPress admin area. If you need to change or edit your event, the plugin will redirect you straight to the appropriate page in your TimePad account.

If you're a site developer or have your own WordPress plugin or theme, the TimePad Events plugin will provide a convenient toolset for ticket selling forms integration. Just install the shortcode `[timepadregistration eventid="123"]` where you need it, and TimePad Events will do the rest.

== Installation  ==

To start automatically publishing your events on your site you have to do a few simple steps:

1. Go to "Plugins" > "Add new" in your WordPress admin area, type "TimePad Events" into the search and click the "Install" button.
Alternatively, manually unzip the file [timepad-events.zip](/files/timepad-events.zip) into your site's `/wp-content/plugins/` directory.
2. Activate the "TimePad Events" plugin in the Plugins section of your WordPress site. An "Events" menu item should appear in the right column.
3. Navigate to "Events" > "Settings" section and link your WordPress installation to your TimePad account. If you don't have a TimePad account yet, you can easily create it right in this section.
4. Configure the import settings in the same section. The TimePad Events plugin won't automatically import your events by default.

You're done! Now all events you publish on TimePad will automatically appear on your WordPress website.

= Requirements =

* WordPress 4.0+
* PHP 5.4+
* php-curl
* php-json

== Changelog ==

= 1.1.5 =

* Bug fixes on event update

= 1.1.4 =

* Auth module changed

= 1.1.3 =

* New app ID

= 1.1.2 =

* Improved compatibylity with PHP 5.4

= 1.1.1 =

* Events now don't start syncing automatically after changing organization in settings

= 1.1.0 =

* Event syncronization made more seamless
* Now every event can be transformed into post
* Added widget configuration in plugin settings
* Added PHP error handling for older versions of PHP
* Fixed event poster updates

= 1.0.4 =
* Fixed security issues while activating plugin

= 1.0.3 =
* Fixed installation problems on PHP version 5.4 or higher

= 1.0.2 =
* oAuth authorisation mechanism changed to comply with WordPress guidelines

= 1.0.1 =
* readme.txt reformatted, installation instructions added

= 1.0.0 =
* Initial release
