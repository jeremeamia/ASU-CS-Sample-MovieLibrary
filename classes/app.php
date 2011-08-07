<?php strpos($_SERVER['PHP_SELF'], 'index.php') OR die('You cannot execute this script.');
/**
 * The App class represents the Application itself and allows for its
 * encapsulated execution.
 */
class App
{
	/**
	 * @constant string The name of the application
	 */
	const NAME = 'MyMovieLibrary';

	/**
	 * @var string Contains the output of the application
	 */
	protected $_output = NULL;

	/**
	 * Executes the app by creating required objects and initiating the request
	 *
	 * @chainable
	 * @throws RuntimeException
	 * @return App
	 */
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

	/**
	 * Returns the output of the application
	 *
	 * @return string
	 */
	public function renderOutput()
	{
		return (string) $this->_output;
	}

	/**
	 * Loads a class by locating and importing its file. Can be used statically
	 * or as an auto-loader.
	 *
	 * @static
	 * @param string $class Name of the class to be loaded
	 * @return bool
	 */
	public static function load($class)
	{
		// Determine the class's path
		$file = dirname(__FILE__).'/'.str_replace('_', '/', strtolower($class)).'.php';

		// Load the file
		if (file_exists($file) AND ! class_exists($class, FALSE))
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
