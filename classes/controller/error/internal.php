<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Error_Internal extends Controller_Page
{
	protected $_title = "500 - Internal Server Error";

	public function execute()
	{
		$this->setResponse($this->getContainer()->build('view', 'error/notfound'));
	}
}
