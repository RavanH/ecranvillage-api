<?php
/**
 * Class EcranVillage Plugin
 *
 * @package EcranVillage API
 * @link https://github.com/RavanH/ecranvillage-api/
 * @author RavanH
 */

namespace EcranVillage;

/**
 * Main Admin class.
 */
class Plugin {
	/**
	 * Initialize the plugin.
	 */
	public static function init() {
		/**
		* Film post type
		*/
		\EcranVillage\Film::register_post_type();
		\EcranVillage\Film::register_taxonomies();

		// ACTIONS.
		add_action( 'save_post', array( '\EcranVillage\Film', 'save_meta' ), 1, 2 );

		// FILTERS.
		add_filter( 'the_content', array( '\EcranVillage\Film', 'filter_content_pre' ), 1 );
		add_filter( 'the_content', array( '\EcranVillage\Film', 'filter_content_post' ), 20 ); // Priority 20 runs after jetpack share icons.

		/**
		 * Shortcodes
		 */
		add_shortcode( 'seances', array( '\EcranVillage\Shortcodes', 'seances' ) );
		add_shortcode( 'applink', array( '\EcranVillage\Shortcodes', 'applink' ) );

		add_shortcode( 'etoiles', array( '\EcranVillage\Shortcodes', 'etoiles' ) );
		// Allow shortcodes in excerpts with add_filter( 'get_the_excerpt', 'do_shortcode', 99 ).

		/**
		* API Endpoints
		*/
		add_action(	'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );

		/**
		* Admin page
		*/
		add_action( 'admin_menu', array( '\EcranVillage\Plugin', 'add_admin_page' ) );
	}

	/**
	 * Register the REST API routes.
	 */
	public static function register_rest_routes() {
		register_rest_route(
			'ecranvillage-api/v2',
			'/export',
			array(
				'methods'             => 'GET',
				'callback'            => array( '\EcranVillage\API', 'response' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			'ecranvillage-api/v2',
			'/export/download',
			array(
				'methods'             => 'GET',
				'callback'            => array( '\EcranVillage\API', 'download_response' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Add the admin page.
	 */
	public static function add_admin_page() {
		add_menu_page( 'Plannings', 'Plannings', 'edit_pages', 'ecranvillage-admin', array( '\EcranVillage\Admin', 'page' ), 'dashicons-calendar-alt' );
	}
	
	/**
	 * Plugin activation hook.
	 */
	public static function activate() {
		// Force rewrite rules to be recrated at the right time.
		delete_option( 'rewrite_rules' );

		\EcranVillage\Film::register_post_type();
		\EcranVillage\Film::register_taxonomies();
		\EcranVillage\Film::insert_terms();
	}

	/**
	 * Plugin deactivation hook.
	 */
	public static function deactivate() {
		// Force rewrite rules to be recrated at the right time.
		delete_option( 'rewrite_rules' );
	}
}
