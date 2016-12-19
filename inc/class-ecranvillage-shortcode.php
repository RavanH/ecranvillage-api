<?php
/**
* Shortcode class
*
* @author RavanH
*/

class EcranVillage_Shortcode {

  public static $app_url = 'http://calendrierecranvillage.deploiement.ovh';

  private static $timeout = 3;

  public static function seances( $atts ) {
    global $post;

    extract( shortcode_atts( [
        'id' => get_post_meta( $post->ID, 'film_id' , true),
        'title' => $post->post_title,
        'align' => 'initial',
        'format' => 'table'
      ], $atts )
    );

    // for debugging
    //delete_transient( 'films' );
    //delete_transient( 'seances_'.$post->ID );
    //delete_transient( 'villages');

    // determine the associated ID
    if ( empty( $id ) || !is_numeric( $id ) ) {
      // get films json or abort mission
      $films_json = self::get_transient_or_remote( 'films', 60, trailingslashit(self::$app_url).'films.json' );
      if( is_wp_error( $films_json ) ) {
        $error_message = $films_json->get_error_message();
        return "<p style=\"text-align:$align\"><em>Aucune séance trouvée.</em></p><!-- Error: $error_message -->";
      }

      foreach ( $films_json as $film ) {
        if ( is_object($film) && $film->titrefilm == $title ) {
          $id = $film->id;
          break 1;
        }
      }

      // no match found? abort mission
      if ( empty( $id ) || !is_numeric( $id ) ) {
        return "<p style=\"text-align:$align\"><em>Aucune séance trouvée.</em></p><!-- Error: '$title' not found -->";
      }

      update_post_meta( $post->ID, 'film_id', $id );
    }

    // get seances json or abort mission
    $seances_json = self::get_transient_or_remote( 'seances_'.$id, 3600, trailingslashit(self::$app_url).'films/'.$id.'.json' );
    if( is_wp_error( $seances_json ) ) {
      $error_message = $seances_json->get_error_message();
      return "<p style=\"text-align:$align\"><em>Aucune séance trouvée.</em></p><!-- Error: $error_message -->";
    }

    // build villages array with ID and full name
    $villages = array();
    $villages_json = self::get_transient_or_remote('villages', 86400, trailingslashit(self::$app_url).'villages.json');
    if( !is_wp_error( $villages_json ) ) {
      foreach ( $villages_json as $village ) {
        if ( is_object($village) ) {
          $village_id = property_exists($village, 'id') ? $village->id : 0;
          $salle = property_exists($village, 'salle') ? $village->salle : '';
          $espace = property_exists($village, 'espace') ? ', ' . $village->espace : '';
          $villages[$village_id] = $salle . $espace;
        }
      }
    }

    // set locale for timestamp date conversion
    setlocale(LC_TIME, get_locale().'.UTF8');

    // prepare the seances array
    $seances = self::prepare_seances( $seances_json, $id );

    if ( !$seances ) {
     return "<p style=\"text-align:$align\">Aucune séance trouvée.</p><!-- Empty response -->";
    }

    // build our output from array
    $output = '';
    $h = 0;
    $now = time();
    foreach ( $seances as $_village_id => $_seances ) {
      $village = array_key_exists($_village_id, $villages) ? $villages[$_village_id] : '';

      if ( 'simple' === $format ) {
        $output .= "<dt style=\"text-align:$align;color: #ff6600\"><strong>$village</strong></dt><dd style=\"margin-bottom:0;text-align:$align\"><ul style=\"margin:0\">";
      } else {
        $output .= '<table style="width:100%"><caption style="text-align:left"><strong>'.$village.'</strong></caption><thead>'
          . '<tr style="text-align:left;background-color:rgba(125,125,125,.6);padding-left:1px">'
          . '<th style="padding-left:2px">Date et heure</th>'
          . '<th>Version</th>'
          . '<th>Extra info</th>'
          . '</tr></thead><tbody>';
      }

      $j = 0;
      foreach ( $_seances as $timestamp => $_data ) {
        $date = ('simple' === $format) ? strftime('%a %d/%m - %kh%M', $timestamp) : strftime('%A %e %B @ %kh%M', $timestamp);
        $version = isset($_data['version']) ? $_data['version'] : '';
        $info = isset($_data['statut']) ? $_data['statut'] : '';
        $faded = $timestamp < $now ? 'opacity:.5;' : '';
                
        //del text-decoration: line-through;
        if ( !empty($_data['annulee']) ) {
          $deleted = 'text-decoration:line-through;';
          $date = '<del>' . $date . '</del>';
          $version = '';
          $info = ( 'simple' === $format ) ? '' : 'Annulée';
        } else {
          $deleted = '';
        }

        if ( 'simple' === $format ) {
          $output .= '<li style="' . $faded . $deleted . '">' . $date . ( !empty($version) ? ' - ' . $version : '' ) . ( !empty($info) ? ' - ' . $info : '' ) . '</li>';
        } else {
          $output .= ++$j > 1 ? "<tr$style>" : '';
          $output .= "<td style=\"$faded$deleted\">$date</td><td style=\"$faded\">$version</td><td style=\"$faded\">$info</td>";
        }
      }

      $output .= ( 'simple' === $format ) ? '</ul></dd>' : '</tbody></table>';
    }
    
    // wrap it up and return
    return ( 'simple' === $format ) ? '<dl style="margin:0 0 1.625em 0">'.$output.'</dl>' : $output;
  }

