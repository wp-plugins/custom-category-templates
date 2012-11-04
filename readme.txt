=== Custom Category Templates ===
Contributors: shazdeh
Plugin Name: Custom Category Templates
Tags: template, category, theme
Requires at least: 3.4.0
Tested up to: 3.4.2
Stable tag: 0.2

Define custom templates for category views.

== Description ==

Just like the way you can create custom page templates, this plugin enables you to build category archive templates by adding this bit to the top of your file:

<code>
<?php
/**
 * Category Template: Grid
 */
?>
</code>
and when you're adding or editing categories, you can choose the desired template file.

You can safely include this plugin in your theme and ship custom templates for categories with the theme.


== Installation ==

1. Upload the whole plugin directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Enjoy!


== Changelog ==

= 0.2 =
* Implementation of new WP_Theme API
* Fixed a bug concerning body class output. Thanks @Sith Lord Goz!
* Added delete_option method