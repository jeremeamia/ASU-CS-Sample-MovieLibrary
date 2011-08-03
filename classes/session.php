<?php defined('App::NAME') OR die('You cannot execute this script.');
/**
 * The Session class encapsulates the global $_SESSION array and provides
 * a simple object-oriented interface.
 */
class Session
{
	/**
	 * Constructs a Session object and sets up sessions for PHP.
	 *
	 * @param string $name Session name
	 */
	public function __construct($name = NULL)
	{
		// Should only do the following if the session has not been started
		if ( ! session_id())
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

			// Make sure the session is saved on shutdown
			register_shutdown_function(array($this, 'shutdown'));
		}
	}

	/**
	 * Returns the entire session array
	 *
	 * @return array A reference to the $_SESSION array
	 */
	public function & asArray()
	{
		return $_SESSION;
	}

	/**
	 * Returns a value from the session based on the key
	 *
	 * @param string $key The key identifying the value
	 * @param mixed $default A fallback value if the key does not exist
	 * @return mixed
	 */
	public function get($key, $default = NULL)
	{
		return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
	}

	/**
	 * Sets a value in the session
	 *
	 * @chainable
	 * @param string $key The key identifying the value
	 * @param mixed $value The value to be stored
	 * @return Session
	 */
	public function set($key, $value = NULL)
	{
		$_SESSION[$key] = $value;

		return $this;
	}

	/**
	 * Deletes the value in te session at the specified key
	 *
	 * @chainable
	 * @param string $key The key to delete
	 * @return Session
	 */
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

		return $this;
	}

	/**
	 * Destroys the current session
	 *
	 * @return bool
	 */
	public function destroy()
	{
		// Destroy the current session
		session_destroy();

		// Did destruction work?
		return (bool) ! session_id();
	}

	/**
	 * Ensures that the session is written. Used by the constructor
	 *
	 * @return void
	 */
	public function shutdown()
	{
		session_write_close();
	}
}
