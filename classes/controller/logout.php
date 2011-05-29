<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Logout extends Controller_Page
{
	public function execute()
	{
		$user = $this->getUser()->logout();

		$this->_request->setUserMessage('success', 'You are now logged out!');
		$this->_request->redirect('login');
	}
}
