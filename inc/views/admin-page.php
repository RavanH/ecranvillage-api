<style type="text/css">
a.nav-tab:focus { box-shadow: none }
</style>

<?php if ( !empty($messages) ) : foreach ( $messages as $message ) { ?>
	<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
		<p><strong><?php echo $message ?></strong></p>
	</div>
<?php }; endif; ?>

<div class="wrap">
	<h1>Plannings</h1>

	<nav class="nav-tab-wrapper">
		<?php if ( !empty($app_url) ) : ?>
		<a href="#import" class="nav-tab import"><?php _e('Export'); ?> / <?php _e('Import'); ?></a>
		<a href="#tools" class="nav-tab tools"><?php _e('Tools'); ?></a>
		<?php endif; ?>
		<a href="#settings" class="nav-tab settings"><?php _e('Settings'); ?></a>
		<a href="#shortcodes" class="nav-tab shortcodes"><?php _e('Shortcodes'); ?></a>
	</nav>

<?php if ( !empty($app_url) ) : ?>

	<div id="import" class="tab">
		<h2>Exporter les nouveaux films vers l'application Plannings</h2>
		<p>Pour importer les nouveaux films dans l'application Plannings, il suffit d'aller sur cette page <a href="<?php echo $app_url . '/ecranvillage'; ?>" class="button" target="plannings">Import Plannings</a></p>
		<p>Cette façon de faire est la même si on clique sur le bouton 'Importer les nouveaux films' dans la page Films de l'application Plannings.</p>
		<h2>Exporter des anciens films</h2>
		<p>Si c'est un film qui existait déjà dans les archives, il suffit de le remettre dans la catégorie 'à venir' ou provisoirement dans la catégorie 'export', puis de recommencer la procédure.</p>
		<p>Vérifiez aussi si le film n'existe pas déjà dans l'application Plannings <a href="<?php echo $app_url . '/admin/film'; ?>" class="button" target="plannings">ici</a>. Si c'est le cas, cliquez sur 'Voir dans l'application' puis 'Modifier' et éditer les champs 'created_at' et 'updated_at' à la date d'aujourd'hui de façon à retrouver ensuite le film dans les films à venir et dans l'édition des séances.</p>
		<h2>Gestion des séances</h2>
		<p>Pour éditer les séances, tout ce passe dans la page <a href="<?php echo $app_url . '/films'; ?>" class="button" target="plannings">Films</a> de l'application. Puis, les seances doivent apparaître en bas des films automatiquement. Sinon, il y a un problème avec l'association entre l'article et la fiche du film dans l'application.</p>
		<p><strong>Associer un article sur le site au bon film dans l'application manuellement:</strong>
			<ul>
				<li>- Ouvre la page <a href="<?php echo $app_url . '/admin/film'; ?>" target="plannings">Listing des Films</a> dans l'application Plannings et note le chiffre de l'<strong>Id</strong> du film souhaité.</li>
				<li>- Reviens sur l'article WordPress pour modifier et trouve le bloc "Champs personnalisés" en dessous le bloc du texte principal. Si il n'y est pas visible, ouvre l'onglet "Options de l'écran" à droite en haut de la page et coche la case "Champs personnalisés."</li>
				<li>- Dans le bloc "Champs personnalisés" sélectionne "film_id" sous "Nom" et entre le chiffre de l'Id du film souhaité sous "Valeur". Si il y a déjà un champ "film_id" existant, modifie ou supprime le. Il faut pas y avoir plusieurs champs avec le même nom "film_id".</li>
			</ul>
		</p>
		<h2>Flux d'export manuelle</h2>
		<p>Le flux JSON d'export peut être vu <a href="<?php echo get_home_url( null, '/wp-json/ecranvillage-api/v2/export' ); ?>" class="button" target="_blank">ici</a> ou téléchargé <a href="<?php echo get_home_url( null, '/wp-json/ecranvillage-api/v2/export/download' ); ?>" class="button">ici</a>.</p>
	</div>

	<div id="tools" class="tab">

		<?php $nonce_url = wp_nonce_url(admin_url('admin.php?page=ecranvillage-admin'), 'ecranvillage-settings'); ?>

		<h2><?php _e('Tools'); ?></h2>
		<p>Pour améliorer la réactivité du site, les réponses de l'application Plannings sont mise en cache. Par fois, en cas de modifications des séances ou lieux sur l'application Plannings, cela peut évoquer une décalage temporaire entre l'application et le site. Au lieu d'attendre l'expiration du cache, on peut forcer un purge des caches sur différentes niveaux.</p>
		<p><strong>Videz les caches :</strong></p>
		<ul>
			<li>
				 <p>
					<a href="<?php print add_query_arg( 'purge', 'villages', $nonce_url ); ?>" class="button">Villages</a>
				 	<span class="description">Normalement mise en cache pendant 24 heures. En cas des problèmes d'affichage des lieux et salles, videz le cache de villages.</span>
				</p>
			</li>
			<li>
				<p>
					<a href="<?php print add_query_arg( 'purge', 'films', $nonce_url ); ?>" class="button">Films</a>
					<span class="description">Normalement mise en cache pendant 10 minutes. En cas des problèmes d'affichage des séances d'un nouveau film, commencez avec un purge de ce cache.</span>
				</p>
			</li>
			<li>
				 <p>
					<a href="<?php print add_query_arg( 'purge', 'seances', $nonce_url ); ?>" class="button">Séances à l'affiche et à venir</a>
					 ou <a href="<?php print add_query_arg( 'purge', 'seances-all', $nonce_url ); ?>" class="button">Tous les séances</a>
				 	<span class="description">Normalement mise en cache pendant 1 heure. En cas des problèmes d'affichage des séances plus pertinents, videz tous le caches des séances de tous les films.</span>
				</p>
			</li>
		</ul>
		<p><strong>Réinitialiser les associtations des films : </strong></p>
		<p>
			<a href="<?php print add_query_arg( 'purge', 'film-ids', $nonce_url ); ?>" class="button" onclick="javascript:return confirm('ATTENTION: Cette reinialisation remettra toutes associations manuelles à zéro au même temps que les associations automatiques! Êtes-vous sûr?')">Réinitialiser les IDs</a>
			<span class="description">Les IDs des films liés entre l'applications et le site sont tous remis à zéro. Normalement, les associations sont recréées à partir des titres des films mais si il y a un ou plusieurs films qui ne sont pas bien liés, il faut une association manuelle. ATTENTION: Cette reinialisation remettra toutes associations manuelles à zéro au même temps que les associations automatiques!</span>
		</p>
	</div>

