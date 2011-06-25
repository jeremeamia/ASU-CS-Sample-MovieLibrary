<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Movie extends Controller_Page
{
	public function actionIndex()
	{
		$movies = $this->getContainer()
			->getModel('movie')
			->getMoviesOwnedByUser($this->getUser());

		$this->view
			->set('title', 'My Movies')
			->set('movies', $movies);
	}

	public function actionLookup()
	{
		$results = array();
		if ($search = $this->getRequest()->post('search'))
		{
			// Perform movie search via Netflix
			$results = $this->getContainer()
				->getNetflix()
				->lookup($search);
		}

		$this->view
			->set('title', 'Lookup a Movie')
			->set('results', $results);
	}

	public function actionAdd()
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

					$this->getRequest()->setUserMessage('success', 'You have added the movie "'.$movie->get('title').'" to your library.');
					$this->getRequest()->redirect(array('movie', 'index'));
				}
				else
				{
					$this->getRequest()->setUserMessage('error', 'The movie could not be added to your library.');
					$this->getRequest()->redirect(array('movie', 'lookup'));
				}
			}
		}

		$this->getRequest()->redirect('movie/lookup');
	}

	public function actionRemove()
	{
		$movie = $this->getContainer()
			->getModel('movie')
			->read($this->getRequest()->getResourceId());

		if ( ! $movie->isLoaded())
		{
			$this->getRequest()->setUserMessage('error', 'The movie you are trying to remove is not in your library.');
			$this->getRequest()->redirect(array('movie', 'index'));
		}

		if ($this->getRequest()->post())
		{
			try
			{
				$this->getContainer()
					->getModel('ownership')
					->unlinkMovieFromUser($movie, $this->getUser());

				$this->getRequest()->setUserMessage('success', 'The movie "'.$movie->get('title').'" has been removed from your library.');
				$this->getRequest()->redirect(array('movie', 'index'));
			}
			catch (Exception $ex)
			{
				$this->getRequest()->setUserMessage('error', 'The movie "'.$movie->get('title').'"could not be removed from your library. It was not in there to begin with.');
				$this->getRequest()->redirect(array('movie', 'index'));
			}
		}

		$this->view
			->set('title', 'Remove Movie')
			->set('movie', $movie);
	}
}
