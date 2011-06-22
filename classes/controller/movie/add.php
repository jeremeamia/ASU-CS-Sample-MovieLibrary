<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Movie_Add extends Controller_Page
{
	public function execute()
	{
		if ($this->_request->post())
		{
			$netflix = $this->getContainer()->getNetflix();

			// Lookup chosen movie and add to library
			if ($netflix_id = $this->_request->post('movie'))
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
					$this->_request->redirect('home');
				}
				else
				{
					$this->_request->setUserMessage('error', 'The movie could not be added to your library.');
					$this->_request->redirect('movie/lookup');
				}
			}
		}

		$this->_request->redirect('movie/lookup');
	}
}
