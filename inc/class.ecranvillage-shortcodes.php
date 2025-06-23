<?php
/**
 * SEANCES Shortcode class
 *
 * @package EcranVillage API
 * @link https://github.com/RavanH/ecranvillage-api/
 * @author RavanH
 */

namespace EcranVillage;

/**
 * Main shortcodes class.
 */
class Shortcodes {

	/**
	 * STAR RATINGS SHORTCODE
	 *
	 * @param array  $atts Attributes.
	 * @param string $content Content.
	 *
	 * @return string
	 */
	public static function etoiles( $atts, $content = null ) {
		\extract( // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			\shortcode_atts(
				array(
					'note'    => '0',
					'total'   => '5',
					'float'   => '',
					'display' => '',
				),
				$atts,
				'etoiles'
			)
		);

		$total = \round( (float) $total );

		// See if $atts has a keyless value and parse that.
		if ( empty( (int) $note ) ) {
			if ( ! empty( $atts[0] ) ) {
				$data = \explode( '/', $atts[0] );
				if ( ! empty( $data[0] ) && \is_numeric( $data[0] ) ) {
					$note = (float) $data[0];
				}
				if ( ! empty( $data[1] ) && \is_numeric( $data[1] ) ) {
					$total = (int) $data[1];
				}
			}
		}

		$note = (float) \str_replace( ',', '.', $note );

		// Make sure $total is not 0 or below, default to 5.
		$total = $total > 0 ? $total : 5;

		$val = (int) ( $note * 10 );

		$style  = \in_array( $display, array( 'block', 'inline', 'inline-block', 'none' ) ) ? 'display:' . $display . ';' : '';
		$style .= \in_array( $float, array( 'left', 'right' ) ) ? 'float:' . $float . ';' : '';
		if ( ! empty( $style ) ) {
			$style = ' style="' . $style . '"';
		}

		// Build output.
		if ( ! empty( $content ) ) {
			$output = '<blockquote' . ( \is_singular( 'film' ) ? ' itemprop="review"' : '' ) . ' itemscope itemtype="http://schema.org/Review">';
		} else {
			$output = '';
		}
		$output .= '<span class="rating"' . $style . ' title="' . ( $val / 10 ) . ' sur ' . $total . ' étoiles" aria-label="' . ( $val / 10 ) . ' sur ' . $total . ' étoiles"' . ( ! empty( $content ) ? ' itemprop="reviewRating"' : '' ) . ' itemscope itemtype="http://schema.org/Rating">';
		$output .= '<meta itemprop="worstRating" content="0"><meta itemprop="bestRating" content="' . $total . '"><meta itemprop="ratingValue" content="' . ( $val / 10 ) . '">';
		while ( $total > 0 ) {
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
			--$total;
		}
		$output .= '</span> ';
		$output .= ! empty( $content ) ? ' ' . $content . '</blockquote>' : '';

