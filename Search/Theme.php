<?php

namespace Swiftype\SiteSearch\Wordpress\Search;

use Swiftype\SiteSearch\Wordpress\Config\Config;

/**
 * Provides a proxy to search result for themes functions.
 *
 * @author Matt Riley <mriley@swiftype.com>, Quin Hoxie <qhoxie@swiftype.com>, Aurelien Foucret <aurelien.foucret@elastic.co>
 */
class Theme
{
    /**
     * @var array
     */
    private $searchResult;

    /**
     * @var Config
     */
    private $config;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->config = new Config();

        if (!\is_admin()) {
            \add_action('swiftype_search_result', [$this, 'setSearchResult']);
            \add_action('wp_enqueue_scripts', [$this, 'enqueueSwiftypeAssets']);
        }
    }

    /**
     * Add Site Search assets.
     *
     * @param array $engine
     */
    public function enqueueSwiftypeAssets($engine)
    {
        $rootDir = __DIR__ . '/../swiftype.php';
        \wp_enqueue_style('swiftype-facets', \plugins_url('assets/facets.css', $rootDir));
    }

    /**
     * Update search results.
     *
     * @param array $searchResult
     */
    public function setSearchResult($searchResult)
    {
        $this->searchResult = $searchResult;
    }

    /**
     * Return current search result as a raw array.
     *
     * @return array
     */
    public function getSearchResult()
    {
        return $this->searchResult;
    }

    /**
     * Return current search results count.
     *
     * @return int
     */
    public function getTotalResultCount()
    {
        $resultInfo = $this->getResultInfo();
        return $resultInfo['total_result_count'];
    }

    public function getAppliedFilters()
    {
        $filters = [];

        foreach ($this->config->getFacetConfig() as $currentFacet) {
            $filterField = $currentFacet['field'];
            if (!empty($_GET['st-filter-' . $filterField])) {
                $filterValue = $_GET['st-filter-' . $filterField];
                $removeParams = array_filter(array_merge($_GET, ["st-filter-$filterField" => '']));
                $removeUrl = \add_query_arg($removeParams, get_search_link());
                $filters[] = [
                    'title'       => $currentFacet['title'],
                    'remove_url' => $removeUrl,
                    'value'      => $filterField == 'category' ? \get_cat_name($filterValue) : trim($filterValue),
                ];
            }
        }

        return $filters;
    }

    /**
     * Return current search results facets.
     *
     * @return int
     */
    public function getFacets()
    {
        $facets = [];
        $resultInfo = $this->getResultInfo();

        foreach ($this->config->getFacetConfig() as $currentFacet) {
            $facetField = $currentFacet['field'];
            $currentFacet['values'] = [];

            if (!empty($resultInfo['facets'][$facetField]) && empty($_GET['st-filter-' . $facetField])) {

                $rawValues = $resultInfo['facets'][$facetField];

                if ($currentFacet['sortOrder'] == "text") {
                    ksort($rawValues, SORT_FLAG_CASE | SORT_NATURAL);
                }

                $rawValues = array_slice($rawValues, 0, $currentFacet['size']);

                foreach ($rawValues as $value => $count) {

                    $facetValue = [
                        'value'    => $facetField == 'category' ? \get_cat_name($value) : trim($value),
                        'rawValue' => $value,
                        'count'    => $count,
                        'url'      => $this->getFilterUrl($facetField, $value),
                    ];

                    if (!empty($facetValue['value'])) {
                        $currentFacet['values'][] = $facetValue;
                    }
                }
            }

            if (!empty($currentFacet['values'])) {
                $facets[] = $currentFacet;
            }
        }

        return $facets;
    }

    private function getFilterUrl($field, $value)
    {
        return \add_query_arg(array_merge($_GET, ["st-filter-$field" => $value]), \get_search_link());
    }


    /**
     * @return array
     */
    private function getResultInfo()
    {
        return $this->searchResult['info'][$this->config->getDocumentType()];
    }
}
