<?php

/*
Plugin Name: Swiftype Search
Plugin URI: http://swiftype.com
Description: The Swiftype Search plugin replaces the standard WordPress search with a better search engine that is fully customizable via the Swiftype dashboard. The Swiftype dashboard lets you customize the results for any search keyword via a drag-and-drop interface.
Author: Swiftype, Inc.
Version: 1.1.46
Author URI: http://swiftype.com
*/

define( 'SWIFTYPE_VERSION', '1.1.46' );

require_once 'class-swiftype-client.php';
require_once 'class-swiftype-error.php';
require_once 'class-swiftype-plugin.php';
require_once 'class-swiftype-widget.php';
require_once 'swiftype-theme-functions.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once 'swiftype-command.php';
}

$swiftype_plugin = new SwiftypePlugin();
