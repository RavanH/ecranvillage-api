=== Écran Village ===
Contributors: RavanH
Tags: custom post type, film, shortcodes
Stable tag: 4.2.3
Requires at least: 4.4
Tested up to: 5.5
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Film post type, JSON endpoint and seances shortcode for Plannings App Écran Village

== Description ==

Film post type, JSON endpoint and seances shortcode to work in conjuntion with the Plannings App "Écran Village".


== Upgrade Notice ==

= 4.2.3 =

Bug fix: Transient caches when external persistent object cache active

== Changelog ==

= 4.2.3 =
* FIX: transient caches when external persistent object cache active

= 4.2 =
* Remove Synopsis header from film template
* Add Audiodescription to API

= 4.1.1 =
* FIX: missing itemprop image

= 4.1.0 =
* Fetch film ID on post save.
* NEW: Tool to clear all film ID associations from database.

= 4.0.3 =
* FIX: Fix incorrect seances dates

= 4.0.2 =
* FIX: missing permission_callback in register_rest_route (WP v5.5 compat)
* FIX: call WP_REST_Response in root namespace

= 4.0 =
* Codebase overhaul with autoloader and namespace
* Re-seperation from theme

= 3.0 =
* add star ratings shortcode
