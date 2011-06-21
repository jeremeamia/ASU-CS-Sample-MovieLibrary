<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Login extends Controller_Page
{
	protected $_title = 'Login';

	public function execute()
	{
		if ($this->getUser()->isAuthenticated())
		{
			$this->_request->redirect('home');
		}

		if ($this->_request->post())
		{
			$email = $this->_request->post('email');
			$password = $this->_request->post('password');
			$user = $this->getContainer()->getUserModel();
			if ($user->login($email, $password))
			{
				$this->_request->setUserMessage('success', 'You are now logged in!');
				$this->_request->redirect('home');
			}
			else
			{
				$this->_request->setUserMessage('error', 'There was a problem with your login credentials. Please try again.');
			}
		}

		$this->setResponse($this->getContainer()->getView('login'));
	}
}
