<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title><?= $title ?> - <?= $view->getConfigValue('site', 'title') ?></title>
		<?= $view->getHelper('html')->stylesheet('assets/boilerplate.css') ?>
		<?= $view->getHelper('html')->stylesheet('assets/styles.css') ?>
		<!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
	</head>
	<body class="<?= $class ?>">
		<div class="wrapper">
			<header>
				<hgroup class="titles">
					<h1><?= $view->getHelper('html')->link($view->getConfigValue('site', 'title')) ?></h1>
					<h2><?= $title ?></h2>
				</hgroup>

				<aside class="authentication-status">
				<? if ($user->isLoaded()): ?>
					<p>
						You are logged in as <?= $user->getFullName() ?>.
						( <?= $view->getHelper('html')->link('log out', array('user' ,'logout')) ?> )
					</p>
				<? endif; ?>
				</aside>

				<? if ($message): ?>
					<p class="message <?= $message['type']?>"><?= $message['message'] ?></p>
				<? endif; ?>
			</header>

			<section class="main" role="main">
				<?= $content ?>
			</section>

			<footer>
				<p class="copyright">Copyright &copy; 2011 <?= $view->getHelper('html')->link('Jeremy Lindblom', 'http://github.com/jeremeamia') ?>. For the use of the ASU CSE department.</p>
			</footer>
		</div>
	</body>
</html>