		return $output;
	}

	/**
	 * APPLICATION LINK SHORTCODE
	 *
	 * @param array  $atts Attributes.
	 * @param string $content Content.
	 *
	 * @return string
	 */
	public static function applink( $atts, $content = null ) {
		\extract( // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			\shortcode_atts(
				array(
					'title'  => '',
					'class'  => '',
					'target' => '',
				),
				$atts,
				'applink'
			)
		);

		$app_url = \get_option( 'ecranvillage_app_url' );

		if ( empty( $content ) ) {
			$content = $app_url;
		}

		$output = '<a href="' . \esc_url( $app_url ) . '"';
		if ( ! empty( $title ) ) {
			$output .= ' title="' . \esc_html( $title ) . '"';
		}
		if ( ! empty( $class ) ) {
			$output .= ' class="' . \esc_html( $class ) . '"';
		}
		if ( ! empty( $target ) ) {
			$output .= ' target="' . \esc_html( $target ) . '"';
		}
		$output .= '>' . $content . '</a>';

		return $output;
	}

	/**
	 *  SEANCES SHORTCODE
	 *
	 * @param array $atts Attributes.
	 *
	 * @return string
	 */
	public static function seances( $atts ) {
		global $post;

		\extract( // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			\shortcode_atts(
				array(
					'id'     => \get_post_meta( $post->ID, 'film_id', true ),
					'title'  => $post->post_title,
					'align'  => 'initial',
					'format' => 'table',
				),
				$atts,
				'seances'
			)
		);

		// These are for debugging:
		// delete_transient( 'films' );
		// delete_transient( 'seances_'.$post->ID );
		// delete_transient( 'villages');.

		$app_url = \get_option( 'ecranvillage_app_url' );

		if ( empty( $app_url ) ) {
			return self::none_found( 'Missing App URL.', $format );
		}

		$app_url = \trailingslashit( $app_url );

		// Determine the associated ID.
		if ( empty( $id ) || ! \ctype_digit( $id ) ) {
			// Get film id or wp_error.
			$id = \EcranVillage\API::get_film_id( $title );

			// got error? abort mission.
			if ( \is_wp_error( $id ) ) {
				return self::none_found( $id->get_error_message(), $format );
			}

			// no ID found? abort mission.
			if ( ! \ctype_digit( $id ) ) {
				return self::none_found( 'Unexpected ID format.', $format );
			}

			// set found id as post meta.
			\update_post_meta( $post->ID, 'film_id', $id );
		}

		// get seances json or abort mission.
		$seances_json = \EcranVillage\API::get_transient_or_remote( 'seances_' . $post->ID, 3600, $app_url . 'films/' . $id . '.json' );
		if ( \is_wp_error( $seances_json ) ) {
			return self::none_found( $seances_json->get_error_message(), $format );
		}

		// build villages array with ID and full name.
		$villages      = array();
		$villages_json = \EcranVillage\API::get_transient_or_remote( 'villages', 86400, $app_url . 'villages.json' );
		if ( ! \is_wp_error( $villages_json ) ) {
			foreach ( $villages_json as $village ) {
				if ( ! \is_array( $village ) ) {
					continue;
				}

				$village_id              = \isset( $village['id'] ) ? $village['id'] : 0;
				$commune                 = \isset( $village['commune']  ) ? $village['commune'] : '';
				$salle                   = \isset( $village['salle'] ) ? ', ' . $village['salle'] : '';
				$villages[ $village_id ] = $commune . $salle;
			}
		}

		// set locale for timestamp date conversion in strftime()
		// setlocale(LC_TIME, get_locale().'.UTF8');.

		// arrange seances per location id.
		$villages_seances = array();
		foreach ( $seances_json as $_seance ) {
			if ( ! \is_array( $_seance ) ) {
				continue;
			}

			if ( ! isset( $film_id ) || ( \isset( $_seance['film_id'] ) && $_seance['film_id'] == $film_id ) ) {
				$village_id = \isset( $_seance['village_id'] ) ? $_seance['village_id'] : 0;
				if ( \isset( $_seance['horaire'] ) && ! empty( $_seance['horaire'] ) ) {
					$timestamp = \ctype_digit( $_seance['horaire'] ) ? $_seance['horaire'] : \strtotime( $_seance['horaire'] );
				} else {
					$timestamp = 0;
				}
				$villages_seances[ $village_id ][ $timestamp ] = array(
					'version'           => \isset( $_seance['version'] ) ? $_seance->version : '',
					'audio_description' => \isset( $_seance['audio_description'] ) ? $_seance->audio_description : '',
					'info'              => \isset( $_seance['extras'] ) ? $_seance->extras : '',
					'annulee'           => \isset( $_seance['annulee'] ) ? $_seance->annulee : '',
				);
			}
		}

		// test for empty response.
		if ( ! $villages_seances ) {
			return self::none_found( 'Empty response.', $format );
		}

		// do deep sorting and test for failed sorting.
		if ( ! self::ksort_deep( $villages_seances ) ) {
			return self::none_found( 'Unexpected response.', $format );
		}

		// build our output from array.
		$output    = '';
		$now       = \time();
		$logo      = \get_site_icon_url(); // integrate into theme... default logo in the dir.
		$image_src = \wp_get_attachment_image_src( \get_post_thumbnail_id( $post->ID ) );
		$image     = \is_array( $image_src ) ? '<meta itemprop="image" content="' . $image_src[0] . '">' : '';

		foreach ( $villages_seances as $_village_id => $_seances ) {
			$village = \array_key_exists( $_village_id, $villages ) ? $villages[ $_village_id ] : '';

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
					if ( 'simple' === $format ) {
						continue;
					} else {
						$class = ' class="past"';
					}
				} elseif ( $timestamp < $now + 6 * HOUR_IN_SECONDS ) {
					$class = ' class="soon"';
				}

				$datetime     = \gmdate( 'c', $timestamp ); // ISO 8601 date.
				$date         = ( 'simple' === $format ) ? \ucfirst( \wp_date( 'D. j M.', $timestamp ) ) : \ucfirst( \wp_date( \get_option( 'date_format' ), $timestamp ) );
				$heure        = \wp_date( \get_option( 'time_format' ), $timestamp );
				$info         = isset( $_data['info'] ) ? $_data['info'] : '';
				$version_data = array();
				if ( ! empty( $_data['version'] ) ) {
					$version_data[] = $_data['version'];
				}
				if ( ! empty( $_data['audio_description'] ) ) {
					$version_data[] = $_data['audio_description'];
				}

				// add del tags if cancelled.
				if ( ! empty( $_data['annulee'] ) ) {
					$date              = '<del>' . $date . '</del>';
					$heure             = '<del>' . $heure . '</del>';
					$version           = '';
					$audio_description = '';
					$info              = ( 'simple' === $format ) ? '' : 'Annulée';
				}

				$rows .= ( 'simple' === $format )
					? '<li itemscope itemtype="http://schema.org/ScreeningEvent"><meta itemprop="name" content="'
						. $title . '">' . $image . '<meta itemprop="startDate" content="' . $datetime . '">'
						. $location
						. ( ! empty( $info ) ? '<em>' . $info . '</em> : ' : '' ) . $date . ' ' . $heure . ( ! empty( $version_data ) ? ' ( <span itemprop="videoFormat">'
						. implode( ', ', $version_data ) . '</span> )' : '' )
					: '<tr' . $class . ' itemscope itemtype="http://schema.org/ScreeningEvent">'
						. '<td><meta itemprop="name" content="' . $title . '">' . $image . $date . '</td>'
						. '<td itemprop="startDate" content="' . $datetime . '">' . $heure . '</td>'
						. '<td itemprop="videoFormat">' . implode( ', ', $version_data ) . '</td>'
						. '<td class="extra">' . $info . $location . '</td></tr>';
			}

			if ( empty( $rows ) ) {
				continue;
			}

			$output .= $header . $rows;

			$output .= ( 'simple' === $format ) ? '</ul></dd>' : '</tbody></table>';

		}

		if ( empty( $output ) ) {
			$return = ( 'simple' === $format ) ? '' : '<p class="seances-none-found"><em>Il n\'y a plus de séances planifiées.</em></p>';
		} else {
			$return = ( 'simple' === $format ) ? '<dl class="seances">' . $output . '</dl>' : $output;
		}

		return $return;
	}

	/**
	 * Nothing found message
	 *
	 * @param string $msg Message.
	 * @param string $format Format.
	 *
	 * @return string
	 */
	private static function none_found( $msg, $format = '' ) {
		return ( 'simple' === $format ) ? '<!-- Error: ' . $msg . ' -->' : '<p class="seances-none-found"><em>Aucune séance trouvée.</em></p><!-- Error: ' . $msg . ' -->';
	}

	/**
	 * Deep ksort for multidimensional arrays
	 *
	 * @author       Kevin van Zonneveld &lt;kevin@vanzonneveld.net>
	 * @copyright    2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	 * @license      http://www.opensource.org/licenses/bsd-license.php New BSD Licence
	 * @version      SVN: Release: $Id: ksortTree.inc.php 223 2009-01-25 13:35:12Z kevin $
	 * @link         http://kevin.vanzonneveld.net/
	 *
	 * @param    array $array Array to deep sort.
	 *
	 * @return   true/false
	 */
	private static function ksort_deep( &$array ) {
		if ( ! \is_array( $array ) ) {
			return false;
		}

		ksort( $array );
		foreach ( $array as $k => $v ) {
			self::ksort_deep( $array[ $k ] );
		}

		return true;
	}
}
