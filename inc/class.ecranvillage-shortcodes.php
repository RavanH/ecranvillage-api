<?php
/**
* SEANCES Shortcode class
*
* @package EcranVillage API
* @link https://github.com/RavanH/ecranvillage-api/
* @author RavanH
*/

namespace EcranVillage;

class Shortcodes {

	private static $timeout = 3;

	/*************************
	* STAR RATINGS SHORTCODE
	*************************/
	public static function etoiles( $atts, $content = null )
	{
		\extract( \shortcode_atts( [
			'note' => '0',
			'total' => '5',
			'float' => '',
			'display' => ''
		], $atts, 'etoiles' ) );

		$total = \round( (float)$total );
		// see if $atts has a keyless value and parse that
		if ( empty( (int)$note ) ) {
			if ( !empty($atts[0]) ) {
				$data = \explode( '/', $atts[0] );
				if ( !empty($data[0]) && \is_numeric($data[0]) ) $note = (float)$data[0];
				if ( !empty($data[1]) && \is_numeric($data[1]) ) $total = (int)$data[1];
			}
		}

		$note = (float) \str_replace( ',', '.', $note );

		// make sure $total is not 0 or below, default to 5
		$total = $total > 0 ? $total : 5;

		$val = (int)($note*10);

		$style = \in_array( $display, array('block','inline','inline-block','none') ) ? 'display:' . $display . ';' : '';
		$style.= \in_array( $float, array('left','right') ) ? 'float:' . $float . ';' : '';
		if ( !empty($style) ) $style = ' style="' . $style . '"';

		// build output
		if ( !empty($content) )
		$output = '<blockquote'. ( \is_singular('film') ? ' itemprop="review"' : '' ) .' itemscope itemtype="http://schema.org/Review">';
		else
		$output = '';
		$output .= '<span class="rating"' . $style . ' title="' . ($val/10) . ' sur ' . $total . ' étoiles" aria-label="' . ($val/10) . ' sur ' . $total . ' étoiles"' . ( !empty($content) ? ' itemprop="reviewRating"' : '' ) . ' itemscope itemtype="http://schema.org/Rating">';
		$output .= '<meta itemprop="worstRating" content="0"><meta itemprop="bestRating" content="' . $total . '"><meta itemprop="ratingValue" content="' . ($val/10) . '">';
		while( $total > 0 ) {
			if ( $val >= 3 ) {
				if ( $val >= 8 ) {
					$output .= '<span class="star awarded"><u><strong class="screen-reader-text">*</strong></u></span>';
				} else {
					$output .= '<span class="star-half awarded"><u class="screen-reader-text">*</u></span>';
				}
				$val = $val - 10;
			} else {
				$output .= '<span class="star"><u class="screen-reader-text"> &#160;</u></span>';
			}
			$total--;
		}
		$output .= '</span> ';
		$output .= !empty($content) ? ' ' . $content . '</blockquote>' : '';

		return $output;
	}

	/*****************************
	* APPLICATION LINK SHORTCODE
	*****************************/
	public static function applink( $atts, $content = null )
	{
		\extract( \shortcode_atts( [
			'title' => '',
			'class' => '',
			'target' => ''
		], $atts, 'applink' ) );

		$app_url = \get_option( 'ecranvillage_app_url' );

		if ( empty( $content ) ) $content = $app_url;

		$output = '<a href="' . \esc_url($app_url) . '"';
		if ( !empty( $title ) ) {
			$output .= ' title="' . \esc_html($title) . '"';
		}
		if ( !empty( $class ) ) {
			$output .= ' class="' . \esc_html($class) . '"';
		}
		if ( !empty( $target ) ) {
			$output .= ' target="' . \esc_html($target) . '"';
		}
		$output .= '>' . $content . '</a>';

		return $output;
	}

