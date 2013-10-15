<?php

/**
 * Return the Swiftype search results object.
 *
 * Use this after a search has been executed to get access to the raw results
 * from Swiftype. This allows you to access facets and other result metadata.
 *
 * @global SwiftypePlugin $swiftype_plugin The SwiftypePlugin instance.
 *
 * @return Array An array or NULL if no search has been executed.
 */
function swiftype_search_results() {
	global $swiftype_plugin;

	return $swiftype_plugin->results();
}

/**
 * Return the number of results.
 *
 * Use this after a search has been executed to get the number of search 
 * results.
 *
 * @global SwiftypePlugin $swiftype_plugin The SwiftypePlugin instance.
 *
 * @return integer
 */
function swiftype_total_result_count() {
	global $swiftype_plugin;

	return $swiftype_plugin->get_total_result_count();
}
