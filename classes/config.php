<?php defined('App::NAME') OR die('You cannot execute this script.');
/**
 * The Config class reads in a config value and allows the config values to be
 * retrieved.
 */
class Config
{
	/**
	 * @var array Configuration data for the application
	 */
	protected $_data = array();

	/**
	 * Constructs a Config object
	 *
	 * @throws RuntimeException
	 * @param SplFileInfo $file A file object representing the config file
	 */
	public function __construct(SplFileInfo $file)
	{
		if ( ! $file->isFile())
			throw new RuntimeException('The config.php file is missing from the application root.');

		$data = include $file->getPathname();

		if ( ! is_array($data))
			throw new RuntimeException('The config.php file did not return an array configuration settings.');

		$this->_data = $data;
	}

	/**
	 * Retrieves a config item from the config file
	 *
	 * @param string $group_key The config group name
	 * @param string $key The config key
	 * @param mixed $default A fallback value if the config item cannot be found
	 * @return mixed
	 */
	public function get($group_key, $key = NULL, $default = NULL)
	{
		if ( ! array_key_exists($group_key, $this->_data))
			return $default;

		if ( ! array_key_exists($key, $this->_data[$group_key]))
			return $default;

		return $this->_data[$group_key][$key];
	}
}
