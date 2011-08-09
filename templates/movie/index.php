<p>Welcome to My Movie Library!</p>

<strong>Movies in Library:</strong>
<ul>
	<? foreach ($movies as $movie): ?>
		<li><?= $movie->get('title') ?> (<?= $movie->get('year') ?>) <?= $view->getHelper('html')->link('remove', array('movie', 'remove', $movie->get('id'))) ?></li>
	<? endforeach; ?>
	<li><strong><?= $view->getHelper('html')->link('+ Add a Movie', array('movie', 'lookup')) ?></strong></li>
</ul>
