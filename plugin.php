<?php
/*
 * Plugin Name: Écran Village
 * Plugin URI:
 * Description: Films post type, JSON endpoint and seances shortcode for Plannings App Écran Village
 * Version: 4.0.3
 * Author: RavanH
 * Author URI: http://status301.net/
 * License: GPLv3
 */

/*
Copyright (C) 2020 RavanH

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

/*
TODO
-- add a delete_transient( 'seances_'.$post->ID ); on post update or delete
-- add tools button to purge all (automatic) post2film associations with delete_post_meta( $post->ID, 'film_id', $id )
-- add options:
	-- which categories to include in the export JSON
	-- transient expiration times
-- add current_user_can check for options and purge
*/

// Exit if accessed directly.
defined( 'ABSPATH' ) ||	exit;

define( 'ECRANVILLAGE_DIR', dirname(__FILE__) );

/**
 * AUTOLOADER
 */

spl_autoload_register(
	function ( $class ) {
		// bail if not inside EcranVillage namespace or class already exists
		if ( 0 !== strpos( $class, 'EcranVillage\\' ) || class_exists( $class ) ) return;
		// construct file name
		$parts = explode( '\\', $class );
		$class = implode( '-', array_filter( $parts ) );
		$file = ECRANVILLAGE_DIR . '/inc/class.' . \strtolower( $class ) . '.php';
		// include file if it exists
		//if ( file_exists( $file ) )
		include $file;
	}
);

/**
* Film post type
*/

// ACTIONS
add_action( 'init', array( '\EcranVillage\Film', 'register_post_type' ) );
add_action( 'init',  array( '\EcranVillage\Film', 'register_taxonomies' ) );
add_action( 'save_post', array( '\EcranVillage\Film', 'save_meta' ), 1, 2 );

// FILTERS
add_filter( 'the_content', array( '\EcranVillage\Film', 'filter_content_pre' ), 1 );
add_filter( 'the_content', array( '\EcranVillage\Film', 'filter_content_post' ), 20 ); // priority 20 runs after jetpack share icons

/**
* Shortcodes
*/

add_action( 'init', function() {
	add_shortcode( 'seances', array( '\EcranVillage\Shortcodes', 'seances' ) );
	//add_shortcode( 'séances', array( '\EcranVillage\Shortcodes', 'seances' ) );

	add_shortcode( 'applink', array( '\EcranVillage\Shortcodes', 'applink' ) );

	add_shortcode( 'etoiles', array( '\EcranVillage\Shortcodes', 'etoiles' ) );
	//add_shortcode( 'étoiles', array( '\EcranVillage\Shortcodes', 'etoiles' ) );

	//add_filter( 'get_the_excerpt', 'do_shortcode', 99 );
} );

/**
* API Endpoints
*/

add_action( 'rest_api_init', function () {
	register_rest_route( 'ecranvillage-api/v2', '/export', array(
		'methods' => 'GET',
		'callback' => array('\EcranVillage\API','api_response'),
		'permission_callback' => '__return_true'
	) );
	register_rest_route( 'ecranvillage-api/v2', '/export/download', array(
		'methods' => 'GET',
		'callback' => array('\EcranVillage\API','download_response'),
		'permission_callback' => '__return_true'
	) );
} );

/**
* Admin page
*/

add_action( 'admin_menu', function () {
  add_menu_page( 'Plannings', 'Plannings', 'edit_pages', 'ecranvillage-admin', array('\EcranVillage\Admin','page'), 'dashicons-calendar-alt' );
} );

/**
* De/activation
*/

register_activation_hook( __FILE__, 'ev_activate' );

function ev_activate() {
	// force rewrite rules to be recrated at the right time
	delete_option('rewrite_rules');

	\EcranVillage\Film::register_post_type();
	\EcranVillage\Film::register_taxonomies();
	\EcranVillage\Film::insert_terms();
}

register_deactivation_hook( __FILE__, 'ev_deactivate' );

function ev_deactivate() {
	// force rewrite rules to be recrated at the right time
	delete_option('rewrite_rules');
}
