<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Admin_CreateUser extends Controller_Admin
{
	protected $_title = 'Create User';

	public function execute()
	{
		if ($data = $this->getRequest()->post())
		{
			$user = $this->getContainer()
				->getUserModel()
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

		$this->setResponse($this->getContainer()->getView('admin/createuser'));
	}
}
