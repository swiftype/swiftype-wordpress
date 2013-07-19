<?php

$wp_tests_dir = getenv('WP_TESTS_DIR');
if (!$wp_tests_dir) {
  die("Please define WP_TESTS_DIR environment variable and point it to your wordpress tests dir!\n");
}

require_once "$wp_tests_dir/includes/functions.php";

function _manually_load_plugin() {
	require dirname( __FILE__ ) . '/../swiftype.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

require "$wp_tests_dir/includes/bootstrap.php";

// Load our testcase base class
require dirname( __FILE__ ) . '/swiftype-testcase.php';
