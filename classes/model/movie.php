<?php defined('App::NAME') OR die('You cannot execute this script.');

class Model_Movie extends Model
{
	protected $_table = 'movielibrary_movies';
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

		if ($key == 'user_rating')
		{
			$value = (float) $value;
		}

		return parent::set($key, $value);
	}

	public function getMoviesOwnedByUser(Model_User $user)
	{
		// Form a custom SQL query for the many-to-many relationship
		$sql = 'SELECT `movielibrary_movies`.* FROM `movielibrary_movies` ';
		$sql .= 'INNER JOIN `movielibrary_ownerships` ON `movielibrary_ownerships`.movie_id = `movielibrary_movies`.id ';
		$sql .= 'WHERE `movielibrary_ownerships`.user_id = '.$user->get('id').' ';
		$sql .= 'ORDER BY';
		foreach ($this->_order_by as $field => $direction)
		{
			$sql .= ' '.$field.' '.$direction.',';
		}
		$sql = rtrim($sql, ',').';';

		// Retrieve results
		$results = $this->_database->query($sql);
		$models = $this->_createModelsFromResults($results);
		
		return $models;
	}

	public function getMovieFromNetflix(Service_Netflix_Library $netflix, $netflix_id)
	{
		// First, look it up in our system
		$movie = $this->readFirst('`netflix_id` = "'.$netflix_id.'"');

		if ($movie === NULL)
		{
			// If it doesn't exist in our system, let's check for it on Netflix
			try
			{
				$netflix_movie = (array) $netflix->getMovie($netflix_id);
			}
			catch (Exception $ex)
			{
				$netflix_movie = array();
			}

			$movie = clone $this;
			$movie->set($netflix_movie);

			if ($movie->isValid())
			{
				// It existed on Netflix and returned valid data, let's add it
				$movie->create();
			}
			else
			{
				// The movie could not be found or was invalid, sorry
				$movie = NULL;
			}
		}

		return $movie;
	}

	protected function _validationRules()
	{
		return array(
			'netflix_id' => array(
				'notEmpty' => array(),
				'is_string' => array(),
				'maxLength' => array(100),
			),
			'title' => array(
				'notEmpty' => array(),
				'is_string' => array(),
				'maxLength' => array(75),
			),
			'year' => array(
				'notEmpty' => array(),
				'ctype_digit' => array(),
				'exactLength' => array(4),
			),
			'mpaa_rating' => array(
				'notEmpty' => array(),
				'is_string' => array(),
				'maxLength' => array(10),
			),
			'categories' => array(
				'notEmpty' => array(),
				'is_string' => array(),
				'maxLength' => array(200),
			),
			'user_rating' => array(
				'notEmpty' => array(),
				'is_numeric' => array(),
			),
			'box_art' => array(
				'notEmpty' => array(),
				'is_string' => array(),
				'maxLength' => array(100),
			),
		);
	}
}
