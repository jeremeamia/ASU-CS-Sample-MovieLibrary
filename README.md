# My Movie Library

By [Jeremy Lindblom](http://webdevilaz.com)

This app is a sample app for the CSE program at ASU. The purpose of this app is
to show good object-oriented design required for effective unit testing. This
app will be packaged with sample unit tests to illustrate good testing
practices.

## Installation Instructions

### Requirements

This application requires a standard LAMP stack with PHP 5.2+, MySQL, and
Apache. PHP should have `short_tags` enabled for use in HTML templates. Also PHP
needs to have the `php_mysqli` and `php_curl` extensions installed.

This application also makes use of the Netflix API. You will need to sign up for
a developer account as <http://developer.netflix.com> to axquire your API keys.

### Setup

Once the application is installed to a web server such that it is accessible by
a web browser, there are just a few more things that need to be done.

#### Database

You should set up a mysql database for the application. The name is not
important, but you will need to remember the name to put into the config file
later. The `schema.sql` file contains the SQL code you need to run to setup the
tables.

#### Sample Files

The application comes with 2 sample files:

- `sample.config.php` - You should rename this file to `config.php`. You should
also edit this file change all of the items in all-caps to be configured for
your installation of the application.
- `sample.htaccess` - You should rename this file to `.htaccess`.

You will need to adjust line 8 of `.htaccess` and line 7 of `config.php` to be
the appropriate path to your application relative to the domain name.

## Todo
- Model unit tests
    - Show how to mock the database
    - Show how to mock the web service
- Unit tests for other classes
- Doc blocks on all classes
- Improve design