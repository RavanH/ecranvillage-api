<?php
/*
 * Plugin Name: Écran Village Plannings API
 * Plugin URI:
 * Description: JSON endpoint and seances shortcode for Plannings App Écran Village
 * Version: 1.99.4
 * Author: RavanH
 * Author URI: http://status301.net/
 * License: GPLv3
 */

/*
Copyright (C) 2016 RavanH

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

/**
* Shortcode
*/

/* include static class */
include_once( __DIR__.'/inc/class-ecranvillage-shortcode.php' );

add_shortcode('seances', array( 'EcranVillage_Shortcode', 'seances') );
add_shortcode('séances', array( 'EcranVillage_Shortcode', 'seances') );

add_filter('get_the_excerpt', 'do_shortcode', 99);

/**
* WP API
*/

/* include static class */
include_once( __DIR__.'/inc/class-ecranvillage-api.php' );

/* create endpoints */
add_action( 'rest_api_init', function () {
  register_rest_route( 'ecranvillage-api/v2', '/export', array(
    'methods' => 'GET',
    'callback' => array('EcranVillage_API','api_response'),
  ) );
  register_rest_route( 'ecranvillage-api/v2', '/export/download', array(
    'methods' => 'GET',
    'callback' => array('EcranVillage_API','download_response'),
  ) );
} );

/**
* Menu
*/

add_action( 'admin_menu', function () {
  add_menu_page( 'Plannings', 'Plannings', 'edit_pages', 'ecranvillage-api/admin.php', '', 'dashicons-editor-video', 16 );
} );
