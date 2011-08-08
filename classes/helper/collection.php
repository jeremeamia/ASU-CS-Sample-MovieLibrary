<?php defined('App::NAME') OR die('You cannot execute this script.');
/**
 * A simple collection class for storing and retrieving helper objects. This
 * class is mainly used to aid with type hinting in the View class
 */
class Helper_Collection
{
	protected $_helpers;

	public function __construct()
	{
		$this->_helpers = array();
	}

	public function addHelper($name, Helper $helper)
	{
		$this->_helpers[$name] = $helper;

		return $this;
	}

	public function getHelper($name)
	{
		return isset($this->_helpers[$name]) ? $this->_helpers[$name] : NULL;
	}
}