<?php endif; ?>

	<div id="settings" class="tab">
		<h2><?php _e('Settings'); ?></h2>

		<form method="post" id="mainform" action="" enctype="multipart/form-data">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="app_url">Plannings App URL</label></th>
						<td>
							<input type="text" class="regular-text" name="app_url" id="app_url" value="<?php echo $app_url; ?>" />
							<p class="description">Adresse web principal de l'aplication Plannings.</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="app_url">Clé de l'API TMDb</label></th>
						<td>
							<input type="text" class="regular-text" name="tmdb_api_key" id="tmdb_api_key" value="<?php echo $tmdb_api_key; ?>" />
							<p class="description">Clé de l'API (v3 auth) pour l'importation des meta-données de <a href="https://www.themoviedb.org/" target="_blank">https://www.themoviedb.org/</a></p>
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit">
				<input name="save" class="button button-primary" type="submit" value="<?php _e( 'Save Changes' ); ?>" />
				<?php wp_nonce_field( 'ecranvillage-settings', '_wpnonce', true, true ); ?>
			</p>
		</form>

	</div>

	<div id="shortcodes" class="tab">
		<p>Il y a plusieurs Codes Courts disponibles:</p>

		<hr />

		<h2>[etoiles]</h2>
		<p>Utilise le shortcode <strong>[etoiles N /]</strong> dans les citations de critiques pour montrer des étoiles.</p>
		<p>Par défaut, le shortcode montre 5 étoiles grises. Le chiffre N indique le nombre des étoiles à éclairer. Par exemple [etoiles 4] va montrer quatres étoiles éclairées en jaune et une grise.</p>
		<p>Il est possible de montrer un autre total d'étoiles en ajoutent une deuxième chiffre comme par exemple [etoiles 7/10] (sept sur dix) ou des demi-étoiles comme [etoiles 3.5] (trois-et-demi sur cinq)</p>

		<hr />

