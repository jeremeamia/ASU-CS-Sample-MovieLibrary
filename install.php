<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title>Install Application</title>
		<!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
		<link href="assets/boilerplate.css" rel="stylesheet">
		<style>
			body {margin: 1em 2em; color: #666;}
			h1 {font-size: 2em; margin-bottom: 1em; border-bottom: 3px solid #666;}
			h2 {font-size: 1.5em; margin-bottom: 1em;}
			p.check {padding: 1em; margin-bottom: 1em; border: 3px solid #666; font-weight: bold;}
			p.yes {background-color: #cfc; color: #060; border-color: #8c8;}
			p.yes:before {content: '[SUCCESS]'; padding-right: 0.5em; opacity: 0.5;}
			p.no {background-color: #fcc; color: #600; border-color: #c88;}
			p.no:before {content: '[FAIL]'; padding-right: 0.5em; opacity: 0.5;}
		</style>
	</head>
	<body>
		<h1>Install Application</h1>
<?php

echo '<h2>Compatibility Checks:</h2>';

// Check PHP version
$version = explode('.', PHP_VERSION);
$check = ($version[0] >= 5 OR $version[1] >= 2) ? 'yes' : 'no';
echo '<p class="check '.$check.'">Application requires PHP version 5.2 or higher.</p>';

// Check short tags
$check = ini_get('short_open_tag') ? 'yes' : 'no';
echo '<p class="check '.$check.'">Application requires PHP short tags to be enabled.</p>';

// cURL extension
$check = extension_loaded('curl') ? 'yes' : 'no';
echo '<p class="check '.$check.'">Application requires the cURL PHP extension.</p>';

// MySQLi extension
$check = extension_loaded('mysqli') ? 'yes' : 'no';
echo '<p class="check '.$check.'">Application requires the MySQLi PHP extension.</p>';

//------------------------------------------------------------------------------

echo '<h2>Test Connections:</h2>';

$config = include 'config.php';

// Database connection
$connection = new MySQLi(
	isset($config['database']['host']) ? $config['database']['host'] : NULL,
	isset($config['database']['username']) ? $config['database']['username'] : NULL,
	isset($config['database']['password']) ? $config['database']['password'] : NULL,
	isset($config['database']['name']) ? $config['database']['name'] : NULL
);
$check = ( ! $connection->connect_error) ? 'yes' : 'no';
echo '<p class="check '.$check.'">Application requires valid database connection details.</p>';

// @TODO Test Netflix connection

//----------------------------------------------------------------------------

echo '<h2>Setup Database:</h2>';

// @TODO Setup database

?>
	</body>
</html>