<?php
/**
 * Class EcranVillage_API
 *
 * @link https://github.com/RavanH/ecranvillage-api/
 * @author RavanH
 *
 * @package EcranVillage API
 */

namespace EcranVillage;

/**
 * Main API class
 */
class API {

	/**
	 * Timeout value. TODO: make this an option.
	 *
	 * @var $timeout
	 */
	private static $timeout = 3;

	/**
	 * Get a collection of items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function response( $request ) {
		// get posts array from category.
		$args = array(
			'post_type'      => 'film',
			'tax_query'      => array(
				array(
					'taxonomy' => 'statut',
					'field'    => 'slug',
					'terms'    => array( 'a-laffiche', 'a-venir', 'export' ),
				),
			),
			'posts_per_page' => -1,
		);

		$posts = \get_posts( $args );

		// if $data empty or wp_error then return error response with 404 status code.
		if ( empty( $posts ) || \is_wp_error( $posts ) ) {
			return null; // new \WP_REST_Response( array( ), 404 );.
		}

		// foreach throught them to get relevant data and add these to response array.
		$data = array();
		foreach ( $posts as $post ) {
			$data[] = self::prepare_item_for_response( $post, $request );
		}

		// return response array + status.
		return new \WP_REST_Response( $data, 200 );
	}

	/**
	 * Download response
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return mixed
	 */
	public static function download_response( $request ) {
		$date = \gmdate( 'Ymd' );
		\header( 'Content-Disposition: attachment; filename="export-' . $date . '.json"' );

		return self::response( $request );
	}

	/**
	 * Prepare the item for the REST response
	 *
	 * @param mixed           $item WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return mixed
	 */
	private static function prepare_item_for_response( $item, $request ) {
		$postdata              = array();
		$postdata['id']        = $item->ID;
		$postdata['titrefilm'] = $item->post_title;

		// build description.
		$postdata['description'] = \EcranVillage\Film::get_feed_meta( $item->ID, false );
		// $postdata['description'] = strip_tags( apply_filters( 'get_the_excerpt', strip_shortcodes( $item->post_excerpt ) ) );

		$postdata['affiche'] = \get_the_post_thumbnail_url( $item->ID, 'full' );

		return $postdata;
	}

	/**
	 * Get a remote response from the Plannins App
	 *
	 * @param string $title Title.
	 *
	 * @return int|WP_Error
	 */
	public static function get_film_id( $title = '' ) {
		$app_url = \get_option( 'ecranvillage_app_url' );

		if ( empty( $app_url ) ) {
			return new \WP_Error( 'ev_request_error', 'Missing App URL.' );
		}

		$films_json = self::get_transient_or_remote(
			'films',
			600,
			\trailingslashit( $app_url ) . 'tous_les_films.json'
		); // films_a_venir.json == films modified last 2 months.

		if ( \is_wp_error( $films_json ) ) {
			return $films_json;
		}

		// try by title.
		if ( ! empty( $title ) ) {
			foreach ( $films_json as $film ) {
				if ( \is_object( $film ) && $film->titrefilm == $title ) {
					return (int) $film->id;
				}
			}
		}

		// try by post_id.
		if ( isset( $import_id ) ) {
			foreach ( $films_json as $film ) {
				if ( \is_object( $film ) && $film->import_id == $import_id ) {
					return (int) $film->id;
				}
			}
		}

		return new \WP_Error( 'ev_request_error', 'No match found.' );
	}

	/**
	 * Get remote JSON and decode.
	 *
	 * Always returns a WP_Error object or a decoded JSON array of objects.
	 *
	 * @param string $url URL.
	 *
	 * @return array\obj JSON\WP_Error
	 */
	private static function remote_get_json_decode( $url ) {
		$response = \wp_remote_get( $url, self::$timeout );

		if ( \is_wp_error( $response ) ) {
			return $response;
		}

		$json = \json_decode( $response['body'], true );

		// return decoded json or WP error.
		switch ( \json_last_error() ) {
			case JSON_ERROR_NONE:
				return $json;

			case JSON_ERROR_DEPTH:
				return new \WP_Error( 'json_error', 'Maximum stack depth exceeded in ' . $url );

			case JSON_ERROR_STATE_MISMATCH:
				return new \WP_Error( 'json_error', 'Underflow or the modes mismatch in ' . $url );

			case JSON_ERROR_CTRL_CHAR:
				return new \WP_Error( 'json_error', 'Unexpected control character found in ' . $url );

			case JSON_ERROR_SYNTAX:
				return new \WP_Error( 'json_error', 'Syntax error, malformed JSON in ' . $url );

			case JSON_ERROR_UTF8:
				return new \WP_Error( 'json_error', 'Malformed UTF-8 characters, possibly incorrectly encoded ' . $url );

			default:
				return new \WP_Error( 'json_error', 'Unknown error in ' . $url );
		}
	}

	/**
	 * Get object from transient or from remote JSON
	 *
	 * @param string $transient  Transient name (required).
	 * @param int    $expiration Expiration.
	 * @param string $url        URL.
	 *
	 * @return array\obj JSON\WP_Error
	 */
	public static function get_transient_or_remote( $transient, $expiration = 0, $url = '' ) {
		if ( empty( $url ) ) {
			return false;
		}

		$json = \get_transient( $transient );

		if ( false === $json ) {
			$json = self::remote_get_json_decode( $url );

			if ( ! \is_wp_error( $json ) ) {
				\set_transient( $transient, $json, $expiration );
			}
		}

		return $json;
	}
}
