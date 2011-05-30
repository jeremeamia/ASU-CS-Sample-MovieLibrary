<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Home extends Controller_Page
{
	protected $_title = 'Home';

	public function execute()
	{
		$movies = $this->getContainer()->build('model', 'movie')->getMoviesOwnedByUser($this->getUser());

		$this->setResponse($this->getContainer()->build('view', 'home')
			->set('movies', $movies)
		);
	}
}
