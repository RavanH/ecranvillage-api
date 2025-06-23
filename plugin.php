<?php
/**
 * Plugin Name: Écran Village
 * Plugin URI:
 * Description: Films post type, JSON endpoint and seances shortcode for Plannings App Écran Village
 * Version: 4.3.0-alpha1
 * Author: RavanH
 * Author URI: http://status301.net/
 * License: GPLv3
 *
 * @package Écran Village API
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
defined( 'ABSPATH' ) || exit;

define( 'ECRANVILLAGE_DIR', __DIR__ );

add_action( 'init', array( '\EcranVillage\Plugin', 'init' ) );

register_activation_hook( __FILE__, array( '\EcranVillage\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( '\EcranVillage\Plugin', 'deactivate' ) );

/**
 * AUTOLOADER
 */
spl_autoload_register(
	function ( $class_name ) {
		// Bail out if not inside EcranVillage namespace or class already exists.
		if ( 0 !== strpos( $class_name, 'EcranVillage\\' ) ) {
			return;
		}

		// Construct file name.
		$class_name = str_replace( 'EcranVillage', 'inc', $class_name );
		$class_name = str_replace( '_', '-', $class_name );
		$class_name = strtolower( $class_name );
		$parts      = explode( '\\', $class_name );
		$parts[]    = 'class-' . array_pop( $parts ) . '.php';
		$file       = ECRANVILLAGE_DIR . DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $parts );
		if ( file_exists( $file ) ) {
			include_once $file;
		}
	}
);
