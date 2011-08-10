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
			em {font-style: italic;}
		</style>
	</head>
	<body>
		<div id="content">
			<h1>Install Application</h1>
<?php

error_reporting(0);
echo '<h2>Sanity Checks:</h2>';

$files = array(
	'config' => dirname(__FILE__).'/config.php',
	'schema' => dirname(__FILE__).'/schema.sql',
	'oauth'  => dirname(__FILE__).'/classes/service/netflix/oauth.php',
);
$exists = file_exists('classes');
foreach ($files as $file)
{
	$exists = ($exists AND file_exists($file));
}
$check = $exists ? 'yes' : 'no';
echo '<p class="check '.$check.'">Installation requires all application files to exist.</p>';
if ( ! $exists)
{
	die('<p><em>Cannot complete installation.</em></p>');
}

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
$config = include $files['config'];

// Database connection
$connection = @new MySQLi(
	isset($config['database']['host']) ? $config['database']['host'] : NULL,
	isset($config['database']['username']) ? $config['database']['username'] : NULL,
	isset($config['database']['password']) ? $config['database']['password'] : NULL,
	isset($config['database']['name']) ? $config['database']['name'] : NULL
);
$check = ( ! $connection->connect_error) ? 'yes' : 'no';
$extra_note = ($check == 'yes') ? '' : '<br />&ensp;&rArr;&ensp;<small>Please '
	. 'verify that your database connection credentials are correct in the '
	. '<kbd>config.php</kbd> file and the database name you specified actually '
	. 'exists (You will have to create it manually if it doesn\'t).</small>';
echo '<p class="check '.$check.'">Application requires valid database connection details.'.$extra_note.'</p>';

// Test Netflix API key
$consumer_key = isset($config['netflix']['api_key']) ? $config['netflix']['api_key'] : NULL;
$shared_secret = isset($config['netflix']['secret_key']) ? $config['netflix']['secret_key'] : NULL;
if ($consumer_key && $shared_secret)
{
	include $files['oauth'];
	$oauth = new Service_Netflix_OAuth();
	$signed = $oauth->sign(array(
		'path' => 'http://api.netflix.com/catalog/titles',
		'parameters' => 'term='.urlencode('The Net'),
		'signatures' => array(
			'api_key' => $consumer_key,
			'shared_secret' => $shared_secret,
		),
	));
	if ($api_call = @file_get_contents($signed['signed_url']))
	{
		$api_call = simplexml_load_string($api_call);
	}
	$check = $api_call ? 'yes' : 'no';
}
else
{
	$check = 'no';
}
echo '<p class="check '.$check.'">Application requires valid api key for Netflix Develop API.</p>';

//------------------------------------------------------------------------------

echo '<h2>Setup Database:</h2>';

// Setup Database
if ($success = ( ! $connection->connect_error))
{
	// Check if tables exist
	$missing_tables = array('movielibrary_movies', 'movielibrary_ownerships', 'movielibrary_users');
	$tables = $connection->query('SHOW TABLES');
	while ($table = $tables->fetch_row())
	{
		$index = array_search($table[0], $missing_tables);
		if ($index !== FALSE)
		{
			unset($missing_tables[$index]);
		}
	}
	
	// Execute the queries for building the schema
	if ( ! empty($missing_tables))
	{
		$sql = file_get_contents($files['schema']);
		$previous_query_successful = TRUE;
		foreach (explode(';', $sql) as $query)
		{
			$query = trim($query);
			if ($previous_query_successful AND $query)
			{
				$previous_query_successful = @$connection->query($query);
			}
		}
		$success = $previous_query_successful;
	}
}
$check = $success ? 'yes' : 'no';
$extra_note = $success ? '' : '<br />&ensp;&rArr;&ensp;<small>Either your database '
	. 'connection cannot be established or your database user does not have sufficient '
	. 'permissions to create tables. You may need to run the <kbd>schema.sql</kbd> '
	. 'manually from your database administration tool.</small>';
echo '<p class="check '.$check.'">Application requires database with correct schema.'.$extra_note.'</p>';

?>
		<hr>
	
		<p><strong>NOTE:</strong> If all items are marked in green as "PASS", then the
		application is ready to use and you may delete &ldquo;<code>install.php</code>&rdquo;.</p>

		</div>
	</body>
</html>