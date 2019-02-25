<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Swiftype_Search
 */

$testsDir = getenv('WP_TESTS_DIR');

if (!$testsDir) {
    $testsDir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

if (!file_exists( $testsDir . '/includes/functions.php')) {
    echo "Could not find $testsDir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // WPCS: XSS ok.
    exit(1);
}

// Give access to tests_add_filter() function.
require_once $testsDir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
    require_once dirname(dirname(__FILE__)) . '/swiftype.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment.
require $testsDir . '/includes/bootstrap.php';

