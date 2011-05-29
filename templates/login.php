<p>Please login to access MyMovieLibrary!</p>

<div form="form-container" id="login_form">
	<?= $helpers->form->open() ?>
	<div class="form-field"><?= $helpers->form->text('email', 'Email Address') ?></div>
	<div class="form-field"><?= $helpers->form->password('password', 'Password') ?></div>
	<div class="form-buttons"><?= $helpers->form->button('submit', 'submit', 'Login') ?></div>
	<?= $helpers->form->close() ?>
</div>
