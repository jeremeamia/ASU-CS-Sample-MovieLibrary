<?php strpos($_SERVER['PHP_SELF'], 'index.php') OR die('You cannot execute this script.');

class App
{
	const NAME = 'MyMovieLibrary';

	protected $_output = NULL;

	public function execute()
	{
		// Register the auto-loader that will load the files we need for the classes we instantiate
		if ( ! spl_autoload_register('App::load'))
			throw new RuntimeException('The application\'s auto-loader failed to be registered.');

		// Create the dependency injection container that will help us maintain IoC (inversion of control)
		$container = new Container();

		// Create the request (via the container) that will process the URL, find the appropriate controller, and execute it
		$request = $container->getRequest();
		$this->_output = $request->execute();

		return $this;
	}

	public function renderOutput()
	{
		return (string) $this->_output;
	}

	public static function load($class)
	{
		// Determine the class's path
		$file = dirname(__FILE__).'/'.str_replace('_', '/', strtolower($class)).'.php';

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
