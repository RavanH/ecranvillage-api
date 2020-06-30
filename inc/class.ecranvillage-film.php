<?php
/**
 * ecranvillage film post type
 *
 * @package EcranVillage API
 * @link https://github.com/RavanH/ecranvillage-api/
 * @author RavanH
 */

namespace EcranVillage;

class Film {

	/* Film post type */
	public static function register_post_type()
	{
		register_post_type( 'film',
			array(
				'labels' => array(
					'name' => 'Films',
					'singular_name' => 'Film',
					'featured_image' => 'Affiche',
					'all_items' 	=> 'Tous les films'
				),
				'public' => true,
				'show_in_rest' => true,
				'has_archive' => true,
				'capability_type' => 'post',
				'hierarchical' => false,
				'supports' => array(
					'title',
					//'excerpt',
					'editor',
					'thumbnail',
					'custom-fields',
					'comments',
					'revisions',
				),
				//'query_var' => 'film',
				//'taxonomies' => array( 'category' ), //'genre', 'realisateur', 'acteur', 'statut', 'film_tag', 'type', 'pays', 'festival' ), // add registered taxonomies
				'menu_position' => 5,
				'menu_icon' => 'dashicons-editor-video',
				'register_meta_box_cb' => function() { // add the meta box
					add_meta_box( 'film_metabox', 'Film Meta', array( __CLASS__, 'metabox' ), 'film', 'normal', 'high' );
					//add_meta_box( 'critiques_metabox', 'Critiques', 'ev_critiques_metabox', 'film', 'normal' );
				},
				// film bloc editor template
				'template' => array(
					array( 'core/quote', array(
						//'value' => 'Bloc de critique à copier ou supprimer. Le shortcode [etoiles X] dont le X devrait être un chiffre entre 0 et 5, est dispo pour générer une image d\'étoiles.',
						//'citation' => 'Source...'
					) ),
					array( 'core/heading', array(
						'content' => 'Synopsis',
					) ),
					array( 'core/paragraph', array(
						'placeholder' => 'Ajout de synopsis en quelques paragraphes...',
					) ),
					array( 'core/gallery', array(
						'columns' => 3,
						'linkTo' => 'media'
					) ),
					array( 'core/heading', array(
						'content' => 'Précédé du court métrage&nbsp;: TITRE...',
						'level' => '3'
					) ),
					array( 'core/gallery', array(
						'columns' => 1,
						'align' => 'left',
						'linkTo' => 'media'
					) ),
	/*				array( 'core/image', array(
						'align' => 'left',
						'width' => 300,
						'linkTo' => 'media'
					) ),*/
					array( 'core/paragraph', array(
						'placeholder' => 'Ajout de synopsis ou supprime ces blocs du court métrage...',
					) )
				)
			)
		);
	}

