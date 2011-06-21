<?php defined('App::NAME') OR die('You cannot execute this script.');

abstract class Controller
{
	protected $_request;
	protected $_response;
	protected $_container;

	public function __construct(Request $request)
	{
		$this->_request = $request;
		$this->_response = '';
		$this->_container = $request->getContainer();
	}

	public function preExecute() {}

	abstract public function execute();

	public function postExecute() {}

	public function getContainer()
	{
		return $this->_container;
	}

	public function getRequest()
	{
		return $this->_request;
	}

	public function setResponse($response)
	{
		$this->_response = $response;
		return $this;
	}

	public function getResponse()
	{
		return (string) $this->_response;
	}
}
