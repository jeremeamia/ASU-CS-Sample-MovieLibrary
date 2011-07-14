<?php
/**
 * My Movie Library
 *
 * This app is a sample app for the CSE program at ASU. The purpose of this app
 * is to show good object-oriented design required for  effective unit testing.
 * This app will be packaged with sample unit tests to illustrate good testing
 * practices.
 *
 * @author     Jeremy Lindblom
 * @copyright  Copyright (c) 2011 Jeremy Lindblom
 */
require 'classes/app.php';
$app = new App();
echo $app->execute()->renderResponse();