	/*********************
	*  SEANCES SHORTCODE
	*********************/
	public static function seances( $atts )
	{
		global $post;

		\extract( \shortcode_atts( [
			'id' => \get_post_meta( $post->ID, 'film_id' , true),
			'title' => $post->post_title,
			'align' => 'initial',
			'format' => 'table'
		], $atts, 'seances' ) );

		// for debugging
		//delete_transient( 'films' );
		//delete_transient( 'seances_'.$post->ID );
		//delete_transient( 'villages');

		$app_url = \get_option( 'ecranvillage_app_url' );
		if ( false === $app_url ) {
			return self::none_found('missing App URL.');
		}
		$app_url = \untrailingslashit( $app_url );

		// determine the associated ID
		if ( empty( $id ) || ! is_numeric( $id ) ) {
			// get films json or abort mission
			$films_json = self::get_transient_or_remote( 'films', 600, $app_url.'/tous_les_films.json' ); // films_a_venir.json == films modified last 2 months
			if( is_wp_error( $films_json ) ) {
				$error_message = $films_json->get_error_message();
				return self::none_found($error_message);
			}

			foreach ( $films_json as $film ) {
				if ( \is_object($film) && $film->titrefilm == $title ) {
					$id = $film->id;
					break 1;
				}
			}

			// no match found? abort mission
			if ( empty( $id ) || ! \is_numeric( $id ) ) {
				return self::none_found($title.' not found.', $format);
			}

			\update_post_meta( $post->ID, 'film_id', $id );
		}

		// get seances json or abort mission
		$seances_json = self::get_transient_or_remote( 'seances_'.$post->ID, 3600, $app_url.'/films/'.$id.'.json' );
		if ( \is_wp_error( $seances_json ) ) {
			$error_message = $seances_json->get_error_message();
			return self::none_found($error_message);
		}

		// build villages array with ID and full name
		$villages = array();
		$villages_json = self::get_transient_or_remote( 'villages', 86400, $app_url.'/villages.json' );
		if ( ! \is_wp_error( $villages_json ) ) {
			foreach ( $villages_json as $village ) {
				if ( ! \is_object($village) ) continue;

				$village_id = \property_exists($village, 'id') ? $village->id : 0;
				$commune = \property_exists($village, 'commune') ? $village->commune : '';
				$salle = \property_exists($village, 'salle') ? ', ' . $village->salle : '';
				$villages[$village_id] = $commune . $salle;
			}
		}

		// set locale for timestamp date conversion in strftime()
		//setlocale(LC_TIME, get_locale().'.UTF8');

		// prepare the seances array
		$seances = self::prepare_seances( $seances_json, $id );

		if ( !$seances ) {
			return self::none_found('Empty response.', $format);
		}

		// build our output from array
		$output = '';
		$now = \time();
		$logo = \get_site_icon_url(); // integrate into theme... default logo in the dir
		$image_src = \wp_get_attachment_image_src( \get_post_thumbnail_id( $post->ID ) );
		$image = \is_array( $image_src ) ? '<meta itemprop="image" content="' . $image[0] . '">' : '';

		foreach ( $seances as $_village_id => $_seances ) {
			$village = \array_key_exists($_village_id, $villages) ? $villages[$_village_id] : '';

			$header = ( 'simple' === $format )
				? '<dt><strong>' . $village . '</strong></dt><dd><ul>'
				: '<table class="seances"><caption><strong>' . $village . '</strong></caption><thead>'
					. '<tr><th>Date</th><th>Heure</th><th>Version</th><th>Info</th></tr></thead><tbody>';

			$location = '<span itemprop="location" itemscope itemtype="http://schema.org/MovieTheater">'
				. '<meta itemprop="name" content="Écran Village"><meta itemprop="image" content="' . $logo . '">'
				. '<meta itemprop="address" content="' . $village . '"></span>';

			$rows = '';

			foreach ( $_seances as $timestamp => $_data ) {

				$class = '';
				if ( $timestamp < $now - HOUR_IN_SECONDS ) {
					if ( 'simple' === $format )
						continue;
					else
						$class = ' class="past"';
				} elseif ( $timestamp < $now + 6 * HOUR_IN_SECONDS ) {
					$class = ' class="soon"';
				}

				$datetime = \date('c', $timestamp); // ISO 8601 date
				$date = ('simple' === $format) ? \ucfirst( \wp_date( 'D. j M.', $timestamp ) ) : \ucfirst( \wp_date( \get_option( 'date_format' ), $timestamp ) );
				$heure = \wp_date( \get_option( 'time_format' ), $timestamp );
				$version = isset($_data['version']) ? $_data['version'] : '';
				$info = isset($_data['info']) ? $_data['info'] : '';

				// add del tags if cancelled
				if ( !empty($_data['annulee']) ) {
					$date = '<del>' . $date . '</del>';
					$heure = '<del>' . $heure . '</del>';
					$version = '';
					$info = ( 'simple' === $format ) ? '' : 'Annulée';
				}

				$rows .= ( 'simple' === $format )
				 	? '<li itemscope itemtype="http://schema.org/ScreeningEvent"><meta itemprop="name" content="'
						. $title . '">' . $image . '<meta itemprop="startDate" content="' . $datetime . '">'
						. $location
						. ( !empty($info) ? '<em>' . $info . '</em> : ' : '' ) . $date . ' ' . $heure . ( !empty($version) ? ' (<span itemprop="videoFormat">'
						. $version . '</span>)' : '' )
					: '<tr' . $class . ' itemscope itemtype="http://schema.org/ScreeningEvent">'
						. '<td><meta itemprop="name" content="' . $title . '">' . $image . $date . '</td>'
						. '<td itemprop="startDate" content="' . $datetime . '">' . $heure . '</td>'
						. '<td itemprop="videoFormat">' . $version . '</td>' . '<td class="extra">' . $info
						. $location . '</td></tr>';
			}

			if ( empty($rows) )
				continue;

			$output .= $header . $rows;

			$output .= ( 'simple' === $format ) ? '</ul></dd>' : '</tbody></table>';

		}

		if ( empty($output) )
			$return = ( 'simple' === $format ) ? '' : '<p class="seances-none-found"><em>Il n\'y a plus de séances planifiées.</em></p>';
		else
			$return = ( 'simple' === $format ) ? '<dl class="seances">'.$output.'</dl>' : $output;

		return $return;
	}

