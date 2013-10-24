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

// Load our testcase base class
require dirname( __FILE__ ) . '/swiftype-testcase.php';
