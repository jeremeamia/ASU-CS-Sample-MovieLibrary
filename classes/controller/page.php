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

	public function getResponse()
	{
		return $this->getContainer()->getView('page')
			->set('title',   $this->_title)
			->set('content', parent::getResponse())
			->set('message', $this->getMessage())
			->render();
	}

	public function getUser()
	{
		$user = $this->getContainer()->getModel('user');
		if ($logged_in_user_id = $this->getContainer()->getSession()->get('user.id'))
		{
			$user->read($logged_in_user_id);
		}

		return $user;
	}

	public function getMessage()
	{
		$message = $this->getRequest()->getUserMessage();
		if (empty($message))
			return FALSE;
		else
			return '<p class="message '.$message['type'].'">'.$message['message'].'</p>';
	}
}
