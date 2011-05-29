<?php strpos($_SERVER['PHP_SELF'], 'index.php') OR die('You cannot execute this script.');

class App
{
	const NAME = 'MyMovieLibrary';

	public static function execute()
	{
		// Register the auto-loader that will load the files we need for the classes we instantiate
		if ( ! spl_autoload_register('App::load'))
			throw new RuntimeException('The application\'s auto-loader failed to be registered.');

		// Create the config; it will load our config settings from config.php
		$config = new Config(new SplFileInfo('config.php'));

		// Create and setup the session for use in the application
		$session = Session::instance(App::NAME);

		// Create the dependency injection container that will help us maintain IoC (inversion of control)
		$container = new Container($config, $session);

		// Create the request that will process the URL, find the appropriate controller, and execute it
		$request = $container->build('request');

		return $request->execute();
	}

	public static function load($class)
	{
		// Determine the class's path
		$file = 'classes/'.str_replace('_', '/', strtolower($class)).'.php';

		// Load the file
		if (file_exists($file))
		{
			require $file;
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
}
