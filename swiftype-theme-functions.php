<?php
global $swiftypeTheme;
$swiftypeTheme = new \Swiftype\SiteSearch\Wordpress\Search\Theme();

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
function swiftype_search_results()
{
    global $swiftypeTheme;
    return $swiftypeTheme->getSearchResult();
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
function swiftype_total_result_count()
{
    global $swiftypeTheme;
    return $swiftypeTheme->getTotalResultCount();
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
    global $swiftypeTheme;
    $facets = $swiftypeTheme->getFacets();
    $appliedFilters = $swiftypeTheme->getAppliedFilters();
    $html = '';

    if (!empty($appliedFilters)) {
        $html .= '<div class="st-current-filters">';
        $html .= '<h4>' . __('Applied filters:') . '</h4>';
        $html .= '<ul>';

        foreach ($appliedFilters as $filter) {
            $html .= "<li>";
            $html .= "<strong>" . esc_html($filter['title']). ": </strong>";
            $html .= '<span>'. esc_html($filter['value']) .'</span>';
            $html .= '<a href="' . esc_attr($filter['remove_url']) . '">' . __('Remove filter') . '</a>';
            $html .= "</li>";
        }

        $html .= '</ul>';
        $html .= '</div>';
    }

    if (!empty($facets)) {
        $html .= '<div class="st-facets">';
        $html .= '<h4>' . __('Filter by:') . '</h4>';
        foreach ($facets as $facet) {
            $facetTitle = $facet['title'];
            $html .= '<h5 class="st-facet-field st-facet-field-' . sanitize_title_with_dashes($facetTitle) . '">' . esc_html($facetTitle) . '</h5>';
            $html .= '<ul>';
            foreach ($facet['values'] as $currentValue) {
                $escapedValue = str_replace('&', '%26', $currentValue['value']);
                $facetDisplay = $escapedValue;

                $html .= "<li><a href=\"" . esc_attr($currentValue['url']) . "\">" . esc_html($facetDisplay) . "</a> (" . esc_html($currentValue['count']) . ")</li>";
            }
            $html .= '</ul>';
        }

        $html .= '</div>';
    }

    echo $html;
}
