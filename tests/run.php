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

		// Add the unit test classes
		$this->loadTestFor('Helper_Collection');
	}

	public function loadTestFor($class)
	{
		parent::addFile(dirname(__FILE__).'/test_'.strtolower($class).'.php');
	}
}

// Make the output sans-serif for easier reading
echo '<style>body {font-family: sans-serif;}</style>';