<?php defined('App::NAME') OR die('You cannot execute this script.');

abstract class Controller
{
	protected $_request;
	protected $_response;
	protected $_container;

	public function __construct(Request $request, Container $container)
	{
		$this->_request = $request;
		$this->_response = '';
		$this->_container = $container;
	}

	public function beforeAction() {}

	public function afterAction() {}

	public function getContainer()
	{
		return $this->_container;
	}

	public function getRequest()
	{
		return $this->_request;
	}

	public function getResponse()
	{
		return $this->_response;
	}

	public function setResponse(View $view)
	{
		$this->_response = $view;
		
		return $this;
	}
}
