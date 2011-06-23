<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Logout extends Controller_Page
{
	public function execute()
	{
		$this->getUser()->logout();

		$this->getRequest()->setUserMessage('success', 'You are now logged out!');
		$this->getRequest()->redirect('login');
	}
}
