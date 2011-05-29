<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Home extends Controller_Page
{
	protected $_title = 'Home';

	public function execute()
	{
		$this->setResponse($this->getContainer()->build('view', 'home')
			->set('movies', $this->getContainer()->build('model', 'movie')->readAll())
		);
	}
}
