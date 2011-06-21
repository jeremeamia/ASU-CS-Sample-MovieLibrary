<? $html = $this->getHelper('html'); // Get HTML helper ?>

<p>Welcome to My Movie Library!</p>

<strong>Movies in Library:</strong>
<ul>
	<? foreach ($movies as $movie): ?>
		<li><?= $movie->get('title') ?> (<?= $movie->get('year') ?>)</li>
	<? endforeach; ?>
	<li><?= $html->link('+ Add a Movie', 'movie/add') ?></li>
</ul>