	/* Film taxonomies */
	public static function register_taxonomies()
	{
		// Add new taxonomy, make it hierarchical (like categories) but don't allow editing
		register_taxonomy(
			'statut',
			array( 'film' ),
			array(
				'hierarchical' 		=> true,
				'labels' 			=> array(
					'name' 			=> 'Statuts',
					'singular_name' => 'Statut',
					'all_items' 	=> 'Tous les statuts'
				),
				'public' 			=> true,
				'show_in_rest'		=> true,
				'show_ui' 			=> true,
				'show_admin_column' => true,
				'show_tagcloud' 	=> false,
				//'query_var' 		=> false,
				'capabilities' 		=> array ( // prevent creation / deletion
					'manage_terms' 	=> 'edit_theme_options', //'edit_theme_options',
					'edit_terms' 	=> 'edit_theme_options', //'edit_theme_options',
					'delete_terms' 	=> 'nobody', //'edit_theme_options',
					'assign_terms' 	=> 'edit_posts'
				)
			)
		);

		// Add new taxonomy, make it hierarchical (like categories) --- allow partial editing ?
		register_taxonomy(
			'categorie',
			array( 'film' ),
			array(
				'hierarchical' 		=> true,
				'labels' 			=> array(
					'name' => 'Catégories',
					'singular_name' => 'Catégorie',
					'all_items' => 'Tous catégories'
				),
				'public' 			=> true,
				'show_in_rest' 		=> true,
				'show_ui' 			=> true,
				'show_admin_column' => true,
				'show_tagcloud' 	=> false,
				//'query_var' 		=> false,
				'capabilities' 		=> array( // prevent creation / deletion
					'manage_terms' 	=> 'edit_theme_options', //'edit_theme_options',
					'edit_terms' 	=> 'edit_theme_options', //'edit_theme_options',
					'delete_terms' 	=> 'nobody', //'edit_theme_options',
					'assign_terms' 	=> 'edit_posts'
				)
			)
		);

		// Add new taxonomy, NOT hierarchical (like tags)
		register_taxonomy(
			'realisateur',
			array( 'film' ),
			array(
				'hierarchical'		=> false,
				'labels'			=> array(
					'name' 				=> 'Réalisateurs',
					'singular_name' 	=> 'Réalisateur',
					'all_items' 		=> 'Tous réalisateurs',
					'parent_item' 		=> null,
					'parent_item_colon' => null
				),
				'show_in_rest'		=> true,
				'show_ui'			=> true,
				'show_admin_column'	=> false,
				//'update_count_callback' => '_update_post_term_count',
				//'rewrite'					=> array( 'slug' => 'realisateur' )
			)
		);

		// Add new taxonomy, NOT hierarchical (like tags)
		register_taxonomy(
			'acteur',
			array( 'film' ),
			array(
				'hierarchical'			 => false,
				'labels'					 => array(
					'name' 				=> 'Acteurs',
					'singular_name' 	=> 'Acteur',
					'all_items' 		=> 'Tous les acteurs',
					'parent_item' 		=> null,
					'parent_item_colon' => null
				),
				'show_in_rest'			=> true,
				'show_ui'					=> true,
				'show_admin_column'	  => false,
				//'update_count_callback' => '_update_post_term_count',
				//'rewrite'					=> array( 'slug' => 'acteur' )
			)
		);

		// Add new taxonomy, make it hierarchical (like categories)
		register_taxonomy(
			'genre',
			array( 'film' ),
			array(
				'hierarchical'		=> true,
				'labels'				=> array(
					'name'			 => 'Genres',
					'singular_name' => 'Genre',
					'all_items' 	=> 'Tous les genres'
				),
				'show_in_rest'		=> true,
				'show_ui'			  => true,
				'show_admin_column' => true,
				//'rewrite'			  => array( 'slug' => 'genre' )
			)
		);

		// Add new taxonomy, NOT hierarchical (like tags)
		register_taxonomy(
			'pays',
			array( 'film' ),
			array(
				'hierarchical'			 => false,
				'labels'					 => array(
					'name' 				=> 'Pays',
					'all_items' 		=> 'Tous pays',
					'parent_item' 		=> null,
					'parent_item_colon' => null
				),
				'show_in_rest'			=> true,
				'show_ui'					=> true,
				'show_admin_column'	  => false,
				//'update_count_callback' => '_update_post_term_count'
			)
		);

		// Add new taxonomy, NOT hierarchical (like tags)
		register_taxonomy(
			'annee',
			array( 'film' ),
			array(
				'hierarchical'			 => false,
				'labels'					 => array(
					'name' 				=> 'Année',
					'all_items' 		=> 'Toutes années',
					'parent_item' 		=> null,
					'parent_item_colon' => null
				),
				'show_in_rest'			=> true,
				'show_ui'					=> true,
				'show_admin_column'	  => true,
				//'update_count_callback' => '_update_post_term_count'
			)
		);

		// Add new taxonomy, NOT hierarchical (like tags)
		register_taxonomy(
			'version',
			array( 'film' ),
			array(
				'hierarchical' 		=> false,
				'labels' 			=> array(
					'name' 				=> 'Versions',
					'singular_name'		=> 'Version',
					'all_items' 		=> 'Toutes versions',
					'parent_item' 		=> null,
					'parent_item_colon' => null
				),
				'public' 			=> true,
				'show_in_rest'		=> true,
				'show_ui' 			=> true,
				'show_admin_column' => false
			)
		);

		// Add new taxonomy, NOT hierarchical (like tags)
		register_taxonomy(
			'conseil',
			array( 'film' ),
			array(
				'hierarchical' 		=> false,
				'labels' 			=> array(
					'name' 				=> 'Conseils d\'age',
					'singular_name' 	=> 'Conseil d\'age',
					'all_items' 		=> 'Tous conseils',
					'parent_item'		=> null,
					'parent_item_colon'	=> null
				),
				'public' 			=> true,
				'show_in_rest'		=> true,
				'show_ui' 			=> true,
				'show_admin_column' => false
			)
		);

		// Add new taxonomy, NOT hierarchical (like tags)
		register_taxonomy(
			'festival',
			array( 'film' ),
			array(
				'hierarchical' 		=> false,
				'labels' 			=> array(
					'name'				=> 'Festivals',
					'singular_name' 	=> 'Festival',
					'all_items'		 	=> 'Tous festivals',
					'parent_item'		=> null,
					'parent_item_colon'	=> null
				),
				'show_in_rest'		=> true,
				'show_ui' 			=> true,
				'show_admin_column' => false,
				//'update_count_callback' => '_update_post_term_count',
				//'rewrite'					=> array( 'slug' => 'festival' )
			)
		);
	}

