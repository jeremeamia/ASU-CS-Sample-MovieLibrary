<?php defined('App::NAME') OR die('You cannot execute this script.');

class Model_Ownership extends Model
{
	protected $_table = 'ownerships';
	protected $_order_by = array('date_added' => 'DESC');
	protected $_fields = array
	(
		'user_id'    => 'int',
		'movie_id'   => 'int',
		'date_added' => 'datetime',
	);

	public function linkMovieToUser(Model_Movie $movie, Model_User $user)
	{
		$movie_valid = ($movie->isLoaded() AND $movie->isValid());
		$user_valid = ($user->isLoaded() AND $user->isValid());

		$this->set(array(
			'user_id'    => $user->get('id'),
			'movie_id'   => $movie->get('id'),
			'date_added' => new DateTime(),
		));

		return $this->create();
	}
}
