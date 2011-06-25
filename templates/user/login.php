<? $form = $view->getHelper('form'); // Get Form helper ?>

<p>Please login to access MyMovieLibrary!</p>

<div form="form-container" id="login_form">
	<?= $form->open() ?>
	<div class="form-field"><?= $form->text('email', 'Email Address') ?></div>
	<div class="form-field"><?= $form->password('password', 'Password') ?></div>
	<div class="form-buttons"><?= $form->button('submit', 'submit', 'Login') ?></div>
	<?= $form->close() ?>
</div>
