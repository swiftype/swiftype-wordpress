# Swiftype WordPress Plugin

The [Swiftype WordPress Plugin](https://swiftype.com/wordpress) replaces WordPress's default search with a better search engine that is fully customizable via the Swiftype dashboard.

## Installation

1. Go to [http://swiftype.com](http://swiftype.com) and sign up for a free Swiftype account. (Be sure to validate your account via the confirmation email we send.)
2. After logging in to Swiftype, go to the Account Settings screen and get your API key.
3. Install the Swiftype Search Wordpress plugin in your Wordpress dashboard.
4. Activate the plugin through the 'Plugins' menu in WordPress.
5. Go to the Swiftype Search plugin page and enter your Swiftype API key on the first screen.
6. Name your search engine, following the instructions on the screen.
7. Build your search index by clicking the "Synchronize with Swiftype" button.
8. See the Demo video for additional details, or email support@swiftype.com if you are having trouble.

## Development

### Running unit tests

To run the unit tests locally, first install the WordPress unit testing framework and a local copy of WordPress by running `scripts/install-wp-tests.sh <db name> <db user name> <wordpress version>`.

Next, run the tests with `script/run_tests.sh`. This runs the non-Multisite tests against the version of WordPress you installed in the previous commands.

When new commits are pushed, the tests will be run automatically on Swiftype's CI server using several different versions of PHP and WordPress (see `scripts/ci_build.sh`). We are working on making these test runs public.


### Version Tagging

1. Find next version using `git tag --list`
2. Change version number in `README.txt` (1 occurrence) and `swiftype.php` (2 occurrences)
3. Update changelog in `README.txt`
4. Commit updates `git commit -am "bump version"`
5. `git push`
4. Tag version `git tag v1.x.yz`
5. `git push --tags`
6. Publish to WP `./script/publish.sh 1.x.yz`


### Pre-commit hook

There is a pre-commit hook to automatically concatenate and minify JavaScript every time you commit. This is done with [Grunt](http://gruntjs.com/).

1. Install `grunt-cli` (`npm install -g grunt-cli`)
2. Test by running `grunt build`.
3. Install the pre-commit hook:  `ln -s ../../scripts/pre-commit .git/hooks/pre-commit`
