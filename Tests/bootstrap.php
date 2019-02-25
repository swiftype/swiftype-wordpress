<?php

// Make sure we have tests directory in our environment.
$wpTestsDir =  __DIR__ . '/../../../../../tests/phpunit/tests';

// Make sure the plugin is enabled.
$GLOBALS['wp_tests_options'] = ['active_plugins' => ['swiftype-search/swiftype.php']];

// Define out test suite root directory
define('SWIFTYPE_PLUGIN_DIR', dirname( __FILE__ ) . '/..');

// Bootstrap wordpress testing framework
require "$wpTestsDir/bootstrap.php";

error_reporting(E_ALL);

set_error_handler("test_error_handler", E_ALL);

$wp_test_errors = array();
function test_error_handler($errno, $errstr, $errfile) {
  global $wp_test_errors;

  array_push($wp_test_errors, $errstr . " in " . $errfile);
  return NULL; // execute default error handler
}

// Load our testcase base class
require dirname( __FILE__ ) . '/swiftype-testcase.php';
