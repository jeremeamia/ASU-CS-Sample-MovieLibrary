<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Login extends Controller_Page
{
	protected $_title = 'Login';

	public function execute()
	{
		if ($this->getUser()->isAuthenticated())
		{
			$this->getRequest()->redirect('home');
		}

		if ($this->getRequest()->post())
		{
			$email = $this->getRequest()->post('email');
			$password = $this->getRequest()->post('password');
			$user = $this->getContainer()->getUserModel();
			if ($user->login($email, $password))
			{
				$this->getRequest()->setUserMessage('success', 'You are now logged in!');
				$this->getRequest()->redirect('home');
			}
			else
			{
				$this->getRequest()->setUserMessage('error', 'There was a problem with your login credentials. Please try again.');
			}
		}

		$this->setResponse($this->getContainer()->getView('login'));
	}
}
