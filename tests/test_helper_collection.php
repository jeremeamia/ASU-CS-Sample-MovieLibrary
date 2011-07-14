<?php defined('App::NAME') OR die('You cannot execute this script.');

App::load('Helper_Collection');
App::load('Helper');
Mock::generate('Helper');

class Test_Helper_Collection extends UnitTestCase
{
	public $helper_collection;

	public function setUp()
	{
		$this->helper_collection = new Helper_Collection();
	}

	public function tearDown()
	{
		unset($this->helper_collection);
	}

	public function testAddHelper()
	{
		$helper = new MockHelper();
		$result = $this->helper_collection->addHelper('test', $helper);
		$this->assertReference($result, $this->helper_collection);
	}

	public function testGetHelper()
	{
		$helper = new MockHelper();
		$this->helper_collection->addHelper('test', $helper);
		$result = $this->helper_collection->getHelper('test');
		$this->assertReference($result, $helper);
	}
}
