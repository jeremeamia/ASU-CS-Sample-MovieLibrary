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

	public function getMovieFromNetflix(Service_Netflix_Library $netflix, $netflix_id)
	{
		// Verify that the ID passed in is an integer
		if ( ! ctype_digit(strval($netflix_id)))
			return NULL;

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
}
