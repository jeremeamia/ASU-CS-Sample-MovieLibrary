<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Movie_Add extends Controller_Page
{
	protected $_title = 'Home';

	public function execute()
	{
		$results = array();
		if ($this->_request->post())
		{
			if ($movie_id = $this->_request->post('movie'))
			{
				$movie_data = (array) $this->getContainer()->build('netflix')->getMovie($movie_id);
				$movie_data['categories'] = implode(', ', $movie_data['categories']);
				$movie = $this->getContainer()->build('model', 'movie')->set($movie_data);
				if ($movie->isValid())
				{
					$movie->create();
					$this->_request->setUserMessage('success', 'You have added the movie "'.$movie->get('title').'".');
				}
				else
				{
					$this->_request->setUserMessage('error', 'The movie "'.$movie->title.'" could not be added.');
				}
				$this->_request->redirect('home');
			}
			elseif ($search = $this->_request->post('search'))
			{
				$results = $this->getContainer()->build('netflix')->lookup($search);
			}
		}

		$this->setResponse($this->getContainer()->build('view', 'movie/add')
			->set('results', $results)
		);
	}
}
