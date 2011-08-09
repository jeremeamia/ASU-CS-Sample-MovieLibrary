<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title>Install Application</title>
		<!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
		<link href="assets/boilerplate.css" rel="stylesheet">
		<style>
			body {color: #666;}
			div#content {width: 800px; margin: 1em auto;}
			h1 {font-size: 2em; margin-bottom: 1em; border-bottom: 3px solid #666;}
			h2 {font-size: 1.5em; margin-bottom: 1em;}
			hr {border: 0; color: #666; background-color: #666; height: 3px; margin: 2em 0;}
			p.check {padding: 1em; margin-bottom: 1em; border: 3px solid #666; font-weight: bold;}
			p.yes {background-color: #cfc; color: #060; border-color: #8c8;}
			p.yes:before {content: '[PASS]'; padding-right: 0.5em; opacity: 0.5;}
			p.no {background-color: #fcc; color: #600; border-color: #c88;}
			p.no:before {content: '[FAIL]'; padding-right: 0.5em; opacity: 0.5;}
			code {background-color: #eee; font-weight: bold;}
		</style>
	</head>
	<body>
		<div id="content">
			<h1>Install Application</h1>
<?php

echo '<h2>Sanity Checks:</h2>';

$check = (file_exists('config.php') AND file_exists('schema.sql') AND file_exists('classes')) ? 'yes' : 'no';
echo '<p class="check '.$check.'">Installation requires all application files to exist.</p>';

//------------------------------------------------------------------------------

echo '<h2>Compatibility Checks:</h2>';

// Check PHP version
$version = explode('.', PHP_VERSION);
$check = ($version[0] >= 5 OR $version[1] >= 2) ? 'yes' : 'no';
echo '<p class="check '.$check.'">Application requires PHP version 5.2 or higher.</p>';

// SimpleXML Available
$check = function_exists('simplexml_load_string') ? 'yes' : 'no';
echo '<p class="check '.$check.'">Application requires the SimpleXML extension.</p>';

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

echo '<h2>Configuration:</h2>';

class App {const NAME = TRUE;} // Declares a constant needed by config file
$config = include 'config.php';

// Database connection
$connection = new MySQLi(
	isset($config['database']['host']) ? $config['database']['host'] : NULL,
	isset($config['database']['username']) ? $config['database']['username'] : NULL,
	isset($config['database']['password']) ? $config['database']['password'] : NULL
);
$check = ( ! $connection->connect_error) ? 'yes' : 'no';
echo '<p class="check '.$check.'">Application requires valid database connection details.</p>';

// Test Netflix API key
$consumer_key = isset($config['netflix']['api_key']) ? $config['netflix']['api_key'] : NULL;
if ($consumer_key)
{
	$url = 'http://api.netflix.com/catalog/titles/autocomplete?oauth_consumer_key='.$consumer_key.'&term=The%20Net';
	$api_call = simplexml_load_string(file_get_contents($url));
	$check = ($api_call !== NULL) ? 'yes' : 'no';
}
else
{
	$check = 'no';
}
echo '<p class="check '.$check.'">Application requires valid api key for Netflix Develop API.</p>';

//------------------------------------------------------------------------------

echo '<h2>Setup Database:</h2>';

// Setup Database
$database_name = isset($config['database']['name']) ? $config['database']['name'] : NULL;
$success = FALSE;
if ($database_name)
{
	// Create the database if it exists
	$database_exists = $connection->select_db($database_name);
	if ( ! $database_exists)
	{
		$sql = 'CREATE DATABASE IF NOT EXISTS '.$connection->real_escape_string($database_name);
		$database_exists = $connection->query($sql);

		// For some reason I must refresh the request in order for the schema queries to work
		header('Location: '.$_SERVER['REQUEST_URI']);
		exit;
	}

	// Execute the queries for building the schema
	if ($database_exists)
	{
		$sql = file_get_contents('schema.sql');
		$previous_query_successful = TRUE;
		foreach (explode(';', $sql) as $query)
		{
			$query = trim($query);
			if ($previous_query_successful AND $query)
			{
				$previous_query_successful = $connection->query($query);
			}
		}
		$success = $previous_query_successful;
	}
}
$check = $success ? 'yes' : 'no';
echo '<p class="check '.$check.'">Application requires database with correct schema in place.</p>';

?>
		<hr>
	
		<p><strong>NOTE:</strong> If all items are marked in green as "PASS", then the
		application is ready to use and you may delete &ldquo;<code>install.php</code>&rdquo;.</p>

		</div>
	</body>
</html>