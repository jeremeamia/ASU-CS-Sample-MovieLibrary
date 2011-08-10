<?php defined('App::NAME') OR die('You cannot execute this script.');

App::load('Helper_HTML');
App::load('Request');
Mock::generate('Request');

class Test_Helper_HTML extends UnitTestCase
{
	public $html_helper;

	public function setUp()
	{
		$request = new MockRequest();
		$request->returns('buildUrl', 'http://example.com/index.php/controller/action/id', array(array('controller', 'action', 'id')));
		$request->returns('buildUrl', 'http://example.com/');
		$request->returns('baseUrl', 'http://example.com/');

		$this->html_helper = new Helper_HTML($request);
	}

	public function tearDown()
	{
		unset($this->html_helper);
	}

	public function testBuildingAttributesFromArrays()
	{
		$result = $this->html_helper->attributes(array('id' => 'unique_id', 'class' => 'example'));
		$this->assertEqual($result, ' id="unique_id" class="example"');

		$result = $this->html_helper->attributes(array('class' => 'example'));
		$this->assertEqual($result, ' class="example"');

		$result = $this->html_helper->attributes(array());
		$this->assertEqual($result, '');
	}

	public function testGeneratingHtmlForLinks()
	{
		$result = $this->html_helper->link('http://example.com');
		$this->assertEqual($result, '<a href="http://example.com/">http://example.com</a>');

		$result = $this->html_helper->link('Example', array('controller', 'action', 'id'));
		$this->assertEqual($result, '<a href="http://example.com/index.php/controller/action/id">Example</a>');

		$result = $this->html_helper->link('Example', array('controller', 'action', 'id'), array('class' => 'example'));
		$this->assertEqual($result, '<a class="example" href="http://example.com/index.php/controller/action/id">Example</a>');
	}

	public function testGeneratingHtmlForImages()
	{
		$result = $this->html_helper->image('http://google.com/logo.png', 'Logo');
		$this->assertEqual($result, '<img src="http://google.com/logo.png" alt="Logo">');

		$result = $this->html_helper->image('assets/logo.png', 'Logo');
		$this->assertEqual($result, '<img src="http://example.com/assets/logo.png" alt="Logo">');

		$result = $this->html_helper->image('assets/logo.png', 'Logo', array('class' => 'example'));
		$this->assertEqual($result, '<img class="example" src="http://example.com/assets/logo.png" alt="Logo">');
	}

	public function testGeneratingHtmlForIncludingStylesheets()
	{
		$result = $this->html_helper->stylesheet('http://google.com/style.css');
		$this->assertEqual($result, '<link rel="stylesheet" href="http://google.com/style.css">');

		$result = $this->html_helper->stylesheet('assets/style.css');
		$this->assertEqual($result, '<link rel="stylesheet" href="http://example.com/assets/style.css">');
	}
}
