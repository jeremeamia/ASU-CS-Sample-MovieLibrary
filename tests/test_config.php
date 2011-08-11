<?php defined('App::NAME') OR die('You cannot execute this script.');

App::load('Config');

class Test_Config extends UnitTestCase
{
	public function testGettingValuesFromConfig()
	{
		// Setup values
		$file = dirname(__FILE__).'/config_for_test.php';
		$values = include $file;
		
		// Create config object to test
		$config = new Config(new SplFileInfo($file));

		// Test keys that exist
		foreach ($values as $group => $items)
		{
			foreach ($items as $key => $value)
			{
				$result = $config->get($group, $key);
				$this->assertEqual($result, $value);
			}
		}

		// Test key that does not exist
		$result = $config->get('test', 'ddd');
		$this->assertNull($result);

		// Test group that does not exist
		$result = $config->get('foo', 'aaa');
		$this->assertNull($result);

		// Test the default (or fallback) functionality
		$result = $config->get('test', 'ddd', 'foo');
		$this->assertEqual($result, 'foo');
	}
}
