<?php defined('App::NAME') OR die('You cannot execute this script.');
/**
 * A simple class for generating HTML elements
 */
class Helper_HTML extends Helper
{
	protected $_request = NULL;

	public function __construct(Request $request)
	{
		$this->_request = $request;
	}

	public function link($text, $uri = NULL, array $attributes = array())
	{
		$url = $this->_request->buildUrl($uri);
		$attributes['href'] = $url;
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

	public function image($src, $alt, array $attributes = array())
	{
		$attributes['src'] = (strpos($src, 'http') === 0) ? $src : $this->_request->baseUrl().$src;
		$attributes['alt'] = $alt;
		return '<img'.$this->attributes($attributes).'>';
	}

	public function stylesheet($href)
	{
		$attributes['rel'] = 'stylesheet';
		$attributes['href'] = (strpos($href, 'http') === 0) ? $href : $this->_request->baseUrl().$href;
		return '<link'.$this->attributes($attributes).'>';
	}
}
