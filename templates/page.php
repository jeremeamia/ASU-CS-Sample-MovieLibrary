<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title><?= $title ?> - <?= $config->get('site', 'title') ?></title>
	</head>
	<body style="font-family: sans-serif;">
		<h1><?= $title ?></h1>

		<?= $message ?>

		<hr />
	
		<?= $content ?>

		<hr />

		<p>Copyright &copy; 2011 Jeremy Lindblom.</p>
	</body>
</html>
