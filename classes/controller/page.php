<?php defined('App::NAME') OR die('You cannot execute this script.');

abstract class Controller_Page extends Controller
{
	protected $_title = NULL;

	public function preExecute()
	{
		$user = $this->getUser();
		if ( ! $user->isAuthenticated() AND ! $this instanceof Controller_Login)
		{
			$this->_request->redirect('login');
		}
	}

	public function postExecute()
	{
		$content = $this->_response;
		$this->_response = $this->_container->getView('page')
			->set('title',   $this->_title)
			->set('content', $content)
			->set('message', $this->getMessage())
			->render();
	}

	public function getUser()
	{
		$user = $this->_container->getModel('user');
		if ($logged_in_user_id = $this->_container->getSession()->get('user.id'))
		{
			$user->read($logged_in_user_id);
		}

		return $user;
	}

	public function getMessage()
	{
		$message = $this->_request->getUserMessage();
		return $message ? $message : NULL;
	}
}
