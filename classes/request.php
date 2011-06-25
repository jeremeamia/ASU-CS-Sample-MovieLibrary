<?php defined('App::NAME') OR die('You cannot execute this script.');

class RequestException404 extends Exception {}

class Request
{
	protected $_container;
	protected $_post;
	protected $_get;
	protected $_cookie;
	protected $_controller;
	protected $_action;
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
		try
		{
			// Create a controller using this request
			$action = 'action'.ucfirst($this->_action);
			$class = 'Controller_'.$this->_controller;
			if ( ! class_exists($class) OR ! method_exists($class, $action))
				throw new RequestException404();
			$controller = new $class($this, $this->_container);

			// Execute the request
			$controller->beforeAction();
			$controller->{$action}();
			$controller->afterAction();
		}
		catch (RequestException404 $ex)
		{
			// Handle 500 errors
			$controller = new Controller_Error($this, $this->_container);
			$controller->beforeAction();
			$controller->actionNotfound();
			$controller->afterAction();
		}
		catch (Exception $ex)
		{
			// Handle 500 errors
			$controller = new Controller_Error($this, $this->_container);
			$controller->beforeAction();
			$controller->actionInternal();
			$controller->afterAction();
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

	public function getAction()
	{
		return $this->_action;
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
	 * Construct an absolute url pointing to an internal or external resource
	 *
	 * @param	mixed	$uri
	 * @return	string
	 */
	public function buildUrl($uri = NULL, array $args = array())
	{
		if (is_array($uri))
		{
			// Build an internal URL to our application
			$uri = trim(implode('/', array_map('strval', $uri)), '/');
		}

		// Append any args as a query string
		if ($args)
		{
			$uri .= '?'.http_build_query($args);
		}

		return $this->_container->getConfig()->get('site', 'base_uri').$uri;
	}

	public function currentUrl()
	{
		return $this->buildUrl(array($this->_controller, $this->_action, $this->_resource_id), $this->_get);
	}

	/**
	 * Redirect the request to a different URL
	 *
	 * @param	mixed	$uri
	 * @param	string	$id
	 */
	public function redirect($uri)
	{
		$url = $this->buildUrl($uri);
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
		//if (strpos($uri, 'index.php/') === 0)
		//{
		//	$uri = (string) substr($uri, strlen('index.php/'));
		//}

		// Now set the controller, action, and resource ID
		$uri_parts = array_pad(explode('/', trim($uri, '/')), 3, NULL);
		$config = $this->_container->getConfig();
		$this->_controller = $uri_parts[0] ? $uri_parts[0] : $config->get('site', 'default_controller');
		$this->_action = $uri_parts[1] ? $uri_parts[1] : $config->get('site', 'default_action');
		$this->_resource_id = $uri_parts[2];
	}
}
