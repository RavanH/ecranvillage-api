<?php
/**
* Class EcranVillage_API
*
* @package EcranVillage API
* @link https://github.com/RavanH/ecranvillage-api/
* @author RavanH
*/

namespace EcranVillage;

class Admin {

	public static function page()
	{
		$tab = 'import';
		$messages = array();

		if ( ( ! empty( $_POST ) || isset( $_GET['_wpnonce'] ) ) && \check_admin_referer('ecranvillage-settings') ) {

			if ( isset( $_POST['app_url'] ) || isset( $_POST['tmdb_api_key'] ) ) {
				$url = !empty($_POST['app_url']) ? \sanitize_option( 'siteurl', $_POST['app_url'] ) : '';
				\update_option( 'ecranvillage_app_url', $url );

				$key = !empty($_POST['tmdb_api_key']) ? \sanitize_text_field( $_POST['tmdb_api_key'] ) : '';
				\update_option( 'ecranvillage_tmdb_api_key', $key );

				$tab = 'settings';
				$messages[0] = __('Settings saved.');
			}

			if ( isset( $_GET['purge'] ) ) {

				// counter
				$i = 0;

				switch ( $_GET['purge'] ) {
					case 'films' :
						if ( \delete_transient( 'films' ) )
							$messages[] = 'Le cache de films est vidé avec succès.';
						else
							$messages[] = 'Le cache de films était déjà vide.';
					break;

					case 'villages' :
						if ( \delete_transient( 'villages' ) )
							$messages[] = 'Le cache de villages est vidé avec succès.';
						else
							$messages[] = 'Le cache de villages était déjà vide.';
					break;

					case 'seances' :
						$post_ids = \get_posts( array(
								'numberposts'	=> -1, // get all posts.
								'post_type'	=> 'film',
								'tax_query'		=> array(
										array(
											'taxonomy'	=> 'statut',
											'field'		=> 'slug',
											'terms'		=> array('a-laffiche','a-venir'),
										),
									),
								'fields'		=> 'ids', // Only get post IDs
							)
						);
						foreach ( $post_ids as $id ) {
							if ( \delete_transient( 'seances_'.$id ) ) $i++;
						}
						$messages[] = "Le cache de séances des films à l'affiche et à venir a été vidé avec succès. Un total de $i réponses mises en cache sont supprimées.";
					break;

					case 'seances-all' :
						$post_ids = \get_posts( array(
								'numberposts'	=> -1, // get all posts.
								'post_type'	=> 'film',
								'fields'		=> 'ids', // Only get post IDs
							)
						);
						foreach ( $post_ids as $id ) {
							if ( \delete_transient( 'seances_'.$id ) ) $i++;
						}
						$messages[] = "Le cache de séances de tous les films a été vidé avec succès. Un total de $i réponses mises en cache sont supprimées.";
					break;

					case 'film-ids' :
						global $wpdb;
						// images meta
						$i = $wpdb->delete( $wpdb->prefix.'postmeta', array( 'meta_key' => 'film_id' ) );
						$messages[] = "Les associations des films par ID ont été remis à zéro avec succès. Un total de $i IDs sont supprimées.";
					break;
				}
				// But guess what? Sometimes transients are not in the DB but in a persistent object cache, so we have to do this too:
				//wp_cache_flush(); // only flushes WP Object cache, not any persistent object cache

				$tab = 'tools';
			}
		}

		$app_url = \untrailingslashit( \get_option( 'ecranvillage_app_url', '' ) );
		$tmdb_api_key = \get_option( 'ecranvillage_tmdb_api_key', '' );

		if ( empty($app_url) ) {
			$tab = 'settings';
		}

		include( ECRANVILLAGE_DIR .'/inc/views/admin-page.php' );
	}

}
