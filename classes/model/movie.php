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

	public function set($key, $value = NULL)
	{
		if ($key == 'categories' AND is_array($value))
		{
			$value = implode(', ', $value);
		}

		return parent::set($key, $value);
	}

	public function getMoviesOwnedByUser(Model_User $user)
	{
		// Form a custom SQL query for the many-to-many relationship
		$sql = 'SELECT `movies`.* FROM `movies` ';
		$sql .= 'INNER JOIN `ownerships` ON `ownerships`.movie_id = `movies`.id ';
		$sql .= 'WHERE `ownerships`.user_id = '.$user->get('id').' ';
		$sql .= 'ORDER BY';
		foreach ($this->_order_by as $field => $direction)
		{
			$sql .= ' '.$field.' '.$direction.',';
		}
		$sql = rtrim($sql, ',').';';

		// Retrieve and return results
		$results = $this->_database->query($sql);
		return $this->_createModelsFromResults($results);
	}
}
