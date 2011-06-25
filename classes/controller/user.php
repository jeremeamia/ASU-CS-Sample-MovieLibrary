<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Login extends Controller_Page
{
	public function actionLogin()
	{
		if ($this->getUser()->isAuthenticated())
		{
			$this->getRequest()->redirect('movies/index');
		}

		if ($this->getRequest()->post())
		{
			$email = $this->getRequest()->post('email');
			$password = $this->getRequest()->post('password');
			$user = $this->getContainer()->getUserModel();
			if ($user->login($email, $password))
			{
				$this->getRequest()->setUserMessage('success', 'You are now logged in!');
				$this->getRequest()->redirect('movies/index');
			}
			else
			{
				$this->getRequest()->setUserMessage('error', 'There was a problem with your login credentials. Please try again.');
			}
		}

		$this->setResponse($this->getContainer()->getView('user/login'));
	}

	public function actionLogout()
	{
		$this->getUser()->logout();

		$this->getRequest()->setUserMessage('success', 'You are now logged out!');
		$this->getRequest()->redirect('user/login');
	}
}
