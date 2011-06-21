<?php defined('App::NAME') OR die('You cannot execute this script.');

class Container
{
	protected $_cache = array();

	public function getConfig()
	{
		if ( ! isset($this->_cache['config']))
		{
			$config_file = new SplFileInfo('config.php');
			$this->_cache['config'] = new Config($config_file);
		}

		return $this->_cache['config'];
	}

	public function getSession()
	{
		if ( ! isset($this->_cache['session']))
		{
			$this->_cache['session'] = new Session(App::NAME);
		}

		return $this->_cache['session'];
	}

	public function getRequest()
	{
		if ( ! isset($this->_cache['request']))
		{
			$this->_cache['request'] = new Request($this);
		}

		return $this->_cache['request'];
	}

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

	public function getDatabase()
	{
		if ( ! isset($this->_cache['database']))
		{
			$connection = $this->getDatabaseConnection();
			$this->_cache['database'] = new Database($connection);
		}

		return $this->_cache['database'];
	}

	public function getNetflix()
	{
		if ( ! isset($this->_cache['netflix']))
		{
			$oauth = new Service_Netflix_OAuth();
			$this->_cache['netflix'] = new Service_Netflix_Library($oauth, $this->getConfig());
		}

		return $this->_cache['netflix'];
	}

	public function getHelpers()
	{
		if ( ! isset($this->_cache['helpers']))
		{
			$helpers = new Helper_Collection();
			$helpers->add('html', new Helper_HTML($this->getRequest()));
			$helpers->add('form', new Helper_Form($this->getRequest(), $helpers->get('html')));
			$this->_cache['helpers'] = $helpers;
		}

		return $this->_cache['helpers'];
	}

	public function getView($file)
	{
		$view = new View($file, $this->getHelpers(), $this->getConfig());

		return $view;
	}

	public function getModel($type)
	{
		// Find the class
		$class = 'Model_'.$type;
		if ( ! class_exists($class))
			throw new RuntimeException('The "'.$type.'" model does not exist.');

		$model = new $class($this->getDatabase(), $this->getConfig());

		return $model;
	}

	public function getUserModel()
	{
		$user = new Model_User($this->getDatabase(), $this->getConfig(), $this->getSession());

		return $user;
	}
}