<?php if ( !empty($app_url) ) : ?>

		<h2>[seances]</h2>
		<p>Utilise le shortcode <strong>[seances /]</strong> dans les articles WordPress pour montrer un tableau des séances. Par défaut, le shortcode cherche le film du même titre. Il faut que les titres du film et de l'article correspondent exactement. Au cas où le bon film n'est pas trouvé automatiquement, il y a deux méthodes pour faire montrer les bonnes séances:</p>
		<p><strong>Associer le shortcode [seances] au bon film.</strong><br>
			Ajoute au shortcode un des paramètres disponible pour forcer l'association à un film dans l'application de Plannings. Par exemple <strong>[seances id="1"]</strong> ou <strong>[seances titrefilm="COURT ECOLE ST JEAN"]</strong> montre les séances du film "COURT ECOLE ST JEAN" même si le titre je l'article ne corresponds pas au titre du film.</li>
		</p>
		<h3>Paramètres</h3>
		<dl>
			<dt><strong>id</strong></dt>
			<dd>Le <strong>Id</strong> du film pour les séances seront affiché. Peut contenir plusieurs Id's pour afficher les séances de plusieurs films ensemble.</dd>
			<dt><strong>film / titre / titrefilm</strong></dt>
			<dd>Si il n'y a pas d'Id, le film peut être trouvé par titre. Il faut correspondre exactement (attention aux minuscules/majuscules, espaces et caractères spéciaux!) au titre utilisé dans l'application Plannings.</dd>
			<dt><strong>format</strong></dt>
			<dd>Le format d'affichage. Peut être "tableau" pour afficher en format de tableau (défaut) ou "simple" pour afficher une liste simple.</dd>
			<dt><strong>align</strong></dt>
			<dd>Alignement des textes en format "simple". Peut être "left", "center", "right" ou "justify".</dd>
		</dl>
		<p>Exemple : le shortcode <code>[seances id="4882" format="simple" /]</code> cherche les séances du film avec ID 4882 dans l'application plannings et (si trouvé) montre les dans une liste.</p>
		<p>Astuce : Il n'y a pas de limite au nombre de shortcodes, avec différentes paramètres si besoin, sur une seul page. </p>

		<hr />

		<h2>[applink]</h2>
		<p>Utilise le shortcode <strong>[applink]texte ou image[/applink]</strong> pour montrer un lien vers la page principal de l'application Plannings. L'adresse du lien corresponds au <strong>Plannings App URL</strong> sous l'onglet <strong>Réglages</strong>.</p>
		<h3>Paramètres</h3>
		<dl>
			<dt><strong>title</strong></dt>
			<dd>L'atribut 'title' ou <strong>tooltip</strong> du lien est le texte affiché au survol du souris.</dd>
			<dt><strong>class</strong></dt>
			<dd>L'attribut 'class' à joindre au lien. Dépends le thème du site</dd>
			<dt><strong>target</strong></dt>
			<dd>L'attribut 'target' à joindre au lien. Par exemple "_blank" pour ouvrir le lien dans une nouvelle fenêtre du navigateur.</dd>
		</dl>
		<p>Exemples :</p>
		<p>Le shortcode de base <code>[applink /]</code> s'affiche comme <a href="<?php echo $app_url; ?>" target="_blank"><?php echo $app_url; ?></a></p>
		<p>Le shortcode <code>[applink title="agenda d'Écran Village"]voir l'agenda' ici[/applink]</code> s'affiche comme <a href="<?php echo $app_url; ?>" target="_blank" title="agenda d'Écran Village">voir l'agenda' ici</a> (survolle pour voir le tooltip).</p>
		<p>Le shortcode <code>[applink class="button"]AGENDA[/applink]</code> peut s'afficher comme <a href="<?php echo $app_url; ?>" class="button" target="_blank">AGENDA</a> mais cela dépends le thème du site.</p>

<?php endif; ?>

	</div>

</div>
<script type="text/javascript">
jQuery(document).on('ready', function(){
	jQuery('div.tab').hide();
	jQuery('a.<?php echo $tab ?>').addClass('nav-tab-active');
	jQuery('#<?php echo $tab ?>').show();
	jQuery('a.nav-tab').on('click', function(e){
		e.preventDefault();
		jQuery('a.nav-tab-active').removeClass('nav-tab-active');
		jQuery(this).addClass('nav-tab-active');
		var href = jQuery(this).attr("href");
		jQuery('div.tab').hide();
		jQuery(href).show();
	});
});
</script>
