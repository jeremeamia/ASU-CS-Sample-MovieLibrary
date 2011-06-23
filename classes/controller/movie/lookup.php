<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Movie_Lookup extends Controller_Page
{
	protected $_title = 'Lookup a Movie to Add';

	public function execute()
	{
		$results = array();
		if ($search = $this->getRequest()->post('search'))
		{
			// Perform movie search via Netflix
			$results = $this->getContainer()
				->getNetflix()
				->lookup($search);
		}

		$this->setResponse($this->getContainer()->getView('movie/lookup')
			->set('results', $results)
		);
	}
}