	public static function insert_terms()
	{
		// routine to set up film status terms
		$terms = get_terms( 'statut', array( 'hide_empty' => false ) );

		if ( empty($terms) ) {
			$terms = array (
				'À l\'affiche', // this week
				'À venir', // soon
				//'Archivé', // archived
				//'Newsletter',
				//'Export'
			);

			foreach ( $terms as $name ) {
				wp_insert_term(	$name, 'statut' );
			}
		}
		// routine to set up film status terms
		$terms = get_terms('categorie',array('hide_empty' => false));

		if ( empty($terms) ) {
			$terms = array (
				'Ciné jeunesse', // child icon
				'Ciné mémoire', // history icon
				'Court métrage', // video icon / hourglass /
			);

			foreach ($terms as $name) {
				wp_insert_term(	$name, 'categorie' );
			}
		}

	}

	/**
	 * Gutenberg BLOCKS
	 */
	//include( ECRANVILLAGE_DIR . '/inc/blocks/genres/index.php' );

	/**
	 * META BOX
	 */

	public static function metabox()
	{
		global $post;
		// Noncename needed to verify where the data originated
		$nonce = wp_create_nonce( 'save_film_meta-' . $post->ID );

		// Get the data if its already been entered
		$trailer_url = get_post_meta($post->ID, 'trailer', true);
		$annee = get_post_meta($post->ID, 'annee', true);
		$duree = get_post_meta($post->ID, 'duree', true);
		$info = get_post_meta($post->ID, 'info', true);
		$info2 = get_post_meta($post->ID, 'info2', true);
		$info3 = get_post_meta($post->ID, 'info3', true);
		$film_id = get_post_meta($post->ID, 'film_id', true);
		$allocine = get_post_meta($post->ID, 'allocine', true);
		$tmdb = get_post_meta($post->ID, 'tmdb', true);
		$imdb = get_post_meta($post->ID, 'imdb', true);

		include( ECRANVILLAGE_DIR . '/inc/views/film-meta-box.php' );
	}

	/* Save film meta */
	public static function save_meta( $post_id, $post )
	{

		if ( $post->post_type !== 'film' ||
			 !isset( $_POST['film_meta_nonce'] ) ||
			 !wp_verify_nonce( $_POST['film_meta_nonce'], 'save_film_meta-' . $post->ID ) ||
			 !current_user_can( 'edit_post', $post->ID ) ) {
			return $post->ID;
		}

		$film_post_meta['trailer'] = isset($_POST['trailer']) ? esc_url_raw( trim($_POST['trailer']) ) : '';
		$film_post_meta['duree'] = isset($_POST['duree']) ? sanitize_text_field( $_POST['duree'] ) : '';
		$film_post_meta['info'] = isset($_POST['info']) ? sanitize_text_field( $_POST['info'] ) : '';
		$film_post_meta['info2'] = isset($_POST['info2']) ? sanitize_text_field( $_POST['info2'] ) : '';
		$film_post_meta['info3'] = isset($_POST['info3']) ? sanitize_text_field( $_POST['info3'] ) : '';
		$film_post_meta['film_id'] = isset($_POST['film_id']) ? (int)$_POST['film_id'] : '';

		if ( isset($_POST['allocine']) ) {
			$film_post_meta['allocine'] = ( is_numeric( $_POST['allocine'] ) ) ? 'http://www.allocine.fr/film/fichefilm_gen_cfilm=' . $_POST['allocine'] . '.html' : esc_url_raw( $_POST['allocine'] );
		} else {
			$film_post_meta['allocine'] = '';
		}

		if ( isset($_POST['tmdb']) ) {
			$film_post_meta['tmdb'] = ( is_numeric( $_POST['tmdb'] ) ) ? 'https://www.themoviedb.org/movie/' . $_POST['tmdb'] : esc_url_raw( $_POST['tmdb'] );
		} else {
			$film_post_meta['tmdb'] = '';
		}

		if ( isset($_POST['imdb']) ) {
			$film_post_meta['imdb'] = ( 0 === strpos( $_POST['imdb'], 'tt') || is_numeric( $_POST['imdb'] ) ) ? 'https://www.imdb.com/title/' . $_POST['imdb'] . '/' : esc_url_raw( $_POST['imdb'] );
		} else {
			$film_post_meta['imdb'] = '';
		}

		// add values as custom fields
		foreach( $film_post_meta as $key => $value ) { // cycle through the $quote_post_meta array
			if ( !$value ) { // delete if blank
				delete_post_meta( $post->ID, $key );
			} else {
				update_post_meta($post->ID, $key, $value);
			}
		}
	}

