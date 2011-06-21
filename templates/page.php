<? $html = $this->getHelper('html'); // Get HTML helper ?>

<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title><?= $title ?> - <?= $this->getConfigValue('site', 'title') ?></title>
	</head>
	<body style="font-family: sans-serif;">
		<h1><?= $html->link($this->getConfigValue('site', 'title')) ?></h1>
		<h2><?= $title ?></h2>

		<? if ($message): ?>
			<p class="message <?= $message['type']?>"><?= $message['message'] ?></p>
		<? endif; ?>

		<hr />

		<?= $content ?>

		<hr />

		<p>Copyright &copy; 2011 Jeremy Lindblom.</p>
	</body>
</html>
