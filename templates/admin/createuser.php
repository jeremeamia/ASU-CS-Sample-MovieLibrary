<? $form = $view->getHelper('form'); // Get Form helper ?>

<p>Enter the user's information below:</p>

<div form="form-container" id="admin_createuser_form">
	<?= $form->open() ?>
		<div class="form-field"><?= $form->text('first_name', 'First Name') ?></div>
		<div class="form-field"><?= $form->text('last_name', 'Last Name') ?></div>
		<div class="form-field"><?= $form->text('email', 'Email Address') ?></div>
		<div class="form-field"><?= $form->text('password', 'Password') ?></div>
		<div class="form-buttons"><?= $form->button('submit', 'submit', 'Create User') ?></div>
	<?= $form->close() ?>
</div>
