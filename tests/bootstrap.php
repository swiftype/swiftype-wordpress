<?php

// Make sure we have tests directory in our environment
$wp_tests_dir = getenv('WP_TESTS_DIR');
if (!$wp_tests_dir) {
  die("Please define WP_TESTS_DIR environment variable and point it to your wordpress tests dir!\n");
}

$GLOBALS['wp_tests_options'] = array(
  'active_plugins' => array( "swiftype-search/swiftype.php" ),
);

// Load wordpress testing code
require_once "$wp_tests_dir/includes/functions.php";

// Define out test suite root directory
define('SWIFTYPE_PLUGIN_DIR', dirname( __FILE__ ) . '/..');

// Bootstrap wordpress testing framework
require "$wp_tests_dir/includes/bootstrap.php";

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
