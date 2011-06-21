<?php defined('App::NAME') OR die('You cannot execute this script.');

class Request
{
	protected $_container;
	protected $_post;
	protected $_get;
	protected $_cookie;
	protected $_controller;
	protected $_resource_id;

	public function __construct(Container $container)
	{
		$this->_container = $container;

		$this->_post = $this->_sanitize($_POST);
		$this->_get = $this->_sanitize($_GET);
		$this->_cookie = $this->_sanitize($_COOKIE);

		$this->_processUrl();
	}

	public function execute()
	{
		// Create a controller using this request
		$controller = NULL;
		$class = 'Controller_'.str_replace('/', '_', $this->_controller);
		if (class_exists($class))
		{
			$controller = new $class($this);
		}

		// Handle 404 errors
		if ($controller == NULL)
		{
			$this->_controller = 'error/notfound';
			$controller = new Controller_Error_NotFound($this);
		}

		try
		{
			// Execute the request
			$controller->preExecute();
			$controller->execute();
			$controller->postExecute();
		}
		catch (Exception $ex)
		{
			// Handle 500 errors
			$this->_controller = 'error/internal';
			$controller = new Controller_Error_Internal($this);
			$controller->preExecute();
			$controller->execute();
			$controller->postExecute();
		}

		// Send the response back to the App
		return $controller->getResponse();
	}

	public function post($key = NULL, $default = NULL)
	{
		if ($key === NULL) return $this->_post;
		return array_key_exists($key, $this->_post) ? $this->_post[$key] : $default;
	}

	public function get($key, $default = NULL)
	{
		if ($key === NULL) return $this->_get;
		return array_key_exists($key, $this->_get) ? $this->_get[$key] : $default;
	}

	public function cookie($key, $default = NULL)
	{
		if ($key === NULL) return $this->_cookie;
		return array_key_exists($key, $this->_cookie) ? $this->_cookie[$key] : $default;
	}

	public function getController()
	{
		return $this->_controller;
	}

	public function getResourceId()
	{
		return $this->_resource_id;
	}

	public function getContainer()
	{
		return $this->_container;
	}

	public function setUserMessage($type, $message)
	{
		$details = array('type' => $type, 'message' => $message);
		$this->_container->getSession()->set('user.message', $details);
	}

	public function getUserMessage()
	{
		$message = $this->_container->getSession()->get('user.message');
		$this->_container->getSession()->delete('user.message');
		return $message;
	}

	/**
	 * Construct an absolute url pointing to an internal resource
	 *
	 * @param	string	$controller
	 * @param	string	$id
	 * @return	string
	 */
	public function buildUri($controller = NULL, $id = NULL, array $args = array())
	{
		if (strpos($controller, '://') === FALSE)
		{
			// Build an internal URL to our application
			$uri = (string) $controller;
			if ($id)
			{
				$uri .= '/'.$id;
			}
			$uri = $this->_container->getConfig()->get('site', 'base_uri').$uri;
		}
		else
		{
			// Treat the argument like an external URL
			$uri = $controller;
		}

		// Append any args as a query string
		if ($args)
		{
			$uri .= '?'.http_build_query($args);
		}

		return $uri;
	}

	public function currentUri()
	{
		return $this->buildUri($this->_controller, $this->_resource_id, $this->_get);
	}

	/**
	 * Redirect the request to a different URL
	 *
	 * @param	string	$controller
	 * @param	string	$id
	 */
	public function redirect($controller = NULL, $id = NULL)
	{
		$url = $this->buildUri($controller, $id);
		header('Location: '.$url);
		exit;
	}

	protected function _sanitize($value)
	{
		if (is_array($value))
		{
			foreach ($value as $key => $val)
			{
				// Recursively clean each value
				$value[$key] = $this->_sanitize($val);
			}
		}
		elseif (is_string($value))
		{
			// Remove slashes added by magic quotes
			if (get_magic_quotes_gpc() === TRUE)
			{
				$value = stripslashes($value);
			}

			// Standardize newlines
			if (strpos($value, "\r") !== FALSE)
			{
				$value = str_replace(array("\r\n", "\r"), "\n", $value);
			}
		}

		return $value;
	}

	protected function _processUrl()
	{
		// Get the URI
		$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$uri = rawurldecode($request_uri);

		// Remove the base URL from the URI
		$base_uri = $this->_container->getConfig()->get('site', 'base_uri');
		if (strpos($uri, $base_uri) === 0)
		{
			$uri = (string) substr($uri, strlen($base_uri));
		}

		// Remove the index file from the URI
		if (strpos($uri, 'index.php/') === 0)
		{
			$uri = (string) substr($uri, strlen('index.php/'));
		}

		// If the URI is empty, then we'll use the default
		if (empty($uri))
		{
			$uri = $this->_container->getConfig()->get('site', 'default_uri', 'home');
		}

		// Now set the controller and resource ID
		if (preg_match('/^([^0-9]+)(\/[0-9]+\/?)?$/', $uri, $matches))
		{
			$this->_controller = trim($matches[1], '/');
			$this->_resource_id = isset($matches[2]) ? trim($matches[2], '/') : NULL;
		}
		else
		{
			$this->_controller = $this->_resource_id = NULL;
		}
	}
}
