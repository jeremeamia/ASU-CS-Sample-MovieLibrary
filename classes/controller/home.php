<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Home extends Controller_Page
{
	protected $_title = 'Home';

	public function execute()
	{
		$movies = $this->getContainer()->getModel('movie')->getMoviesOwnedByUser($this->getUser());

		$this->setResponse($this->getContainer()->getView('home')
			->set('movies', $movies)
		);
	}
}
