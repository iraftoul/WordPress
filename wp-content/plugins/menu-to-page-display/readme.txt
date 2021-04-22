=== Menu to Page Display ===
Contributors: RustyBadRobot
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=NLUUNW22YAQ64
Tags: shortcode, menu, grid, pages, posts, page, query, display, list, columns
Requires at least: 3.0
Tested up to: 3.8.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display a menu within a page using the [menu-display] shortcode.

== Description ==

The *Menu to Page Display* was written to allow users to easily display a wordpress menu within a page without knowing PHP or editing template files.

Add the shortcode in a post or page, then add your menu slug to the attribute menu_name and use the arguments to include / exclude extra information as necessary. I've also added some extra options to display something more than just the title: include_date, include_excerpt, and image_size.

See the [WordPress Codex](http://codex.wordpress.org/Class_Reference/WP_Query) for information on using the arguments.

[Documentation](https://github.com/RustyBadRobot/menu-to-page-display/wiki) | [Examples](http://widgetmedia.co/menu-to-page-display-examples/)

== Installation ==

1. Upload `menu-to-page-display` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the *Plugins* menu in WordPress.
1. Add the shortcode to a post or page. 


== Frequently Asked Questions ==

= So the shortcode [menu-display] will not work by itself? =

No. You have to include at least the menu_name attribute. If you have created a wordpress menu called "My awesome menu" your shortcode will be [menu-display menu_name="my-awesome-menu"].

== Changelog ==

**Version 1.0**

* This is version 1.0.  Everything's new!