<?php defined('App::NAME') OR die('You cannot execute this script.');

class Helper_Collection
{
	protected $_helpers;

	public function __construct()
	{
		$this->_helpers = array();
	}

	public function add($name, Helper $helper)
	{
		$this->_helpers[$name] = $helper;

		return $this;
	}

	public function get($name)
	{
		return isset($this->_helpers[$name]) ? $this->_helpers[$name] : NULL;
	}
}
