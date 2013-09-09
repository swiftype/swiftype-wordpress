<?php

/*
Plugin Name: Swiftype Search
Plugin URI: http://swiftype.com
Description: The Swiftype Search plugin replaces the standard WordPress search with a better search engine that is fully customizable via the Swiftype dashboard. The Swiftype dashboard lets you customize the results for any search keyword via a drag-and-drop interface.
Author: Swiftype, Inc.
Version: 1.1.28
Author URI: http://swiftype.com
*/

define( 'SWIFTYPE_VERSION', '1.1.28' );

require_once 'class-swiftype-client.php';
require_once 'class-swiftype-error.php';
require_once 'class-swiftype-plugin.php';
require_once 'class-swiftype-widget.php';
require_once 'swiftype-theme-functions.php';

$swiftype_plugin = new SwiftypePlugin();