	/**
	* Nothing found message
	*/

	private static function none_found( $msg, $format = '' )
	{
		return ( 'simple' === $format ) ? '' : '<p class="seances-none-found"><em>Aucune séance trouvée.</em></p><!-- Error: '.$msg.' -->';
	}

	/**
	* Get remote JSON and decode.
	*
	* Always returns a WP_Error object or a decoded JSON array of objects.
	*
	* @param string $url, self::$timeout
	* @return array\obj JSON\WP_Error
	*/

	private static function remote_get_json_decode( $url )
	{
		$response = \wp_remote_get( $url, self::$timeout );

		if ( \is_wp_error( $response ) ) {
			return $response;
		}

		$json = \json_decode( $response['body'] );

		switch ( \json_last_error() ) {
			case JSON_ERROR_NONE:
				return $json;

			case JSON_ERROR_DEPTH:
				return new WP_Error( 'json_error', 'Maximum stack depth exceeded in ' . $url );

			case JSON_ERROR_STATE_MISMATCH:
				return new WP_Error( 'json_error', 'Underflow or the modes mismatch in ' . $url );

			case JSON_ERROR_CTRL_CHAR:
				return new WP_Error( 'json_error', 'Unexpected control character found in ' . $url );

			case JSON_ERROR_SYNTAX:
				return new WP_Error( 'json_error', 'Syntax error, malformed JSON in ' . $url );

			case JSON_ERROR_UTF8:
				return new WP_Error( 'json_error', 'Malformed UTF-8 characters, possibly incorrectly encoded ' . $url );

			default:
				return new WP_Error( 'json_error', 'Unknown error in ' . $url );
		}
	}

