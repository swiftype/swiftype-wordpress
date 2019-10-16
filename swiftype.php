<?php

/*
Plugin Name: Swiftype Search
Plugin URI: http://swiftype.com
Description: The Swiftype Search plugin replaces the standard WordPress search with a better search engine that is fully customizable via the Swiftype dashboard. The Swiftype dashboard lets you customize the results for any search keyword via a drag-and-drop interface.
Author: Swiftype, Inc.
Version: 2.0.1
Author URI: http://swiftype.com
*/

define('SWIFTYPE_VERSION', '2.0.2');

require_once('vendor/autoload.php');

require_once 'swiftype-theme-functions.php';

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('swiftype', 'Swiftype\SiteSearch\Wordpress\Cli\Command');
}

new \Swiftype\SiteSearch\Wordpress\SwiftypePlugin();
