=== Swiftype Site Search Plugin for Wordpress===
Contributors: matthewtyriley, qhoxie, afoucret, jasonstoltz, goodroot
Donate link:
Tags: search, better search, custom search, relevant search, search by category, autocomplete, suggest, typeahead
Requires at least: 3.3
Tested up to: 5.5.1
Stable tag: 2.0.4
License: Apache 2.0
License URI: https://github.com/swiftype/swiftype-wordpress/blob/master/LICENSE

Fast, intelligent, and fully customizable search for your site.

== Description ==

The Site Search Wordpress plugin replaces the standard WordPress search with a polished, customizable, and more relevant search engine. Gain access to deep search insights and all the tools you need to customize and perfect your search experience. Join thousands of growing customers and bring world class search to your website, all backed under the hood by Elasticsearch.

[What is Site Search?](https://swiftype.com/site-search)

## Features

* **Fully managed**: We secure, store, and search all of your documents in the cloud. Your site stays fast.
* **No programming required**: Works with your theme's existing search.php template. Drop in the Site Search plugin and it _just works_.
* **Out of the box relevance**: Pre-optimized typo tolerance, bigram matching, stemming, synonyms, phrase matching, and more.
* **Automatic Updating**: Search results _automatically synchronize_ when you save, delete, or change Wordpress content.
* **Intuitive Dashboard**: Use slick and powerful dashboard tools to customize your search relevance.
* **Deep Insights**: Impactful search analytics help you understand your users and guide you to productive actions.
* **Choose Your Language**: Supports 13 languages, including: English, French, German, Russian, Chinese, Japanese, Universal, and more.

Read the [Site Search WordPress guide](https://swiftype.com/documentation/site-search/guides/wordpress) for more details.

== Installation ==

1. Go to [swiftype.com](https://swiftype.com/free-trial?utm_channel=readme-web&utm_source=wordpress-org) and sign up for an account.
2. After logging in to Site Search, get your API Key from the dashboard.
3. Install the Site Search Wordpress plugin from the Wordpress dashboard.
4. Activate the plugin through the 'Plugins' menu in WordPress.
5. Go to the Site Search plugin page and enter your Site Search API Key on the first screen.
6. Name your search engine, choose from one of 13 languages, then create it.
7. Build your search index by clicking the "Synchronize" button.
8. Search!

Email support@swiftype.com if you are having trouble.

== Screenshots ==

1. Adjust Weights to fine tune search relevance for different post values.
2. View advanced, real time analytics. Gain insights that will help you perfect your search experience.
3. Promote or hide documents from your search results, so that searchers always find just the right thing.

== Frequently asked questions ==

_If your questions are not answered here, check out the [Site Search Community forum](https://discuss.elastic.co/c/site-search), or email [support@swiftype.com](mailto:support@swiftype.com) for help._

= Where do I sign up for a Swiftype Site Search account? =

Sign up for an account at [https://swiftype.com](http://swiftype.com).  Plans start at $79.

= Why don't search results in the Site Search Dashboard match what's displayed on my site? =

Your posts may have fallen out of sync. Click 'Synchronize' from within the plugin to correct this.

== Screenshots ==

1. The Swiftype Site Search analytics dashboard.  Here you see your Top Queries, Top Content by Click-throughs, Top Queries with No Result, and Search Trends over time.
2. The Swiftype Site Search result controls dashboard.  Here you can customize any aspect of your results.  You can drag and drop to reoder search results, remove results you don't want to show up in the search, and even add results that don't show up automatically.

== Changelog ==

= 2.0.4 =
* Fix composer package type
* Fix error reporting during config and synchronization

= 2.0.3 =
* Update Site Search client to the latest

= 2.0.2 =
* WP-CLI fixes and action refactoring

= 2.0.1 =
* Polishes README.txt
* Remove trailing quote in language list

= 2.0.0 =
* Using the new official Swiftype Site Search PHP client.
* Complete refactoring of the module.
* New admin user interface
* Facet management from the admin
* New rendering of the facet filters in search results.

= 1.1.50 =
* Bugfix for Issue #34: Removed undefined variable `$retries`.
* Bugfix for Issue #35: Removed use of deprecated `create_function` method.

= 1.1.46 =
* Export JS changes to min files.

= 1.1.45 =
* Added option to override renderFunction.

= 1.1.44 =
* Added documentation.
* Added a public function for developers.

= 1.1.43 =
* Made deletion message clear that we only delete trashed posts.

= 1.1.42 =
* Minor updates

= 1.1.41 =
* Made compatible with WordPress 4.1.

= 1.1.40 =
* Refactor public search api to allow for integration options with other plugins.
* Tested with WordPress 4.1.

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
