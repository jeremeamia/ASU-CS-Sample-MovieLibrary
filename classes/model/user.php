<?php defined('App::NAME') OR die('You cannot execute this script.');

class Model_User extends Model
{
	protected $_session;
	protected $_table = 'users';
	protected $_order_by = array('last_name' => 'ASC', 'first_name' => 'ASC');
	protected $_fields = array
	(
		'first_name' => 'string',
		'last_name'  => 'string',
		'email'      => 'string',
		'password'   => 'string',
	);

	public function __construct(Database $database, Config $config, Session $session)
	{
		parent::__construct($database, $config);
		$this->_session = $session;
	}

	public function set($key, $value = NULL)
	{
		if ($key == 'password')
			return parent::set($key, $this->_hashPassword($value));
		else
			return parent::set($key, $value);
	}

	public function login($email, $password)
	{
		if ($this->isAuthenticated())
			return TRUE;

		$users = $this->readAll('`email` = "'.$email.'"');
		if (empty($users))
			return FALSE;

		if ($users[0]->isLoaded() AND $this->_hashPassword($password) == $users[0]->get('password'))
		{
			$this->_session->set('user.id', $users[0]->get('id'));
			return TRUE;
		}

		return FALSE;
	}

	public function logout()
	{
		$this->_session->delete('user.id');
		return $this->_session->destroy();
	}

	public function isAuthenticated()
	{
		$logged_in_id = $this->_session->get('user.id');
		return (bool) ($logged_in_id == $this->get('id') AND $logged_in_id > 0);
	}

	protected function _hashPassword($password)
	{
		$salt = $this->_config->get('hash', 'salt');
		$hash = $this->_config->get('hash', 'function');

		if (strlen($salt) < 10)
			throw new UnexpectedValueException('Hash salts must be at least 10 characters long. Please check the config file.');

		if ( ! is_callable($hash))
			throw new RuntimeException('Could not execute the hash function, because it was not a function.');

		// Split the salt in half
		$salt_halfway = strlen(floor($salt / 2));
		$salt_before = substr($salt, 0, $salt_halfway);
		$salt_after = substr($salt, $salt_halfway + 1);

		// Put the password in between the salt halves and encode the result
		$encoded = base64_encode($salt_before.$password.$salt_after);

		// Hash the encoded, salted password and return the result.
		return call_user_func($hash, $encoded);
	}
}
