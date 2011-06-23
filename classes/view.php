<?php defined('App::NAME') OR die('You cannot execute this script.');

class View
{
	protected $_data;
	protected $_file;
	protected $_helpers;
	protected $_config;

	public function __construct($file, Helper_Collection $helpers, Config $config)
	{
		$this->_data = array();
		$this->_file = 'templates/'.$file.'.php';
		$this->_helpers = $helpers;
		$this->_config = $config;
	}

	public function set($key, $value)
	{
		$this->_data[$key] = $value;
		return $this;
	}

	public function getHelper($name)
	{
		return $this->_helpers->getHelper($name);
	}

	public function getConfigValue($group_key, $key = NULL, $default = NULL)
	{
		return $this->_config->get($group_key, $key, $default);
	}

	public function render()
	{
		ob_start();
		@extract($this->_data);
		if ( ! file_exists($this->_file))
			throw new Exception('The template file "'.$this->_file.'" does not exist.');
		@include $this->_file;
		return ob_get_clean();
	}

	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (Exception $e)
		{
			// We must catch this exception because __toString cannot throw exceptions
			return 'ERROR: '.$e->getMessage();
		}
	}
}
