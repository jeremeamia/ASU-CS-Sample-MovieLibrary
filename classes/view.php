<?php defined('App::NAME') OR die('You cannot execute this script.');

class View
{
	protected $_data;
	protected $_file;

	public function __construct($file)
	{
		$this->_file = 'templates/'.$file.'.php';
		$this->_data = array();
	}

	public function set($key, $value)
	{
		$this->_data[$key] = $value;
		return $this;
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
