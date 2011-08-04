<?php defined('App::NAME') OR die('You cannot execute this script.');
/**
 * The View class helps implement the V part of MVC. Views, in conjunction
 * with templates, are responsible for creating the HTML sent to the browser.
 */
class View
{
	/**
	 * @var array The data provided to the view for the template
	 */
	protected $_data;

	/**
	 * @var string The path to the template (relative to the templates dir)
	 */
	protected $_template;

	/**
	 * @var Helper_Collection The helpers passed into the view
	 */
	protected $_helpers;

	/**
	 * @var Config The config passed into the view
	 */
	protected $_config;

	/**
	 * Constructs the View object.
	 *
	 * @param string $template The template to be used when rendering the view
	 * @param Helper_Collection $helpers The collection of helpers available
	 * @param Config $config The Config object
	 */
	public function __construct($template, Helper_Collection $helpers, Config $config)
	{
		$this->_data = array();
		$this->setTemplate($template);
		$this->_helpers = $helpers;
		$this->_config = $config;
	}

	/**
	 * Sets the template to be used when rendering the view
	 *
	 * @param  $template
	 * @return View
	 */
	public function setTemplate($template)
	{
		$this->_template = 'templates/'.trim($template, '/').'.php';

		return $this;
	}

	/**
	 * Sets a key-value pair such that a variable with a name the same as key
	 * in the template will be filled with the value
	 *
	 * @param string $key The key or variable name
	 * @param mixed $value The value that will be present in the template
	 * @return View
	 */
	public function set($key, $value)
	{
		$this->_data[$key] = $value;

		return $this;
	}

	/**
	 * Returns a value from the data
	 *
	 * @param string $key The key of the datum
	 * @param mixed $default A fallback value if the key does not exist
	 * @return mixed
	 */
	public function get($key, $default = NULL)
	{
		return array_key_exists($key, $this->_data) ? $this->_data[$key] : $default;
	}

	/**
	 * Returns a Helper from the collection with the specified name
	 *
	 * @param  $name
	 * @return array|null
	 */
	public function getHelper($name)
 	{
		return $this->_helpers->getHelper($name);
 	}

	/**
	 * Returns a config value with the specified keys
	 *
	 * @param string $group_key The config group name
	 * @param string $key The config key
	 * @param mixed $default A fallback value if the config item cannot be found
	 * @return mixed
	 */
	public function getConfigValue($group_key, $key = NULL, $default = NULL)
	{
		return $this->_config->get($group_key, $key, $default);
	}

	/**
	 * Renders the View into a string using the template
	 *
	 * @return string
	 */
	public function render()
	{
		$this->set('view', $this);
		return View::_safeRender($this->_template, $this->_data);
	}

	/**
	 * Provides a static, safe context in which to to the rendering
	 *
	 * @static
	 * @throws RuntimeException
	 * @param string $template The path to a template
	 * @param array $data An array of key-values pairs of data
	 * @return string
	 */
	protected static function _safeRender($template, $data)
	{
		if ( ! file_exists($template))
			throw new RuntimeException('The template file "'.$template.'" does not exist.');

		ob_start();
		extract($data);
		include $template;
		return ob_get_clean();
	}
}
