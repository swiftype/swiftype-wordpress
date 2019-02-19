<?php

namespace Swiftype\SiteSearch\Wordpress\Search;

use Swiftype\SiteSearch\Wordpress\Config\Config;

class Theme
{
    private $searchResult;

    private $config;

    public function __construct()
    {
        $this->config = new Config();
        \add_action('swiftype_search_result', [$this, 'setSearchResult']);
    }

    public function setSearchResult($searchResult)
    {
        $this->searchResult = $searchResult;
    }

    public function getSearchResult()
    {
        return $this->getSearchResult();
    }

    public function getTotalResultCount()
    {
        $resultInfo = $this->getResultInfo();
        return $resultInfo['total_result_count'];
    }

    public function getFacets()
    {
        $resultInfo = $this->getResultInfo();
        return $resultInfo['facets'];
    }

    private function getResultInfo()
    {
        return $this->searchResult['info'][$this->config->getDocumentType()];
    }
}