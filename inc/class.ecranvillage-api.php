<?php
/**
* Class EcranVillage_API
*
* @package EcranVillage API
* @link https://github.com/RavanH/ecranvillage-api/
* @author RavanH
*/

namespace EcranVillage;

class API {

	/**
	* Get a collection of items
	*
	* @param WP_REST_Request $request Full data about the request.
	* @return WP_Error|WP_REST_Response
	*/

	public static function api_response( $request )
	{
		// get posts array from category
		$args = array(
			'post_type' => 'film',
			'tax_query' => array(
				array(
					'taxonomy' => 'statut',
					'field' => 'slug',
					'terms' => array('a-laffiche','a-venir','export')
				)
			),
			'posts_per_page' => -1
		);

		$posts = \get_posts( $args );

		// if $data empty or wp_error then return error response with 404 status code
		if ( empty( $posts ) || \is_wp_error( $posts ) ) {
			return null; // new WP_REST_Response( array( ), 404 );
		}

		// foreach throught them to get relevant data and add these to response array
		$data = array();
		foreach( $posts as $post ) {
			$data[] = self::prepare_item_for_response( $post, $request );
		}

		// return response array + status
		return new WP_REST_Response( $data, 200 );
	}

	public static function download_response( $request )
	{
		$date = \date("Ymd");
		\header('Content-Disposition: attachment; filename="export-'.$date.'.json"');

		return self::api_response( $request );
	}

	/**
	* Prepare the item for the REST response
	*
	* @param mixed $item WordPress representation of the item.
	* @param WP_REST_Request $request Request object.
	* @return mixed
	*/

	private static function prepare_item_for_response( $item, $request )
	{
		$postdata = array();
		$postdata['id'] = $item->ID;
		$postdata['titrefilm'] = $item->post_title;

		// build description
		$postdata['description'] = \EcranVillage\Film::get_feed_meta( $item->ID, false );
		//$postdata['description'] = strip_tags( apply_filters( 'get_the_excerpt', strip_shortcodes( $item->post_excerpt ) ) );

		$postdata['affiche'] = \get_the_post_thumbnail_url( $item->ID, 'full' );

		return $postdata;
	}

}
