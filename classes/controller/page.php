<?php defined('App::NAME') OR die('You cannot execute this script.');

abstract class Controller_Page extends Controller
{
	protected $_template = 'page';

	public function beforeAction()
	{
		$user = $this->getUser();
		if ( ! $user->isAuthenticated() AND $this->getRequest()->getAction() !== 'login')
		{
			$this->_request->redirect('user/login');
		}
	}

	public function afterAction()
	{
		$this->setResponse($this->getContainer()->getView($this->_template)
			->set('title',   'TEMPORARY')
			->set('content', $this->getResponse())
			->set('message', $this->getRequest()->getUserMessage())
			->render()
		);
	}

	public function getUser()
	{
		$user = $this->getContainer()->getUserModel();
		if ($logged_in_user_id = $this->getContainer()->getSession()->get('user.id'))
		{
			$user->read($logged_in_user_id);
		}

		return $user;
	}
}
