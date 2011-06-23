<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Movie_Remove extends Controller_Page
{
	protected $_title = 'Remove a Movie';

	public function execute()
	{
		$movie = $this->getContainer()
			->getModel('movie')
			->read($this->getRequest()->getResourceId());

		if ( ! $movie->isLoaded())
		{
			$this->getRequest()->setUserMessage('error', 'The movie you are trying to remove is not in your library.');
			$this->getRequest()->redirect('home');
		}

		if ($this->getRequest()->post())
		{
			try
			{
				$this->getContainer()
					->getModel('ownership')
					->unlinkMovieFromUser($movie, $this->getUser());

				$this->getRequest()->setUserMessage('success', 'The movie "'.$movie->get('title').'" has been removed from your library.');
				$this->getRequest()->redirect('home');
			}
			catch (Exception $ex)
			{
				$this->getRequest()->setUserMessage('error', 'The movie "'.$movie->get('title').'"could not be removed from your library. It was not in there to begin with.');
				$this->getRequest()->redirect('home');
			}
		}

		$this->setResponse($this->getContainer()->getView('movie/remove')
			->set('movie', $movie)
		);
	}
}
