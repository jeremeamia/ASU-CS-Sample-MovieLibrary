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

		if ( ! $movie_valid OR ! $user_valid)
			throw new UnexpectedValueException('The movie and user must both be valid to be linked to each other.');

		$this->set(array(
			'user_id'    => $user->get('id'),
			'movie_id'   => $movie->get('id'),
			'date_added' => new DateTime(),
		));

		return $this->create();
	}

	public function unlinkMovieFromUser(Model_Movie $movie, Model_User $user)
	{
		if ( ! $movie->isLoaded() OR ! $user->isLoaded())
			throw new UnexpectedValueException('The movie and user must both be loaded to be unlinked from each other.');

		$ownership = $this->readFirst('`movie_id` = "'.$movie->get('id').'" AND `user_id` = "'.$user->get('id').'"');

		if ( ! $ownership->isLoaded())
			throw new RuntimeException('The user did not own the movie that was to be unlinked.');

		return $ownership->delete();
	}
}
