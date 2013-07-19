#!/usr/bin/env bash

# Fail on all errors
set +ex

export WP_TESTS_DIR=/tmp/wordpress-testing

SCRIPTS_DIR=`dirname $0`
WP_VERSION="$1"

# Prepare testing frameworks and test database
bash $SCRIPTS_DIR/install-wp-tests.sh wordpress_test root '' $WP_VERSION

# Choose test configuration for single blog and MU wordpress
if [ "$WP_MULTISITE" == "1" ]; then
  TEST_CONFIG=tests/multisite.xml
else
  TEST_CONFIG=tests/phpunit.xml
fi

# Run tests
exec phpunit -c $TEST_CONFIG --strict --log-junit tests/report.xml
