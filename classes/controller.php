<?php defined('App::NAME') OR die('You cannot execute this script.');

abstract class Controller
{
	protected $_request;
	protected $_response;

	public static function factory(Request $request)
	{
		$class = 'Controller_'.str_replace('/', '_', $request->getController());
		if ( ! class_exists($class))
			return NULL;

		return new $class($request);
	}

	public function __construct(Request $request)
	{
		$this->_request = $request;
		$this->_response = '';
	}

	public function preExecute() {}

	abstract public function execute();

	public function getContainer()
	{
		return $this->_request->getContainer();
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
