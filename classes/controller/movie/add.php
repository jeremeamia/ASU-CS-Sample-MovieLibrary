<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Movie_Add extends Controller_Page
{
	protected $_title = 'Add a Movie';

	// @TODO turn this into two actions
	public function execute()
	{
		$results = array();
		if ($this->_request->post())
		{
			$netflix = $this->getContainer()->getNetflix();

			// Perform movie search via Netflix (STAGE 1)
			if ($search = $this->_request->post('search'))
			{
				$results = $netflix->lookup($search);
			}

			// Lookup chosen movie and add to library (STAGE 2)
			elseif ($netflix_id = $this->_request->post('movie'))
			{
				// Let's find the movie
				$movie = $this->getContainer()
					->getModel('movie')
					->getMovieFromNetflix($netflix, $netflix_id);

				// Now let's link the movie to the current user
				if ($movie !== NULL)
				{
					$ownership = $this->getContainer()
						->getModel('ownership')
						->linkMovieToUser($movie, $this->getUser());

					$this->_request->setUserMessage('success', 'You have added the movie "'.$movie->get('title').'" was added to your library.');
				}
				else
				{
					$this->_request->setUserMessage('error', 'The movie could not be added to your library.');
				}

				$this->_request->redirect('home');
			}
		}

		$this->setResponse($this->getContainer()->getView('movie/add')
			->set('results', $results)
		);
	}
}
