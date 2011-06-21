<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Movie_Add extends Controller_Page
{
	protected $_title = 'Home';

	public function execute()
	{
		$results = array();
		if ($this->_request->post())
		{
			// Perform movie search via Netflix (STAGE 1)
			if ($search = $this->_request->post('search'))
			{
				$results = $this->getContainer()->getNetflix()->lookup($search);
			}

			// Lookup chosen movie and add to library (STAGE 2)
			elseif ($netflix_id = $this->_request->post('movie'))
			{
				// First, look it up in our system
				$movie = $this->getContainer()->getModel('movie')
					->readFirst('`netflix_id` = "'.$netflix_id.'"');

				// If it doesn't exist in our system, let's add it
				if ($movie === NULL)
				{
					$new_movie = $this->getContainer()->getModel('movie')
						->set((array) $this->getContainer()->getNetflix()->getMovie($netflix_id));
					if ($new_movie->isValid())
					{
						$movie = $new_movie->create();
					}
				}

				// Now let's link the movie to the current user
				if ($movie !== NULL)
				{
					$ownership = $this->getContainer()->getModel('ownership');
					$ownership->linkMovieToUser($movie, $this->getUser());
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
