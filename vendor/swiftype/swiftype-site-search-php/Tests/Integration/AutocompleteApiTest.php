<?php
/**
 * This file is part of the Swiftype Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swiftype\SiteSearch\Tests\Integration;

/**
 * Integration tests for the Autocomplete API.
 *
 * @package Swiftype\SiteSearch\Test\Integration
 *
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 */
class AutocompleteApiTest extends AbstractClientTestCase
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
        $searchResponseInfo = $searchResponse['info'][$docType];

        $this->assertArrayHasKey($docType, $searchResponse['info']);
        $this->assertEquals($queryText, $searchResponseInfo['query']);

        if ($pageSize) {
            $this->assertEquals($pageSize, $searchResponseInfo['per_page']);
        }

        if ($currentPage) {
            $this->assertEquals($currentPage, $searchResponseInfo['current_page']);
        }
        $expectedRecords = min($searchResponseInfo['per_page'], $searchResponseInfo['total_result_count']);
        $this->assertEquals($expectedRecords, $searchResponse['record_count']);
        $this->assertArrayHasKey('records', $searchResponse);
        $this->assertArrayHasKey($docType, $searchResponse['records']);
        $this->assertCount($searchResponse['record_count'], $searchResponse['records'][$docType]);
    }

    /**
     * Run the autocomplete query.
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

        return $client->suggest($engine, $queryText, $searchParams);
    }

    /**
     * @return string
     */
    protected static function getDefaultEngineName()
    {
        return 'kb-demo';
    }
}
