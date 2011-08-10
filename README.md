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
needs to have the `php_mysqli` and `php_curl` extensions installed. These
requirements are checked for in the `install.php` script.

This application also makes use of the Netflix API. You will need to sign up for
a developer account as <http://developer.netflix.com> to acquire your API keys.

### Setup

Once the application is installed to a web server such that it is accessible by
a web browser, there are just a few more things that need to be done.

#### Configuration File

The application comes with a `sample.config.php` file. You should rename this
file to `config.php`, and edit this file to change all of the items in all-caps
to be configured for your installation of the application. This includes your
database credentials, and Netflix API keys.

You will need to adjust line 7 of `config.php` to be the appropriate path to
your application relative to your web-accessible domain name.

#### Database

You ned to setup MySQL for the application. You may create a database
beforehand, or you may let the `install.php` script attempt do it for you. If
you set it up yourself, the `install.php` script will just skip those steps.
(Note: Your MySQL user account may or may not have the permissions to create
databases and tables. You can try running the `install.php` script first, but if
it doesn't work you will need to use a database administration tool to create
your database.) Either way, you need to make sure you have entered the database
credentials and database name in the `config.php` file before running the
`install.php` script.

#### Installation Script

After the above are completed, you can run the `install.php` script which will
confirm that your environment supports all of the application's requirements and
also create an setup the database with the schema required for the application
to run. Once all items pass in the `install.php` script, you can delete it.

#### Setup a User

After the application is running, you will need to go to
`index.php/admin/createuser` in your application via the browser. Enter in the
admin credentials defined in your `config.php` file. Then use the form to create
a user account for the application.

## Testing

### Running Tests

To run the unit tests and see the results, you will need to go to
`index.php/admin/rununittests` in your application via the browser. Enter in the
admin credentials defined in your `config.php` file, if prompted to do so. The
unit tests results page will now be displayed.

You may want to try altering existing tests or adding new ones to experiment.

## Development Todos

- Model unit tests
    - Show how to mock the database
    - Show how to mock the web service
- Doc blocks for helpers and models (time permitting)
