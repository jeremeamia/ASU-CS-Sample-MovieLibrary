<?php defined('App::NAME') OR die('You cannot execute this script.');

class Container
{
	protected $_config;
	protected $_session;

	public function __construct(Config $config, Session $session)
	{
		$this->_config  = $config;
		$this->_session = $session;
	}

	public function getConfig()
	{
		return $this->_config;
	}

	public function getSession()
	{
		return $this->_session;
	}

	public function build($type, $name = NULL)
	{
		$method = '_'.strtolower($type);
		if ( ! method_exists($this, $method))
			throw new InvalidArgumentException('The type of object requested of the Container does not exist.');

		return $this->$method($name);
	}

	protected function _request()
	{
		return Request::instance($this);
	}

	protected function _view($name)
	{
		// Setup an object that houses helper classes available to the view
		$helpers = new ArrayObject();
		$helpers->html = new Helper_HTML(Request::instance());
		$helpers->form = new Helper_Form(Request::instance(), $helpers->html);

		$view = new View($name);
		$view->set('helpers', $helpers);
		$view->set('config',  $this->_config);

		return $view;
	}

	protected function _model($name)
	{
		// Find the class
		$class = 'Model_'.$name;
		if ( ! class_exists($class))
			throw new RuntimeException('The model, "'.$class.'", does not exist.');

		// Get dependencies
		$database = Database::instance($this->_config);

		// Instantiate the model with its dependencies
		if ($name == 'user')
		{
			// User model needs Session to do authentication
			$model = new $class($database, $this->_config, $this->_session);
		}
		else
		{
			$model = new $class($database, $this->_config);
		}

		return $model;
	}

	protected function _netflix()
	{
		$oauth = new Service_Netflix_OAuth();
		return new Service_Netflix_Library($oauth, $this->_config);
	}
}
