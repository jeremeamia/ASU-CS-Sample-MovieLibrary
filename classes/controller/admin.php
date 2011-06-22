<?php defined('App::NAME') OR die('You cannot execute this script.');

abstract class Controller_Admin extends Controller_Page
{
	protected $_title = NULL;
	protected $_template = 'admin';

	public function preExecute()
	{
		$config = $this->getContainer()->getConfig();
				
		$is_http_auth = (isset($_SERVER['PHP_AUTH_USER']) AND isset($_SERVER['PHP_AUTH_PW']));
		$admin_user_correct = ($_SERVER['PHP_AUTH_USER'] == $config->get('admin', 'username'));
		$admin_pass_correct = ($_SERVER['PHP_AUTH_PW'] == $config->get('admin', 'password'));
		
		if ( ! $is_http_auth OR ! $admin_user_correct OR ! $admin_pass_correct)
		{
			header('WWW-Authenticate: Basic realm="'.$config->get('site', 'title').'"');
			header('HTTP/1.0 401 Unauthorized');
			echo 'You cannot access the Admin Tools for '.$config->get('site', 'title').'.';
			exit(0);
		}
	}

	public function getUser()
	{
		return NULL;
	}
}
