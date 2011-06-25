<?php defined('App::NAME') OR die('You cannot execute this script.');

class Controller_Error extends Controller_Page
{
	public function actionInternal()
	{
		$this->setResponse($this->getContainer()->getView('error/internal'));
	}

	public function actionNotfound()
	{
		$this->setResponse($this->getContainer()->getView('error/notfound'));
	}
}
