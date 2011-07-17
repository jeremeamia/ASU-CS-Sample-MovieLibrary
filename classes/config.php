<?php defined('App::NAME') OR die('You cannot execute this script.');

class Config
{
	protected $_data = array();
	
	public function __construct(SplFileInfo $file)
	{
		if ( ! $file->isFile())
			throw new RuntimeException('The config.php file is missing from the application root.');

		$data = include $file->getPathname();

		if ( ! is_array($data))
			throw new RuntimeException('The config.php file did not return an array configuration settings.');

		$this->_data = $data;
	}

	public function get($group_key, $key = NULL, $default = NULL)
	{
		if ( ! array_key_exists($group_key, $this->_data))
			return $default;

		if ( ! array_key_exists($key, $this->_data[$group_key]))
			return $default;

		return $this->_data[$group_key][$key];
	}
}