	/**
	* Get object from transient or from remote JSON
	*
	* @param string $transient (required), int $exiration, string $url
	* @return array\obj JSON\WP_Error
	*/

	private static function get_transient_or_remote( $transient, $expiration = 0, $url = '' )
	{
		// W3TC: Do we need to turn off the object cache temporarily while we deal with
		// transients, as the W3 Total Cache conflicts with our work if transient
		// expiration is longer than the object cache expiration?
		// TODO: Test this theory or ask Frediric Townes...

		//global $_wp_using_ext_object_cache;
		//$_wp_using_ext_object_cache_previous = $_wp_using_ext_object_cache;
		//$_wp_using_ext_object_cache = false;

		$json = \get_transient( $transient );

		if( false === $json && !empty($url) ) {
			$json = self::remote_get_json_decode( $url );

			if( ! \is_wp_error( $json ) ) {
				\set_transient( $transient, $json, $expiration );
			}
		}

		// return object caching to its previous state
		//$_wp_using_ext_object_cache = $_wp_using_ext_object_cache_previous;

		return $json;
	}

	/**
	* Prepare the seances json response object array for parsing.
	* The objects are grouped by location, turned into arrays and sorted by date.
	*
	* @param array/obj $json
	* @return array
	*/

	private static function prepare_seances( $json, $film_id = null )
	{
		// set timezone for date to UNIX time conversion
		/*$current_offset = get_option('gmt_offset');
		$tzstring = get_option('timezone_string');
		if ( empty($tzstring) ) { // Create a UTC+- zone if no timezone string exists
			if (0 == $current_offset) {
				$tzstring = 'UTC';
			} elseif ($current_offset < 0) {
				$tzstring = 'UTC' . $current_offset;
			} else {
				$tzstring = 'UTC+' . $current_offset;
			}
		}
		date_default_timezone_set($tzstring);*/

		// arrange seances per location id
		$villages_seances = array();
		foreach ( $json as $_seance ) {
			if ( ! \is_object($_seance) ) continue;

			if ( ! isset($film_id) || ( \property_exists($_seance, 'film_id') && $_seance->film_id == $film_id ) ) {
				$village_id = \property_exists($_seance, 'village_id') ? $_seance->village_id : 0;
				if ( \property_exists($_seance, 'horaire') ) {
					$_timestamp = \strtotime( $_seance->horaire );
					$timestamp = $_timestamp ? $_timestamp : $_seance->horaire;
				} else {
					$timestamp = 0;
				}
				$villages_seances[$village_id][$timestamp] = array(
					'version' => \property_exists($_seance, 'version') ? $_seance->version : '',
					'info'  => \property_exists($_seance, 'extras') ? $_seance->extras : '',
					'annulee' => \property_exists($_seance, 'annulee') ? $_seance->annulee : ''
				);
			}
		}

		// sorting
		self::ksort_deep($villages_seances);

		return $villages_seances;
	}

	/**
	* Deep ksort for multidimensional arrays
	*
	* @author		Kevin van Zonneveld &lt;kevin@vanzonneveld.net>
	* @copyright	2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	* @license		http://www.opensource.org/licenses/bsd-license.php New BSD Licence
	* @version		SVN: Release: $Id: ksortTree.inc.php 223 2009-01-25 13:35:12Z kevin $
	* @link			http://kevin.vanzonneveld.net/
	*
	* @param	array $array
	* @return	true/false
	*/

	private static function ksort_deep( &$array )
	{
		if ( ! \is_array($array) ) {
			return false;
		}

		ksort( $array );
		foreach ( $array as $k => $v ) {
			self::ksort_deep($array[$k]);
		}

		return true;
	}

}
