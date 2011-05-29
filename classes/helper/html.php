<?php defined('App::NAME') OR die('You cannot execute this script.');

class Helper_HTML
{
	protected $_request = NULL;

	public function __construct(Request $request)
	{
		$this->_request = $request;
	}

	public function link($text, $controller = NULL, $id = NULL, array $attributes = array())
	{
		$uri = $this->_request->buildUri($controller, $id);
		$attributes['href'] = $uri;
		return '<a'.$this->attributes($attributes).'>'.$text.'</a>';
	}

	public function attributes(array $attributes = array())
	{
		$html = '';
		foreach ($attributes as $key => $value)
		{
			$html .= ' '.$key.'="'.$value.'"';
		}
		
		return $html;
	}
}
