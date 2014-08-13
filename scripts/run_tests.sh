#! /bin/sh 

# Simpler test script for running tests iteratively. 
# You must have wordpress-testing and wordpress installed already.

export WP_TESTS_DIR=/tmp/wordpress-testing
export WP_DIR=/tmp/wordpress
SCRIPTS_DIR=`dirname $0`

rsync --recursive --quiet --exclude=.git $SCRIPTS_DIR/.. $WP_DIR/wp-content/plugins/swiftype-search
phpunit -c $WP_DIR/wp-content/plugins/swiftype-search/tests/phpunit.xml