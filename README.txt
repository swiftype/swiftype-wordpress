=== Swiftype Search ===
Contributors: matthewtyriley, qhoxie
Donate link:
Tags: search, better search, custom search, relevant search, search by category, autocomplete, suggest, typeahead
Requires at least: 3.3
Tested up to: 3.5
Stable tag: 1.1.23
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Swiftype Search plugin replaces the standard WordPress search with a better search engine that is fully customizable via the Swiftype dashboard.

== Description ==

The Swiftype Search plugin replaces the standard WordPress search with a better, more relevant search engine. It also gives you detailed insight into what your users are searching for, so you know which keywords to target when customizing your search engine results. The base ranking algorithm is based on industry best-practices and provides more relevant results by default, but we also allow for any result set to be fully customized via our drag-and-drop interface for result reordering. To make customizations you simply create a Swiftype account and install our the Swiftype Search plugin. You can then login to our dashboard to customize results and read through detailed search analytics. See the short demo video below for more details.

[youtube="http://www.youtube.com/watch?v=rukXYKEpvS4"]

## Features

* Search runs on our powerful servers - it doesn't bog down your site, even if you have **hundreds of thousands of posts**.
* Works with your theme's search.php template - drop in Swiftype and it **just works**.
* Fast typeahead autocomplete based on titles, tags, and author names.
* Search results **automatically update** when you save or delete content.
* **Re-order search results** with drag-and-drop from your Swiftype Dashboard.
* Impactful search analytics help you understand your users.

## Advanced Customization

* Modify what types of posts, categories are searched by adding a hook.
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

== Frequently asked questions ==

_If your questions are not answered here, check our [Q&A forum](https://swiftype.com/questions), or email [support@swiftype.com](mailto:support@swiftype.com) for help._

= Where do I sign up for a Swiftype account? =

Sign up for a free account at [http://swiftype.com](http://swiftype.com)

= Why don't search results in the Swiftype Dashboard match what's displayed on my site? =

This is usually caused by your theme not using `query_posts` properly. Fortunately, [it is easy to fix](https://swiftype.com/questions/why-dont-the-search-results-in-my-swiftype-dashboard-match-what-is-displayed-on-my-wordpress-site).

= Does Swiftype support WordPress Multisite? =

You can install the plugin for each site with its own search engine, but we don't have a way yet to index an entire Multisite network yet. But stay tuned!

== Screenshots ==

== Changelog ==

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

== Upgrade notice ==
