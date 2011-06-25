<? $html = $view->getHelper('html'); // Get HTML helper ?>
<? $form = $view->getHelper('form'); // Get Form helper ?>

<p>Are you sure you want to remove the movie <i><?= $movie->get('title') ?></i> from your library?</p>

<?= $html->image($movie->get('box_art'), $movie->get('title')) ?>

<div form="form-container" id="movie_remove_form">
	<?= $form->open() ?>
		<div class="form-buttons">
			<?= $form->button('submit', 'submit', 'Remove '.$movie->get('title')) ?>
			<?= $html->link('Cancel', array('movie', 'index')) ?>
		</div>
	<?= $form->close() ?>
</div>
