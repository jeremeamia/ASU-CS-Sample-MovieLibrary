<?php defined('App::NAME') OR die('You cannot execute this script.');
/**
 * The Container class is a Dependency Injection Container and contains methods
 * for creating the various objects required by the programs such that all of
 * the required dependencies are injected automatically. It is an abstraction
 * and organization of object instantiation.
 */
class Container
{
	/**
	 * @var array Stores cached versions of items in the container
	 */
	protected $_cache = array();

	/**
	 * Returns an initialized Config object
	 *
	 * @return Config
	 */
	public function getConfig()
	{
		if ( ! isset($this->_cache['config']))
		{
			$config_file = new SplFileInfo('config.php');
			$this->_cache['config'] = new Config($config_file);
		}

		return $this->_cache['config'];
	}

	/**
	 * Returns an initialized Session object
	 *
	 * @return Session
	 */
	public function getSession()
	{
		if ( ! isset($this->_cache['session']))
		{
			$this->_cache['session'] = new Session(App::NAME);
		}

		return $this->_cache['session'];
	}

	/**
	 * Returns an initialized Request object
	 *
	 * @return Request
	 */
	public function getRequest()
	{
		if ( ! isset($this->_cache['request']))
		{
			$this->_cache['request'] = new Request($this);
		}

		return $this->_cache['request'];
	}

	/**
	 * Returns an initialized database Cconnection
	 *
	 * @return MySQLi
	 */
	public function getDatabaseConnection()
	{
		if ( ! isset($this->_cache['database_connection']))
		{
			$config = $this->getConfig();
			$connection = new MySQLi(
				$config->get('database', 'host'),
				$config->get('database', 'username'),
				$config->get('database', 'password'),
				$config->get('database', 'name')
			);

			if ($connection->connect_error)
				throw new RuntimeException('Could not connect to the MySQL database.');

			$this->_cache['database_connection'] = $connection;
		}

		return $this->_cache['database_connection'];
	}

	/**
	 * Returns an initialized Database object
	 *
	 * @return Database
	 */
	public function getDatabase()
	{
		if ( ! isset($this->_cache['database']))
		{
			$connection = $this->getDatabaseConnection();
			$this->_cache['database'] = new Database($connection);
		}

		return $this->_cache['database'];
	}

	/**
	 * Returns an initialized Netflix object
	 *
	 * @return Service_Netflix_Library
	 */
	public function getNetflix()
	{
		if ( ! isset($this->_cache['netflix']))
		{
			$oauth = new Service_Netflix_OAuth();
			$this->_cache['netflix'] = new Service_Netflix_Library($oauth, $this->getConfig());
		}

		return $this->_cache['netflix'];
	}

	/**
	 * Returns an initialized Helper Collection object
	 *
	 * @return Helper_Collection
	 */
	public function getHelpers()
	{
		if ( ! isset($this->_cache['helpers']))
		{
			$request = $this->getRequest();
			$html_helper = new Helper_HTML($request);

			$helpers = new Helper_Collection();
			$helpers->addHelper('html', $html_helper);
			$helpers->addHelper('form', new Helper_Form($request, $html_helper));

			$this->_cache['helpers'] = $helpers;
		}

		return $this->_cache['helpers'];
	}

	/**
	 * Returns an initialized View object
	 *
	 * @return View
	 */
	public function getView($template)
	{
		return new View($template, $this->getHelpers(), $this->getConfig());
	}

	/**
	 * Returns an initialized Model object
	 *
	 * @return Model
	 */
	public function getModel($name)
	{
		// Get the model class and make sure it exists
		$class = 'Model_'.ucfirst($name);
		if ( ! class_exists($class))
			throw new RuntimeException('The "'.$name.'" model does not exist.');

		// Create an instance of the model
		if ($name == 'user')
		{
			$model = new Model_User($this->getDatabase(), $this->getConfig(), $this->getSession());
		}
		else
		{
			$model = new $class($this->getDatabase(), $this->getConfig());
		}

		return $model;
	}
}
