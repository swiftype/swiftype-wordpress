=== Swiftype Search ===
Contributors: matthewtyriley, qhoxie
Donate link:
Tags: search, better search, custom search, relevant search, search by category, autocomplete, suggest, typeahead
Requires at least: 3.3
Tested up to: 4.0
Stable tag: 1.1.39
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Fast, intelligent, and fully customizable search for your site. Comes with detailed analytics and controls in the Swiftype Dashboard.

== Description ==

The Swiftype Search plugin replaces the standard WordPress search with a better, more relevant search engine. It also gives you detailed insight into what your users are searching for, so you know which keywords to target when customizing your search engine results. The Swiftype search plugin is WordPress VIP-approved and already used on huge sites.  The search plugin is complemented by our dashboard, [full-featured developer API](https://swiftype.com/search-api), and [powerful analytics](https://swiftype.com/search-analytics).  Manage search results with drag and drop and see the changes reflected instantly.

The base ranking algorithm is based on industry best-practices for search and provides more relevant results by default, but we also allow for any result set to be fully customized via our drag-and-drop interface for result reordering. To make customizations you simply create a Swiftype account and install the Swiftype Search plugin. You can then login to our dashboard to customize results and read through detailed search analytics. See the short demo video below for more details.

Do you have a mobile app displaying content from your WordPress site? Swiftype’s [mobile SDKs](https://swiftype.com/mobile) make it simple to add powerful search to your mobile apps.  Combine our WordPress plugin with our mobile SDKs to create the same search experience on your site and in your app.


[youtube="http://www.youtube.com/watch?v=rukXYKEpvS4"]

## Features

* Search runs on our powerful servers - it doesn't bog down your site, even if you have **hundreds of thousands of posts**.
* Works with your theme's search.php template - drop in the Swiftype search plugin and it **just works**.
* Fast typeahead autocomplete search suggestions based on titles, tags, and author names.
* Search results **automatically update** when you save, delete, or change search content.
* **Re-order search results** with drag-and-drop from your Swiftype Dashboard.
* Impactful search analytics help you understand your users.

## Advanced Customization

* Modify what types of posts and categories are searched or weight fields like title more heavily by adding a filter.
* Change the autocomplete behavior with JavaScript.

Read our [WordPress search customization tutorial](https://swiftype.com/documentation/tutorials/customizing_wordpress_search) for details.

== Installation ==

1. Go to [http://swiftype.com](http://swiftype.com) and sign up for a free Swiftype account. (Be sure to validate your account via the confirmation email we send.)
2. After logging in to Swiftype, go to the Account Settings screen and get your API key.
3. Install the Swiftype Search Wordpress plugin in your Wordpress dashboard.
4. Activate the plugin through the 'Plugins' menu in WordPress.
5. Go to the Swiftype Search plugin page and enter your Swiftype API key on the first screen.
6. Name your search engine, following the instructions on the screen.
7. Build your search index by clicking the "Synchronize with Swiftype" button.

See the Demo video for additional details, or email support@swiftype.com if you are having trouble.

== Screenshots ==

1. Detailed, real-time analytics show you what your users are searching for, click on, and even what they are having trouble finding.
2. Result controls let you customize everything – reorder results, remove bad results, and add custom results.

== Frequently asked questions ==

_If your questions are not answered here, check our [Q&A forum](https://swiftype.com/questions), or email [support@swiftype.com](mailto:support@swiftype.com) for help._

= Where do I sign up for a Swiftype search account? =

Sign up for a free account at [http://swiftype.com](http://swiftype.com)

= Why don't search results in the Swiftype Dashboard match what's displayed on my site? =

This is usually caused by your theme not using `query_posts` properly and affecting search as a result. Fortunately, [it is easy to fix](https://swiftype.com/questions/why-dont-the-search-results-in-my-swiftype-dashboard-match-what-is-displayed-on-my-wordpress-site) the search results.

= Does Swiftype search support WordPress Multisite? =

You can install the plugin for each site with its own search engine, but we don't have a way yet to index an entire Multisite network yet. But stay tuned!

== Screenshots ==

1. The Swiftype search analytics dashboard.  Here you see your Top Queries, Top Content by Click-throughs, Top Queries with No Result, and Search Trends over time.
2. The Swiftype search result controls dashboard.  Here you can customize any aspect of your results.  You can drag and drop to reoder search results, remove results you don't want to show up in the search, and even add results that don't show up automatically.

== Changelog ==

= 1.1.39 =
* Fix error that caused some titles in the autocomplete dropdown to display as "undefined".

= 1.1.38 =
* Add `swiftype_render_facets` theme function to support faceting on the search results page.

= 1.1.37 =
* Tested with WordPress 4.0.

= 1.1.36 =
* WP-CLI commands. If you use [WP-CLI](http://wp-cli.org/), you can now use the command line to index your content much faster. Great for large sites. Type `wp swiftype` in your WordPress directory for details.
* Add `swiftype_search_query_string` filter to make modifying the query easier. Thanks to Paul Morrison for the patch.

= 1.1.35 =
* Streamline script concatenation.

= 1.1.34 =
* Skip NULL documents in indexing API request. You can use the `swiftype_document_builder` filter to exclude documents from the search engine by returning NULL.
* Remove development files from the released version of the plugin for smaller download size.

= 1.1.33 =
* Fix an issue that caused the plugin to repeatedly check for API authorization in the WordPress admin.

= 1.1.32 =
* Faster assets using our CDN.

= 1.1.31 =
* Better support for WordPress 3.8.

= 1.1.30 =
* Allow editors and contributors to update posts in index.

= 1.1.29 =
* Allow JavaScript functions for autocomplete dropdown configuration.
* Add theme functions for accessing Swiftype search results and total number of results.
* Add unit tests to the plugin.

= 1.1.28 =
* Add support for tracking search result clicks. Now you'll be able see top clicked content in the Swiftype Dashboard.
* Fix an issue displaying search results when a deleted post hasn't been deleted from Swiftype.

= 1.1.27 =
* Add support for customizing functional boosts and number of items returned by the autocomplete, as well as disabling the autocomplete entirely
* Handle thumbnail URLs that fail to load

= 1.1.26 =
* Work around SSL verification issue on some systems

= 1.1.24 =
* Added a filter to the end of document creation to allow for additional fields to be indexed.
* Moving all api calls to https

= 1.1.22 =
* Remove API calls on set engine screen

= 1.1.20 =
* Allow customization of autocomplete through JavaScript variables

= 1.1.19 =
* Rename category filter search widget

= 1.1.18 =
* Improved synchronization of posts with Swiftype

= 1.1.17 =

* Support for indexing custom post types
* Improved synchronization of posts with Swiftype

= 1.0 =

Initial release.

== Upgrade Notice ==

= 1.1.26 =
This fixes an important issue for many users that prevented synchronization of new posts and first time authentication.  Please upgrade immediately and resynchronize your posts.

== WP-CLI Support ==

Swiftype Search supports [WP-CLI](http://wp-cli.org/).

To see the available commands type `wp swiftype` in your WordPress install directory.

The CLI is particularly useful if you have a large number of posts (more than 10,000) because it can synchronize posts with Swiftype more quickly by controlling the indexing batch size. Also, the CLI provides an option to destructively reindex the contents of your WordPress site which will be faster if you have a large number of deleted posts or simply want to start fresh.

To index your WordPress site with the CLI, make sure you have WP-CLI installed and configure the plugin with your API key and search engine. Then run:

`wp swiftype sync`

To index more content at once, increase the batch size:

`wp swiftype sync --index-batch-size 100`

In general, using a larger batch size will be faster. However, the size of the batch you can use is determined by how large your posts are on average.

== Modifying search parameters ==

The Swiftype Search plugin provides a filter called `swiftype_search_params` that allows you to modify the search parameters before they are sent to Swiftype.

For example, to add a filter so only pages are returned by the search results, you can add the following to your functions.php file:

    function swiftype_search_params_filter( $params ) {
        $params['filters[posts][object_type]'] = array( 'page' );

        return $params;
    }

    add_filter( 'swiftype_search_params', 'swiftype_search_params_filter', 8, 1 );

For more details, check out our [WordPress customization tutorial](https://swiftype.com/documentation/tutorials/customizing_wordpress_search) and the Swiftype [Search API documentation](https://swiftype.com/documentation/searching).

== Modifying the query string ==

The Swiftype Search plugin provides a filter called `swiftype_search_query_string` that makes it easier to modify the query string before it is sent to Swiftype.

For example, to add a term to every query string, you can add the following to your functions.php file:

    function swiftype_search_query_filter( $query ) {
        return $query . ' ThisWillAlwaysBeInEveryQuery';
    }

    add_filter( 'swiftype_search_query_string', 'swiftype_search_query_filter', 8, 1 );

You can use this filter to pre-process queries before they are executed.

== Adding Faceted Search Results ==

The Swiftype Search plugin provides the `swiftype_render_facets` theme function to allow you to easily add faceting to your search results page. To use it, follow these steps:

1.  Add a `swiftype_search_params_filter` function to your theme's `functions.php` file that adds facet parameters to the search. 

    The version below will facet on tags and category:

        function swiftype_search_params_filter( $params ) {
            $params['facets[posts]'] = array( 'tags', 'category' );
            return $params;
        }

        add_filter( 'swiftype_search_params', 'swiftype_search_params_filter', 8, 1 );

2.  Add a `swiftype_render_facets()` call to your `search.php` template. This is what actually renders the facets. You should place it where you want the facets to be rendered:

        <?php swiftype_render_facets(); ?>

3.  (Optional) Style the facets using CSS. You can target `div.st-facets` for your CSS rules to only affect the content inside the facets container.
