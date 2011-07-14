<?php defined('App::NAME') OR die('You cannot execute this script.');

App::load('Helper_Collection');
App::load('Helper');
Mock::generate('Helper');

class Test_Helper_Collection extends UnitTestCase
{
	public $helpers;

	public function setUp()
	{
		$this->helpers = new Helper_Collection();
	}

	public function tearDown()
	{
		unset($this->helpers);
	}

	public function testAddHelper()
	{
		$helper = new MockHelper();
		$result = $this->helpers->addHelper('test', $helper);
		$this->assertReference($result, $this->helpers);
	}

	public function testGetHelper()
	{
		$helper = new MockHelper();
		$this->helpers->addHelper('test', $helper);
		$result = $this->helpers->getHelper('test');
		$this->assertReference($result, $helper);
	}
}
