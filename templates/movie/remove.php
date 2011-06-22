<? $html = $this->getHelper('html'); // Get HTML helper ?>
<? $form = $this->getHelper('form'); // Get Form helper ?>

<p>Are you sure you want to remove the movie <i><?= $movie->get('title') ?></i> from your library?</p>

<?= $html->image($movie->get('box_art'), $movie->get('title')) ?>

<div form="form-container" id="movie_remove_form">
	<?= $form->open() ?>
		<div class="form-buttons">
			<?= $form->button('submit', 'submit', 'Remove '.$movie->get('title')) ?>
			<?= $html->link('Cancel', 'home') ?>
		</div>
	<?= $form->close() ?>
</div>
