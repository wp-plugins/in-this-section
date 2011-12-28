=== Plugin Name ===
Contributors: ChemicalSailor
Tags: custom menus, widget
Requires at least: 3.1.3
Tested up to: 3.3
Stable tag: 1.0

Show a list of links to other pages within the same section, as defined in a custom menu. Great for CMS.

== Description ==

If you use WordPress for CMS, you probably have several pages, organised into sections. Custom menus make it easy to arrange these pages into sections. This widget takes any custom menu you choose, and parses it to find the current page and any pages in the same section, outputting a set of links for your sidebar.

Sections are defined by child menu items, or sub-menus. A top level menu item has pages in the same section if there is a sub-menu (i.e it has child menu items) defined for it. If there is no sub-menu a 'No other pages in section' message is displayed. Pages that are part of a sub-menu show the siblings of that page in the list.

== Installation ==

1. Download and unpack .zip file
1. Upload `in-this-section` directory to `wp-contents/plugins` directory
1. Active in the plugins page of the admin area
1. Configure the widget. You'll need a custom menu set up first.

Alternatively install directly from the WordPress admin panel.

== Changelog ==

= 1.0 =
* Initial Release
* Option to choose custom menu
* Ready for translation