  /**
  * Get remote JSON and decode.
  *
  * Always returns a WP_Error object or a decoded JSON array of objects.
  *
  * @param string $path, self::$app_url, self::$timeout
  * @return array\obj JSON\WP_Error
  */

  private static function remote_get_json_decode( $url ) {

    $response = wp_remote_get( $url, self::$timeout );

    if ( is_wp_error( $response ) ) {
      return $response;
    }

    $json = json_decode( $response['body'] );

    switch (json_last_error()) {
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

  private static function get_transient_or_remote( $transient, $expiration = 0, $url = '' ) {

    // W3TC: Do we need to turn off the object cache temporarily while we deal with
    // transients, as the W3 Total Cache conflicts with our work if transient
    // expiration is longer than the object cache expiration?
    // TODO: Test this theory or ask Frediric Townes...

    //global $_wp_using_ext_object_cache;
    //$_wp_using_ext_object_cache_previous = $_wp_using_ext_object_cache;
    //$_wp_using_ext_object_cache = false;

    $json = get_transient( $transient );

    if( false === $json && !empty($url) ) {
      $json = self::remote_get_json_decode( $url );

      if( !is_wp_error( $json ) ) {
        set_transient( $transient, $json, $expiration );
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

  private static function prepare_seances( $json, $film_id = null ) {
    // set timezone for date to UNIX time conversion
    $current_offset = get_option('gmt_offset');
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
    date_default_timezone_set($tzstring);

    // arrange seances per location id
    $villages_seances = array();
    foreach ( $json as $_seance ) {
      if ( is_object($_seance) ) {
        if ( !isset($film_id) || ( property_exists($_seance, 'film_id') && $_seance->film_id == $film_id ) ) {
          $village_id = property_exists($_seance, 'village_id') ? $_seance->village_id : 0;
          $timestamp = property_exists($_seance, 'horaire') ? strtotime( $_seance->horaire ) : 0;
          $version = property_exists($_seance, 'version') ? $_seance->version : '';
          $statut = property_exists($_seance, 'statut') ? $_seance->statut : '';
          $villages_seances[$village_id][$timestamp] = array('version'=>$version,'statut'=>$statut);
        }
      }
    }

    // sorting
    self::ksort_deep($villages_seances);

    return $villages_seances;
  }

  /**
  * Deep ksort for multidimensional arrays
  *
  * @author    Kevin van Zonneveld &lt;kevin@vanzonneveld.net>
  * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
  * @version   SVN: Release: $Id: ksortTree.inc.php 223 2009-01-25 13:35:12Z kevin $
  * @link	  http://kevin.vanzonneveld.net/
  *
  * @param   array $array
  * @return  true/false
  */

  private static function ksort_deep( &$array ) {
    if (!is_array($array)) {
      return false;
    }

    ksort($array);
    foreach ($array as $k=>$v) {
      self::ksort_deep($array[$k]);
    }

    return true;
  }

}
