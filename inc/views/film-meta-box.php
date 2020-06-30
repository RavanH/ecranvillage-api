<input type="hidden" name="film_meta_nonce" value="<?php echo $nonce ?>" />
<table class="form-table">
	<tr>
		<th>
			<label>Durée du film</label>
		</th>
		<td>
			<input type="text" name="duree" class="small-text" value="<?php echo $duree; ?>">
			<!-- classes: .small-text .regular-text .large-text -->
		</td>
	</tr>
	<tr>
		<th>
			<label>Remarques</label>
		</th>
		<td>
			<input type="text" name="info" class="large-text" value="<?php echo $info; ?>">
			<input type="text" name="info2" class="large-text" value="<?php echo $info2; ?>">
			<input type="text" name="info3" class="large-text" value="<?php echo $info3; ?>">
			<!-- classes: .small-text .regular-text .large-text -->
			<p class="description">Toutes autres remarques comme prix, avertissement de violance etc.</p>
		</td>
	</tr>
	<tr>
		<th>
			<label>Bande Annonce</label>
		</th>
		<td>
			<input type="url" name="trailer" class="large-text" value="<?php echo $trailer_url; ?>">
			<!-- classes: .small-text .regular-text .large-text -->
			<p class="description">Adresse de la page Youtube ou Viméo.</p>
		</td>
	</tr>
	<tr>
		<th>
			<label>AlloCiné</label>
		</th>
		<td>
			<input type="text" name="allocine" class="large-text" value="<?php echo $allocine; ?>">
			<!-- classes: .small-text .regular-text .large-text -->
			<p class="description">Adresse de la fiche du film sur <a href="http://www.allocine.fr" target="_blank">AlloCiné</a>.</p>
		</td>
	</tr>
	<tr>
		<th>
			<label>TMDb</label>
		</th>
		<td>
			<input type="text" name="tmdb" class="large-text" value="<?php echo $tmdb; ?>">
			<!-- classes: .small-text .regular-text .large-text -->
			<p class="description">Adresse de la fiche du film sur <a href="https://www.themoviedb.org/" target="_blank">The Movie Database</a>.</p>
		</td>
	</tr>
	<tr>
		<th>
			<label>IMDb</label>
		</th>
		<td>
			<input type="text" name="imdb" class="large-text" value="<?php echo $imdb; ?>">
			<!-- classes: .small-text .regular-text .large-text -->
			<p class="description">Adresse de la fiche du film sur l'<a href="https://www.imdb.com" target="_blank">Internet Movie Database</a>.</p>
		</td>
	</tr>
	<tr>
		<th>
			<label>Écran Village Plannings ID</label>
		</th>
		<td>
			<input type="number" name="film_id" class="small-text" value="<?php echo $film_id; ?>">
			<!-- classes: .small-text .regular-text .large-text -->
			<span class="description">ID du film dans <a href="<?php echo get_option( 'ecranvillage_app_url' ); ?>" target="_blank">Plannings</a>.</span>
		</td>
	</tr>
</table>
