<?php defined('App::NAME') OR die('You cannot execute this script.');

class Model_Movie extends Model
{
	protected $_table = 'movies';
	protected $_order_by = array('year' => 'DESC', 'title' => 'ASC');
	protected $_fields = array
	(
		'netflix_id'  => 'string',
		'title'       => 'string',
		'year'        => 'int',
		'mpaa_rating' => 'string',
		'categories'  => 'string',
		'user_rating' => 'float',
		'box_art'     => 'string',
	);
}
