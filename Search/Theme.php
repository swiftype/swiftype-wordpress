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
        \add_action('swiftype_search_result', [$this, 'setSearchResult']);
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

    /**
     * Return current search results facets.
     *
     * @return int
     */
    public function getFacets()
    {
        $resultInfo = $this->getResultInfo();
        return $resultInfo['facets'];
    }

    /**
     * @return array
     */
    private function getResultInfo()
    {
        return $this->searchResult['info'][$this->config->getDocumentType()];
    }
}
