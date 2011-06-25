<?php defined('App::NAME') OR die('You cannot execute this script.');

class View
{
	protected $_data;
	protected $_template;
	protected $_helpers;
	protected $_config;

	public function __construct($template, Helper_Collection $helpers, Config $config)
	{
		$this->_data = array();
		$this->setTemplate($template);
		$this->_helpers = $helpers;
		$this->_config = $config;
	}

	public function setTemplate($template)
	{
		$this->_template = 'templates/'.trim($template, '/').'.php';

		return $this;
	}

	public function set($key, $value)
	{
		$this->_data[$key] = $value;

		return $this;
	}

	public function get($key, $default = NULL)
	{
		return array_key_exists($key, $this->_data) ? $this->_data[$key] : $default;
	}

	public function getHelper($name)
 	{
		return $this->_helpers->getHelper($name);
 	}

	public function getConfigValue($group_key, $key = NULL, $default = NULL)
	{
		return $this->_config->get($group_key, $key, $default);
	}

	public function render()
	{
		$this->set('view', $this);
		return View::_safeRender($this->_template, $this->_data);
	}

	protected static function _safeRender($template, $data)
	{
		if ( ! file_exists($template))
			throw new Exception('The template file "'.$template.'" does not exist.');

		ob_start();
		extract($data);
		include $template;
		return ob_get_clean();
	}
}