	/* remove old [seances] */
	public static function filter_content_pre( $content )
	{
	  // Check if we're inside the main loop in a single post page.
	  if ( is_singular('film') && in_the_loop() && is_main_query() ) {
			// we may need to strip old séances title and shortcode
			$content = str_replace( array("<h1>Séances</h1>","<h1>SÉANCES</h1>","<h1>SEANCES</h1>","[seances]"), '', $content );
	  }

	  return $content;
	}

	/* append content with 1. seances and 2. trailer */
	public static function filter_content_post( $content )
	{
		 // Check if we're inside the main loop in a single post page.
		 if ( is_singular('film') && in_the_loop() && is_main_query() ) {

			$content = '
				<div id="synopsis" itemprop="description">
					' . $content . '
				</div>';

			// add our trailer (if there is one)
			$trailer_url = self::get_trailer_url( get_the_ID() );
			if ( !empty($trailer_url) ) {
				global $wp_embed;
				$content .= '
				<div id="bande-annonce" style="margin-top:1.5em">
					<h2>Bande annonce</h2>
					<div class="trailer">' . $wp_embed->run_shortcode( '[embed]' . $trailer_url . '[/embed]' ) . '</div>
				</div>';
			}

			// add our seances if in the right state
			if ( has_term( array('a-laffiche','a-venir'), 'statut' ) ) {
				$content .= '
				<div id="seances" style="margin-top:1.5em">
					<h2>Séances</h2>
					' . do_shortcode( '[seances]' ) . '
				</div>';
			}
		 }

		 return $content;
	}

	public static function get_trailer_url( $id )
	{
		$url = get_post_meta( $id, 'trailer', true );

		if ( empty($url) )
			return '';

		if ( strpos($url,'youtu') ) {
			$args = array(
				'modestbranding' => '1',
				'rel' => '0',
				'showinfo' => '0'
			);
			// convert short url
			$url = str_replace('youtu.be/', 'youtube.com/watch?v=', $url);
		} elseif ( strpos($url,'vimeo') ) {
			$args = array(
				'title' => '0',
				'byline' => '0',
				'portrait' => '0',
				'outros' => '0'
			);
		} elseif ( strpos($url,'dailymotion') ) {
			$args = array(
				'ui-logo' => 'false',
				'endscreen-enable' => 'false',
				'ui-start-screen-info' => 'false'
			);
		} else {
			$args = array();
		}

		return esc_url( add_query_arg( $args, $url ) );
	}

	private static function get_metadata( $post_id, $meta_keys = array(), $before = '', $sep = ', ', $after = '' )
	{
		$data = array();

		foreach ( (array)$meta_keys as $meta_key ) {
			$metadata = get_metadata( 'post', $post_id, $meta_key, true );

			if ( is_wp_error( $metadata ) )
				return false;

			$data[] = $metadata;
		}

		$data = array_filter($data);

		 if ( empty( $data ) )
			  return false;

		 return $before . join( $sep, $data ) . $after;
	}

