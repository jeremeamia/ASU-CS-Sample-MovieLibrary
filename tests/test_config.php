<?php defined('App::NAME') OR die('You cannot execute this script.');

App::load('Config');

class Test_Config extends UnitTestCase
{
	public $file = '_config.php';
	public $values = array(
		'test' => array(
			'aaa' => '111',
			'bbb' => '222',
			'ccc' => '333',
		),
	);

	public function setUp()
	{
		$file = fopen($this->file, 'w');
		fwrite($file, '<?php return '.var_export($this->values, TRUE).';');
		fclose($file);
	}

	public function tearDown()
	{
		unlink($this->file);
	}

	public function testGet()
	{
		$config = new Config(new SplFileInfo($this->file));

		// Test values that exist
		foreach ($this->values as $group => $items)
		{
			foreach ($items as $key => $value)
			{
				$result = $config->get($group, $key);
				$this->assertEqual($result, $value);
			}
		}

		// Test values that do not exist
		$result = $config->get('test', 'ddd');
		$this->assertNull($result);

		$result = $config->get('foo', 'aaa');
		$this->assertNull($result);

		$result = $config->get('test', 'ddd', 'foo');
		$this->assertEqual($result, 'foo');
	}
}
