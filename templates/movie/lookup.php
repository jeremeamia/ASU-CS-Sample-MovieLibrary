<? $form = $view->getHelper('form'); // Get Form helper ?>
<? $html = $view->getHelper('html'); // Get HTML helper ?>

<script>
var submitMovie = function(netflix_id) {
	// Quick JavaScript hack to make the form submit when clicking the movie box art image
	document.getElementById('movie').value = netflix_id;
	document.forms[0].submit();
};
</script>

<? if ($results): ?>
	<p>We've looked up the closest matches for the movie title you entered. Please select the movie you would like to add to your library.</p>

	<div form="form-container" id="movie_select_form">
		<?= $form->open(array('movie', 'add')) ?>
			<?= $form->hidden('movie') ?>
			<? foreach ($results as $movie): ?>
			<div class="form-field" style="display: inline-block; margin: 10px;">
				<div class="movie-title"><?= str_replace(': ', ':<br>', $movie->title) ?></div>
				<div class="movie-art"><?= $html->image($movie->box_art, 'Cover for '.$movie->title, array('onclick' => "submitMovie('{$movie->netflix_id}');")) ?></div>
				<div class="movie-year"><span>Year</span><?= $movie->year ?></div>
				<div class="movie-mpaa"><span>MPAA</span><?= $movie->mpaa_rating ?></div>
				<div class="movie-rating"><span>Rating</span><?= $movie->user_rating ?></div>
			</div>
			<? endforeach; ?>
		<?= $form->close() ?>
	</div>

	<p>Not the movie you were looking for? Try another lookup.</p>
<? else: ?>
	<p>Enter the name of the movie you'd like to add to your library. We'll look up the details for you.</p>
<? endif; ?>

<div form="form-container" id="movie_lookup_form">
	<?= $form->open() ?>
		<div class="form-field"><?= $form->text('search', 'Movie Title') ?></div>
		<div class="form-buttons"><?= $form->button('submit', 'submit', 'Lookup') ?></div>
	<?= $form->close() ?>
</div>