	/* film meta */
	public static function get_meta()
	{
		$id = get_the_ID();

		$return = '<p>';

		$terms = wp_get_post_terms( $id, 'realisateur', array('orderby'=>'count', 'order' => 'DESC') );
		$links = array();
		if ( !empty($terms) ) {
			$return .= 'Film de <span class="real-links" itemprop="director" itemscope itemtype="http://schema.org/Person"><span itemprop="name">';

			foreach( $terms as $term ) {
				$term_link = get_term_link( $term, 'acteur' );
				if ( is_wp_error( $term_link ) ) continue;
				$links[] = '<a href="' . $term_link . '" rel="tag">' . $term->name . '</a>';
			}

			$return .= implode('</span></span>' . esc_html__( ', ', 'dyad-2' ) . '<span class="real-links" itemprop="director" itemscope itemtype="http://schema.org/Person"><span itemprop="name">',$links);
			$return .= '</span></span><br/>';
		}
		//$return .= get_the_term_list( $id, 'realisateur', 'Film de <span class="real-links" itemprop="director" itemscope itemtype="http://schema.org/Person"><span itemprop="name">', esc_html__( ', ', 'dyad-2' ), '</span></span><br />' );

		$return .= get_the_term_list( $id, 'genre', '<span class="icon folder" itemprop="genre">', esc_html__( ', ', 'dyad-2' ), '</span>' );
		$return .= get_the_term_list( $id, 'pays', ' <span class="icon globe" itemprop="countryOfOrigin" itemscope itemtype="http://schema.org/Country"><span itemprop="name">', esc_html__( ', ', 'dyad-2' ), '</span></span>' );
		$return .= get_the_term_list( $id, 'annee', ' <span class="icon calendar" itemprop="dateCreated">', '/', '</span>' );
		$return .= self::get_metadata( $id, array('duree'), ' <span class="icon clock" itemprop="duration">', ' / ', '</span>' );
		$return .= get_the_term_list( $id, 'version', ' <span class="icon tags">', esc_html__( ', ', 'dyad-2' ), '</span>' );
		$return .= get_the_term_list( $id, 'festival', ' <span class="icon festival">', esc_html__( ', ', 'dyad-2' ), '</span>' );
		$return .= get_the_term_list( $id, 'conseil', ' <span class="icon eye">', esc_html__( ', ', 'dyad-2' ), '</span>' );
		$return .= '</p>';

		$terms = wp_get_post_terms( $id, 'acteur', array('orderby'=>'count', 'order' => 'DESC') );
		$links = array();
		if ( !empty($terms) ) {
			$return .= '<p>Avec <span class="act-link" itemprop="actor" itemscope itemtype="http://schema.org/Person"><span itemprop="name">';

			foreach( $terms as $term ) {
				$term_link = get_term_link( $term, 'acteur' );
				if ( is_wp_error( $term_link ) ) continue;
				$links[] = '<a href="' . $term_link . '" rel="tag">' . $term->name . '</a>';
			}

			$return .= implode('</span></span>' . esc_html__( ', ', 'dyad-2' ) . '<span class="act-link" itemprop="actor" itemscope itemtype="http://schema.org/Person"><span itemprop="name">',$links);
			$return .= '</span></span></p>';
		}
		//$return .= get_the_term_list( $id, 'acteur', '<p>Avec <span class="act-link" itemprop="actor" itemscope itemtype="http://schema.org/Person"><span itemprop="name">', '</span></span>' . esc_html__( ', ', 'dyad-2' ) . '<span class="act-link" itemprop="actor" itemscope itemtype="http://schema.org/Person"><span itemprop="name">', '</span></span></p>' );

		$return .= self::get_metadata( $id, array('info','info2','info3'), '<p class="extra">', ' &mdash; ', '</p>' );

		return $return;
	}

	public static function get_feed_meta( $id = false, $html = true )
	{
		$return = '';

		$strong_open = $html ? '<strong>' : '';
		$strong_close = $html ? '</strong>' : '';

		if ( !$id && in_the_loop() ) $id = get_the_ID();

		if ( is_numeric($id) ) {
			$taxs = array(
				'realisateur' => array('Film de '.$strong_open,$strong_close.', '.$strong_open,$strong_close.'. '.PHP_EOL.PHP_EOL),
				'acteur' => array('Avec '.$strong_open,$strong_close.', '.$strong_open,$strong_close.'. '.PHP_EOL.PHP_EOL),
				'genre' => array($strong_open,$strong_close.', '.$strong_open,$strong_close.' * '),
				'pays' => array($strong_open,$strong_close.', '.$strong_open,$strong_close.' * '),
				'annee' => array($strong_open,$strong_close.'/'.$strong_open,$strong_close.' * '),
				'version' => array($strong_open,$strong_close.', '.$strong_open,$strong_close.' * '),
				'conseil' => array($strong_open,$strong_close.', '.$strong_open,$strong_close.' * '),
			);
			foreach ( $taxs as $tax => $seps ) {
				$terms = get_the_terms( $id, $tax );
				if ( !empty( $terms ) && !is_wp_error( $terms ) ) {
					$names = array();
					foreach ( $terms as $term ) {
						$names[] = $term->name;
					}
					$return .= $seps[0] . implode( $seps[1], $names ) . $seps[2];
				}
			}
			$return .= self::get_metadata( $id, array('duree'), $strong_open, $strong_close.'/'.$strong_open, $strong_close.'. ' );
			$return .= PHP_EOL.PHP_EOL;
			$return .= self::get_metadata( $id, array('info','info2','info3'), $strong_open, $strong_close.'. '.$strong_open, $strong_close.'.' );

			if ($html) {
				$return = wpautop($return);
			}
		}

		return $return;
	}

}
