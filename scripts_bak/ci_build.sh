#!/usr/bin/env bash

# Fail on all errors
set -e

# Install the command line retry tool
gem install retryit

# Start MySQL
/etc/init.d/mysql start

export WP_TESTS_DIR=/tmp/wordpress-testing
export WP_DIR=/tmp/wordpress

SCRIPTS_DIR=`dirname $0`

# Prepare testing frameworks and test database
bash $SCRIPTS_DIR/install-wp-tests.sh wordpress_test root '' $WP_VERSION

# Put our plugin in it. Note that the name of the plugin is swiftype-search on WordPress.org
rsync -rv --exclude=.git $SCRIPTS_DIR/.. $WP_DIR/wp-content/plugins/swiftype-search

# Choose test configuration for single blog and MU wordpress
if [ "$WP_MULTISITE" == "1" ]; then
  TEST_CONFIG=tests/multisite.xml
else
  TEST_CONFIG=tests/phpunit.xml
fi

source ~/.phpbrew/bashrc

phpbrew switch $PHP_VERSION
php -v
phpunit --version

#----------------------------------------------------------------------------------------------------
# Run tests
exec phpunit --verbose -c $WP_DIR/wp-content/plugins/swiftype-search/$TEST_CONFIG --strict --log-junit tests/report.xml
