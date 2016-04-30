<?php
$app_url = "http://planning-ecranvillage.herokuapp.com"; // http://149.202.62.195:3000/admin/film/import
$exp_url = "/wp-json/ecranvillage-api/v2/export/download";
?>

<div class="wrap">
	<h1>Plannings</h1>
	<p><strong>1.</strong> <a href="<?php echo $exp_url; ?>" class="button button-primary" target="_blank"><?php _e('Download Export File'); ?></a> et sauvegarde le. Ce fichier contient les films des catégories "À l'affiche" et "À vernir" en format JSON.</p>
	<p><strong>2.</strong> Ouvre la page <a href="<?php echo $app_url . '/admin/film/import'; ?>" class="button button-primary" target="plannings"><?php _e('Import'); ?></a> de l'application de Plannings.</p>
	<p><strong>3.</strong> Choisis le fichier JSON dans l'application sous "Charger l'archive > Archive des données" et clique "Enregistrer" pour importer les films à l'affiche et à venir. Coche la case "Actualiser si il existe" pour éviter la création des doubles entrées au cas où un film existe déja dans la base de données.</p>
	<p><strong>4.</strong> Crée des nouvelles <a href="<?php echo $app_url . '/admin/seance/new'; ?>" class="button button-primary" target="plannings">Séances</a> et/ou leurs associations aux Villages et Films dans l'application.</p>
	<h2><?php _e('Shortcode'); ?> [seances]</h2>
	<p>Utilise le shortcode <strong>[seances]</strong> dans les articles WordPress pour montrer un tableau des séances. Par défaut, le shortcode cherche le film du même titre. Il faut que les titres du film et de l'article correspondent exactement. Au cas où le bon film n'est pas trouvé automatiquement, il y a deux méthodes pour faire montrer les bonnes séances:</p>
        <ol>
                <li><strong>Associer l'article au bon film.</strong>
                        <ul>
                                <li>- Ouvre la page <a href="<?php echo $app_url . '/admin/film'; ?>" target="plannings">Listing des Films</a> dans l'application Plannings et note le chiffre de l'<strong>Id</strong> du film souhaité.</li>
                                <li>- Reviens sur l'article WordPress pour modifier et trouve le bloc "Champs personnalisés" en dessous le bloc du texte principal. Si il n'y est pas visible, ouvre l'onglet "Options de l'écran" à droite en haut de la page et coche la case "Champs personnalisés."</li>
                                <li>- Dans le bloc "Champs personnalisés" sélectionne "film_id" sous "Nom" et entre le chiffre de l'Id du film souhaité sous "Valeur". Si il y a déjà un champ "film_id" existant, modifie ou supprime le. Il faut pas y avoir plusieurs champs avec le même nom "film_id".</li>
                        </ul>
                </li>
                <li><strong>Associer le shortcode [seances] au bon film.</strong><br>
                Ajoute au shortcode un des paramètres disponible pour forcer l'association à un film dans l'application de Plannings. Par exemple <strong>[seances id="1"]</strong> ou <strong>[seances titrefilm="COURT ECOLE ST JEAN"]</strong> montre les séances du film "COURT ECOLE ST JEAN" même si le titre je l'article ne corresponds pas au titre du film.</li>
        </ol>
	<p><strong>Paramètres</strong></p>
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
<!--	<p><a href="<?php echo $exp_url; ?>" class="button button-primary" target="_blank">< ?php _e('Download Export File'); ?></a> puis <a href="< ?php echo $app_url; ?>" class="button button-primary" target="_blank">< ?php _e('Import'); ?></a></p> -->
	<p>Astuce: Il n'y a pas de limite au nombre de shortcodes, avec différentes paramètres si besoin, sur une seul page. </p>
	<h2><?php _e('Tools'); ?></h2>
	<p>Bientôt des boutons pour [vider le cache des films], [vider le cache des lieux] et [vider tous les caches des séances] ici...</p>
	
<!--	<iframe src="<?php echo $app_url . '/admin/film/import'; ?>" style="width: 100%; height: 1220px; border-radius: 6px"></iframe> -->

</div>
