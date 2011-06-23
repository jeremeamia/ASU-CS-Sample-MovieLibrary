<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Movie_Add extends Controller_Page
{
	public function execute()
	{
		if ($this->getRequest()->post())
		{
			$netflix = $this->getContainer()->getNetflix();

			// Lookup chosen movie and add to library
			if ($netflix_id = $this->getRequest()->post('movie'))
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

					$this->getRequest()->setUserMessage('success', 'You have added the movie "'.$movie->get('title').'" was added to your library.');
					$this->getRequest()->redirect('home');
				}
				else
				{
					$this->getRequest()->setUserMessage('error', 'The movie could not be added to your library.');
					$this->getRequest()->redirect('movie/lookup');
				}
			}
		}

		$this->getRequest()->redirect('movie/lookup');
	}
}
