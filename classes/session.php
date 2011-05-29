<?php defined('App::NAME') OR die('You cannot execute this script.');

class Session
{
	protected static $_instance = NULL;

	public static function instance($name = NULL)
	{
		if (Session::$_instance === NULL)
		{
			Session::$_instance = new Session($name);
		}

		return Session::$_instance;
	}

	final private function __construct($name = NULL)
	{
		// Do not allow PHP to send Cache-Control headers
		session_cache_limiter(FALSE);

		// Set the session cookie name
		if ($name)
		{
			session_name($name);
		}

		// Start the session
		session_start();

		// Make sure the session is saved on shutdown... with a lambda!
		$save_session = function() {session_write_close();};
		register_shutdown_function($save_session);
	}

	final private function __clone() {}
	
	public function & asArray()
	{
		return $_SESSION;
	}

	public function get($key, $default = NULL)
	{
		return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
	}

	public function set($key, $value = NULL)
	{
		$_SESSION[$key] = $value;
	}

	public function delete($key)
	{
		$args = func_get_args();

		foreach ($args as $key)
		{
			if (array_key_exists($key, $_SESSION))
			{
				unset($_SESSION[$key]);
			}
		}
	}

	public function destroy()
	{
		// Destroy the current session
		session_destroy();

		// Did destruction work?
		return (bool) ! session_id();
	}
}
