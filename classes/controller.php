<?php defined('App::NAME') OR die('You cannot execute this script.');
/**
 * The Controller class acts as an abstract base class for controllers in an
 * MVC architecture
 */
abstract class Controller
{
	/**
	 * @var Request The currently executing request
	 */
	protected $_request;

	/**
	 * @var string The response set by the executed controller action
	 */
	protected $_response;

	/**
	 * @var Container The container class used for object instantiation
	 */
	protected $_container;

	/**
	 * Constructs as Controller object
	 *
	 * @param Request $request The currently executing request
	 * @param Container $container The dependency injection container
	 */
	public function __construct(Request $request, Container $container)
	{
		$this->_request = $request;
		$this->_response = '';
		$this->_container = $container;
	}

	/**
	 * This method is executed before every action in the controller
	 *
	 * @return void
	 */
	public function beforeAction() {}

	/**
	 * This method is executed after every action in the controller
	 *
	 * @return void
	 */
	public function afterAction() {}

	/**
	 * Returns the Container
	 *
	 * @return Container
	 */
	public function getContainer()
	{
		return $this->_container;
	}

	/**
	 * Returns the Request
	 *
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->_request;
	}

	/**
	 * Returns the Response
	 *
	 * @return string
	 */
	public function getResponse()
	{
		return $this->_response;
	}

	/**
	 * Sets the Response
	 *
	 * @chainable
	 * @param View $view The View to be displayed in the browser
	 * @return Controller
	 */
	public function setResponse(View $view)
	{
		$this->_response = $view;
		
		return $this;
	}
}
