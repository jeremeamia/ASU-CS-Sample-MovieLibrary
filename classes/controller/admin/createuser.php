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
				
				$this->_request->setUserMessage('success', 'There user, '.$user->full_name().', was added.');
			}
			else
			{
				$this->_request->setUserMessage('error', 'There was a problem adding the user.');
			}
		}

		$this->setResponse($this->getContainer()->getView('admin/createuser'));
	}
}
