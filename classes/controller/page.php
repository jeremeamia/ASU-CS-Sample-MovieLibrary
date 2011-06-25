<?php defined('App::NAME') OR die('You cannot execute this script.');

abstract class Controller_Page extends Controller
{
	public $view;
	protected $_template = 'page';

	public function beforeAction()
	{
		// Make sure the user is authenticated
		$user = $this->getUser();
		if ( ! $user->isAuthenticated() AND $this->getAction() !== 'user/login')
		{
			$this->_request->redirect(array('user', 'login'));
		}

		// Create a view class that corresponds to this request
		$this->view = $this->getContainer()->getView($this->getAction());
	}

	public function afterAction()
	{
		// Insert this view inside an outer page layout view
		$layout = $this->getContainer()->getView($this->_template)
			->set('title',   $this->view->get('title', 'My Movie Library'))
			->set('class',   'page-'.str_replace('/', '-', $this->getAction()))
			->set('user',    $this->getUser())
			->set('content', $this->view->render())
			->set('message', $this->getRequest()->getUserMessage());

		// Set the response
		$this->setResponse($layout);
	}

	public function getAction()
	{
		return $this->getRequest()->getController().'/'.$this->getRequest()->getAction();
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
}
