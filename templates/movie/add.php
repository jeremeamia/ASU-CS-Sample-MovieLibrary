<? if ($results): ?>
	<p>We've looked up the closest matches for the movie title you entered. Please select the movie you would like to add to your library.</p>

	<div form="form-container" id="movie_add_form">
	<?= $helpers->form->open() ?>
	<? foreach ($results as $movie): ?>
		<div class="form-field" style="display: inline-block; margin: 10px;">
			<?= $movie->title ?> (<?= $movie->year ?>) [<?= $movie->mpaa_rating ?>]<br>
			<?= $helpers->form->image('movie', $movie->box_art, $movie->netflix_id, $movie->title) ?>
		</div>
	<? endforeach; ?>
	<?= $helpers->form->close() ?>
	</div>

	<p>Not the movie you were looking for? Try another lookup.</p>
<? else: ?>
	<p>Enter the name of the movie you'd like to add to your library. We'll look up the details for you.</p>
<? endif; ?>

<div form="form-container" id="movie_lookup_form">
	<?= $helpers->form->open() ?>
	<div class="form-field"><?= $helpers->form->text('search', 'Movie Title') ?></div>
	<div class="form-buttons"><?= $helpers->form->button('submit', 'submit', 'Lookup') ?></div>
	<?= $helpers->form->close() ?>
</div>
