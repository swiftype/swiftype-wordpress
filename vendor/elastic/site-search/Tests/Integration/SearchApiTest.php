<?php
/**
 * This file is part of the Elastic Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\SiteSearch\Client\Tests\Integration;

/**
 * Integration tests for the Search API.
 *
 * @package Elastic\SiteSearch\Client\Test\Integration
 */
class SearchApiTest extends AbstractClientTestCase
{
    /**
     * Test the search API with simple searches and pagination.
     *
     * @param string $docType
     * @param string $queryText
     * @param int    $currentPage
     * @param int    $pageSize
     *
     * @testWith ["page", ""]
     *           ["page", "search engine"]
     *           ["page", "search engine", 1, 3]
     *           ["page", "noresultsearch", 1, 3]
     */
    public function testSimpleSearch($docType, $queryText, $currentPage = null, $pageSize = null)
    {
        $searchParams = ['per_page' => $pageSize, 'page' => $currentPage, 'document_types' => [$docType]];

        $searchResponse = $this->search($queryText, $searchParams);

        $this->assertEmpty($searchResponse['errors']);
        $this->assertArrayHasKey('info', $searchResponse);
        $this->assertArrayHasKey($docType, $searchResponse['info']);
        $searchResponseInfo = $searchResponse['info'][$docType];

        $this->assertEquals($queryText, $searchResponseInfo['query']);

        if ($pageSize) {
            $this->assertEquals($pageSize, $searchResponseInfo['per_page']);
        }

        if ($currentPage) {
            $this->assertEquals($currentPage, $searchResponseInfo['current_page']);
        }

        $expectedRecords = min($searchResponseInfo['per_page'], $searchResponseInfo['total_result_count']);
        $this->assertArrayHasKey('records', $searchResponse);
        $this->assertArrayHasKey($docType, $searchResponse['records']);
        $this->assertCount($expectedRecords, $searchResponse['records'][$docType]);
    }

    /**
     * Test using a search boost inside the search query.
     */
    public function testBoostedSearch()
    {
        $searchParams = ['functional_boosts' => ['page' => ['votes' => 'logarithmic']]];

        $searchResponse = $this->search('search engine', $searchParams);

        $this->assertEmpty($searchResponse['errors']);
    }

    /**
     * Test using a search sort order inside the search query.
     *
     * @param string $sortField
     * @param string $sortDirection
     *
     * @testWith ["votes", "desc"]
     *           ["title", "desc"]
     */
    public function testSortedSearch($sortField, $sortDirection = 'asc')
    {
        $searchParams = ['sort_field' => ['page' => $sortField], 'sort_direction' => ['page' => $sortDirection]];

        $searchResponse = $this->search('search engine', $searchParams);

        $this->assertEmpty($searchResponse['errors']);
    }

    /**
     * Test using a search sort order inside the search query.
     * Run filtered query to check the doc count is consistent.
     *
     * @param string $facetField
     *
     * @testWith ["type"]
     */
    public function testFacetedSearch($facetField)
    {
        $searchParams = ['facets' => ['page' => [$facetField]]];

        $searchResponse = $this->search('search engine', $searchParams);

        $this->assertEmpty($searchResponse['errors']);
        $this->assertNotEmpty($searchResponse['info']['page']['facets'][$facetField]);

        $filterValues = array_slice($searchResponse['info']['page']['facets'][$facetField], 0, 10);

        foreach ($filterValues as $filterValue => $docCount) {
            $filteredSearchParams = ['filters' => ['page' => [$facetField => $filterValue]]];

            $filteredSearchResponse = $this->search('search engine', $filteredSearchParams);

            $this->assertEmpty($filteredSearchResponse['errors']);
            $this->assertEquals($docCount, $filteredSearchResponse['info']['page']['total_result_count']);
        }
    }

    /**
     * Run the search query.
     *
     * @param string     $queryText
     * @param array|null $searchParams
     *
     * @return array
     */
    protected function search($queryText, $searchParams = null)
    {
        $client = $this->getDefaultClient();
        $engine = $this->getDefaultEngineName();

        return $client->search($engine, $queryText, $searchParams);
    }

    /**
     * @return string
     */
    protected static function getDefaultEngineName()
    {
        return 'kb-demo';
    }
}
