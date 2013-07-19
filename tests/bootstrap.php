<?php

// Make sure we have tests directory in our environment
$wp_tests_dir = getenv('WP_TESTS_DIR');
if (!$wp_tests_dir) {
  die("Please define WP_TESTS_DIR environment variable and point it to your wordpress tests dir!\n");
}

// Load wordpress testing code
require_once "$wp_tests_dir/includes/functions.php";

// Define out test suite root directory
define('SWIFTYPE_PLUGIN_DIR', dirname( __FILE__ ) . '/..');

// Create a hook for automatically loading our plugin
function _manually_load_plugin() {
	require SWIFTYPE_PLUGIN_DIR . '/swiftype.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Bootstrap wordpress testing framework
require "$wp_tests_dir/includes/bootstrap.php";

// Load our testcase base class
require dirname( __FILE__ ) . '/swiftype-testcase.php';
