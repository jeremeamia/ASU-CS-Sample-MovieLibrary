<?php defined('App::NAME') OR die('You cannot execute this script.');

return array
(
	'site' => array
	(
		'base_uri'           => '/',
		'title'              => 'My Movie Library',
		'default_controller' => 'movie',
		'default_action'     => 'index',
	),

	'netflix' => array
	(
		'api_key'     => 'YOUR NETFLIX CREDENTIALS HERE',
		'secret_key'  => 'YOUR NETFLIX CREDENTIALS HERE',
		'max_results' => 9,
	),

	'database' => array
	(
		'host'     => 'YOUR DATABASE CREDENTIALS HERE',
		'username' => 'YOUR DATABASE CREDENTIALS HERE',
		'password' => 'YOUR DATABASE CREDENTIALS HERE',
		'name'     => 'YOUR DATABASE CREDENTIALS HERE',
	),

	'hash' => array
	(
		'salt'     => 'Eu&4#Nss1{0m', // A random string at least 10 chars long
		'function' => 'sha1', // sha1, md5, etc.
	),

	'admin' => array
	(
		'username' => 'admin', // You can change this if you want
		'password' => 'test', // You can change this if you want
	),
);
