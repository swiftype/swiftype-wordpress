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

/**
 * Echo a facet listing from the search results. This should only be used on
 * the search results page, because Swiftype search results must be present.
 *
 * You must modify the Swiftype query parameters to request facets.
 *
 * Facets are rendered inside a <div> with class st-facets.
 *
 * @return void
 */
function swiftype_render_facets() {
	$results = swiftype_search_results();

	$facets = $results['info']['posts']['facets'];


	if ( empty( $facets ) ) {
		return '';
	}

	$html = '<div class="st-facets">';

	foreach ( $facets as $facet_field => $facet_values ) {
		if ( empty($facet_values) ) {
			continue;
		}

		$html .= "<h4>" . esc_html($facet_field) . "</h4>";
		$html .= '<ul>';

		foreach ( $facet_values as $facet_term => $facet_count ) {
			if ( trim( $facet_term ) === '' ) {
				continue;
			}

			$facet_display = $facet_term;

			// special case for category since it's stored as an ID
			if ( $facet_field == 'category' ) {
				$facet_display = get_cat_name( $facet_term );
				if ( $facet_display === '' ) {
					continue;
				}
			}

			$url = add_query_arg( array( 'st-facet-field' => $facet_field, 'st-facet-term' => $facet_term ), get_search_link() );
			$html .= "<li><a href=\"" . esc_attr( $url ) . "\">" . esc_html( $facet_display ) . "</a> (" . esc_html( $facet_count ) . ")</li>";
		}

		$html .= '</ul>';

	}

	$html .= '</div>';

	echo $html;
}
