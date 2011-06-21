<?php defined('App::NAME') OR die('You cannot execute this script.');

class Helper_Form
{
	protected $_request = NULL;
	protected $_html = NULL;

	public function __construct(Request $request, Helper_HTML $html)
	{
		$this->_request = $request;
		$this->_html = $html;
	}

	public function open($action = NULL)
	{
		$attributes = array
		(
			'method' => 'post',
			'action' => $action ? $action : $this->_request->currentUri(),
		);
		return '<form'.$this->_html->attributes($attributes).'>';
	}

	public function close()
	{
		return '</form>';
	}

	public function input($type, $name, $label = NULL, $label_before = TRUE)
	{
		$attributes = array
		(
			'type' => $type,
			'name' => $name,
			'id'   => $name,
		);

		$input = '<input'.$this->_html->attributes($attributes).' />';
		$label = $label ? $this->label($name, $label) : '';

		if ($label_before)
			return $label.$input;
		else
			return $input.$label;
	}

	public function button($type, $name, $label)
	{
		$attributes = array
		(
			'type' => $type,
			'name' => $name,
			'id'   => $name,
		);
		return '<button'.$this->_html->attributes($attributes).'>'.$label.'</button>';
	}

	public function label($name, $label)
	{
		return '<label for="'.$name.'">'.$label.'</label>';
	}

	public function text($name, $label)
	{
		return $this->input('text', $name, $label);
	}

	public function password($name, $label)
	{
		return $this->input('password', $name, $label);
	}

	public function image($name, $src, $value, $alt = NULL)
	{
		$attributes = array
		(
			'type'  => 'image',
			'name'  => $name,
			'id'    => $name,
			'src'   => $src,
			'value' => $value,
			'alt'   => $alt ? $alt : $name,
		);

		return '<input'.$this->_html->attributes($attributes).' />';
	}
}
