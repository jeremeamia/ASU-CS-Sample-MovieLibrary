<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Admin extends Controller_Page
{
	protected $_template = 'admin';

	public function getUser()
	{
		return NULL;
	}

	public function beforeAction()
	{
		$config = $this->getContainer()->getConfig();

		$is_http_auth = (isset($_SERVER['PHP_AUTH_USER']) AND isset($_SERVER['PHP_AUTH_PW']));
		$admin_user_correct = ($_SERVER['PHP_AUTH_USER'] == $config->get('admin', 'username'));
		$admin_pass_correct = ($_SERVER['PHP_AUTH_PW'] == $config->get('admin', 'password'));

		if ( ! $is_http_auth OR ! $admin_user_correct OR ! $admin_pass_correct)
		{
			header('WWW-Authenticate: Basic realm="'.$config->get('site', 'title').'"');
			header('HTTP/1.0 401 Unauthorized');
			echo 'You cannot access the Admin Tools for '.$config->get('site', 'title').'.';
			exit(0);
		}

		// Create a view class that corresponds to this request
		$this->view = $this->getContainer()->getView($this->getAction());
	}

	public function actionCreateuser()
	{
		if ($data = $this->getRequest()->post())
		{
			$user = $this->getContainer()
				->getModel('user')
				->set($data);

			if ($user->isValid())
			{
				$user->create();
				$this->getRequest()->setUserMessage('success', 'There user, '.$user->getFullName().', was added.');
			}
			else
			{
				$this->getRequest()->setUserMessage('error', 'There was a problem adding the user.');
			}
		}
	}

	public function actionRununittests()
	{
		// Require the test suite for running the tests
		require 'tests/run.php';

		// Style the testing output to be sans-serif for easier reading
		echo '<style>body {font-family: sans-serif;}</style>';

		// Terminate the application and trigger the tests to run
		exit;
	}
}
