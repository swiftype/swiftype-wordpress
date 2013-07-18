#!/usr/bin/env bash

# Fail on all errors
set +ex

export WP_TESTS_DIR=/tmp/wordpress-testing

BIN_DIR=`dirname $0`
WP_VERSION="$1"

# Prepare testing frameworks and test database
bash $BIN_DIR/install-wp-tests.sh wordpress_test root '' $WP_VERSION

# Run tests
phpunit
