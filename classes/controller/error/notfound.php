<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Error_NotFound extends Controller_Page
{
	protected $_title = "404 - Page Not Found";

	public function execute()
	{
		$this->setResponse($this->getContainer()->getView('error/notfound'));
	}
}
