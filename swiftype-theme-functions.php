<?php

function swiftype_search_results() {
	global $swiftype_plugin;

	return $swiftype_plugin->results();
}

function swiftype_total_result_count() {
	global $swiftype_plugin;

	return $swiftype_plugin->get_total_result_count();
}
