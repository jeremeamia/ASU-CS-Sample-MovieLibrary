<?php defined('App::NAME') OR die('You cannot execute this script.');
/**
 * An Exception signifying a 404 error
 */
class RequestException404 extends Exception {}

/**
 * The request class is what processes the URL and determines which controller
 * and action to execute. It also provides an interface to fetching user input
 * (GET, POST, COOKIE), building URLs, and handling flash messages.
 */
class Request
{
	/**
	 * @var Container The dependency injection container
	 */
	protected $_container;

	/**
	 * @var array The sanitized POST input
	 */
	protected $_post;

	/**
	 * @var array The sanitized GET input
	 */
	protected $_get;

	/**
	 * @var array The sanitized COOKIE input
	 */
	protected $_cookie;

	/**
	 * @var string The name of the controller that will be executed
	 */
	protected $_controller;

	/**
	 * @var string The name of the action that will be executed
	 */
	protected $_action;

	/**
	 * @var string The name of the resource associated with the request
	 */
	protected $_resource_id;

	/**
	 * Constructs a Request object
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->_container = $container;

		$this->_post = $this->_sanitize($_POST);
		$this->_get = $this->_sanitize($_GET);
		$this->_cookie = $this->_sanitize($_COOKIE);

		$this->_processUrl();
	}

	/**
	 * Executes the request by calling the action on the controller. Returns
	 * the result.
	 *
	 * @return string
	 */
	public function execute()
	{
		try
		{
			// Create a controller using this request
			$action = 'action'.ucfirst($this->_action);
			$class = 'Controller_'.ucfirst($this->_controller);
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
			$this->_controller = 'error';
			$this->_action = 'notfound';
			$controller = new Controller_Error($this, $this->_container);
			$controller->beforeAction();
			$controller->actionNotfound();
			$controller->afterAction();
		}
		catch (Exception $ex)
		{
			// Handle 500 errors
			$this->_controller = 'error';
			$this->_action = 'internal';
			$controller = new Controller_Error($this, $this->_container);
			$controller->beforeAction();
			$controller->actionInternal();
			$controller->afterAction();
		}

		// Send the response back to the App
		return $controller->getResponse()->render();
	}

	/**
	 * Fetches data from POST
	 *
	 * @param string $key The key of the item in the POST array
	 * @param mixed $default A fallback value if the key doesn't exist
	 * @return mixed
	 */
	public function post($key = NULL, $default = NULL)
	{
		if ($key === NULL) return $this->_post;
		return array_key_exists($key, $this->_post) ? $this->_post[$key] : $default;
	}

	/**
	 * Fetches data from GET
	 *
	 * @param string $key The key of the item in the GET array
	 * @param mixed $default A fallback value if the key doesn't exist
	 * @return mixed
	 */
	public function get($key, $default = NULL)
	{
		if ($key === NULL) return $this->_get;
		return array_key_exists($key, $this->_get) ? $this->_get[$key] : $default;
	}

	/**
	 * Fetches data from COOKIE
	 *
	 * @param string $key The key of the item in the COOKIE array
	 * @param mixed $default A fallback value if the key doesn't exist
	 * @return mixed
	 */
	public function cookie($key, $default = NULL)
	{
		if ($key === NULL) return $this->_cookie;
		return array_key_exists($key, $this->_cookie) ? $this->_cookie[$key] : $default;
	}

	/**
	 * Return the name of the controller
	 *
	 * @return string
	 */
	public function getController()
	{
		return $this->_controller;
	}

	/**
	 * Returns the name of the action
	 *
	 * @return string
	 */
	public function getAction()
	{
		return $this->_action;
	}

	/**
	 * Returns the ID of the resource to which the request refers
	 *
	 * @return int
	 */
	public function getResourceId()
	{
		return (int) $this->_resource_id;
	}

	/**
	 * Returns the container
	 *
	 * @return Container
	 */
	public function getContainer()
	{
		return $this->_container;
	}

	/**
	 * Sets a flash message in the SESSION
	 *
	 * @param string $type The type of message (e.g. error, success, info)
	 * @param string $message The message content
	 * @return void
	 */
	public function setUserMessage($type, $message)
	{
		$details = array('type' => $type, 'message' => $message);
		$this->_container->getSession()->set('user.message', $details);
	}

	/**
	 * Retrieves a flash message from the SESSION
	 *
	 * @return mixed
	 */
	public function getUserMessage()
	{
		$message = $this->_container->getSession()->get('user.message');
		$this->_container->getSession()->delete('user.message');
		return $message;
	}

	/**
	 * Construct an absolute url pointing to an internal or external resource
	 *
	 * @param mixed $uri relative or absolute URI or URL
	 * @param array $args Query arguments
	 * @return string
	 */
	public function buildUrl($uri = NULL, array $args = array())
	{
		if (is_array($uri) OR is_null($uri))
		{
			// Build an internal URL to our application
			$uri = trim(implode('/', array_map('strval', (array) $uri)), '/');
			$uri = $this->baseUrl().$uri;
		}

		// Append any args as a query string
		if ($args)
		{
			$uri .= '?'.http_build_query($args);
		}

		return $uri;
	}

	/**
	 * Returns the current URL
	 *
	 * @return string
	 */
	public function currentUrl()
	{
		return $this->buildUrl(array($this->_controller, $this->_action, $this->_resource_id), $this->_get);
	}

	/**
	 * Returns the base URL of the application
	 *
	 * @return mixed
	 */
	public function baseUrl($exclude_index = FALSE)
	{
		$base = rtrim($this->_container->getConfig()->get('site', 'base_uri'), '/');
		if ( ! $exclude_index)
		{
			$base .= '/index.php';
		}

		return $base.'/';
	}

	/**
	 * Redirect the request to a different URL
	 *
	 * @param mixed $uri A URI or URL
	 * @return void
	 */
	public function redirect($uri)
	{
		$url = $this->buildUrl($uri);
		header('Location: '.$url);
		exit;
	}

	/**
	 * Sanitizes and normalizes values
	 *
	 * @param mixed $value Whatever value you want to pass in
	 * @return mixed
	 */
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

	/**
	 * Parses the URL to get the controller, action, and resource this request
	 * is supposed to execute
	 *
	 * @return void
	 */
	protected function _processUrl()
	{
		// Get the URI
		$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$uri = rawurldecode($request_uri);

		// Remove the base URL from the URI
		$base_uri = rtrim($this->baseUrl(), '/');
		if (strpos($uri, $base_uri) === 0)
		{
			$uri = (string) substr($uri, strlen($base_uri));
		}

		// Now set the controller, action, and resource ID
		$uri_parts = array_pad(explode('/', trim($uri, '/')), 3, NULL);
		$config = $this->_container->getConfig();
		$this->_controller = $uri_parts[0] ? $uri_parts[0] : $config->get('site', 'default_controller');
		$this->_action = $uri_parts[1] ? $uri_parts[1] : $config->get('site', 'default_action');
		$this->_resource_id = $uri_parts[2];
	}
}
