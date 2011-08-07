<?php defined('App::NAME') OR die('You cannot execute this script.');

// Import the testing library
require_once dirname(dirname(__FILE__)).'/simpletest/autorun.php';

// Setup the test suite class
class MovieLibraryTests extends TestSuite
{
	public function MovieLibraryTests()
	{
		// Create the test suite
		$this->TestSuite('Movie Library Tests');

		// Add the unit test classes from the tests directory
		foreach (glob(dirname(__FILE__).'/test_*.php') as $filename)
		{
			parent::addFile($filename);
		}
	}
}
