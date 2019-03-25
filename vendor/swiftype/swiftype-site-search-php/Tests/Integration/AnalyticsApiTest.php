<?php
/**
 * This file is part of the Swiftype Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swiftype\SiteSearch\Tests\Integration;

/**
 * Integration tests for the Search API.
 *
 * @package Swiftype\SiteSearch\Test\Integration
 *
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 */
class AnalyticsApiTest extends AbstractClientTestCase
{
    /**
     * Test count method for Engine Analytics API.
     *
     * @param string $method
     *
     * @testWith ["getSearchCountAnalyticsEngine"]
     *           ["getClicksCountAnalyticsEngine"]
     *           ["getAutoselectsCountAnalyticsEngine"]
     */
    public function testEngineAnalyticsCountMethod($method)
    {
        $counts = self::getDefaultClient()->$method(self::getDefaultEngineName());

        foreach ($counts as list($date, $count)) {
            $this->assertNotFalse(\DateTime::createFromFormat('Y-m-d', $date));
            $this->assertInternalType('int', $count);
        }
    }

    /**
     * Test count method for Document Types Analytics API.
     *
     * @param string $method
     *
     * @testWith ["getSearchCountAnalyticsDocumentType"]
     *           ["getClicksCountAnalyticsDocumentType"]
     *           ["getAutoselectsCountAnalyticsDocumentType"]
     */
    public function testDocumentTypeAnalyticsCountMethod($method)
    {
        $counts = self::getDefaultClient()->$method(self::getDefaultEngineName(), 'page');

        foreach ($counts as list($date, $count)) {
            $this->assertNotFalse(\DateTime::createFromFormat('Y-m-d', $date));
            $this->assertInternalType('int', $count);
        }
    }

    /**
     * Test calling the log clickthrough API.
     */
    public function testLogClickthrough()
    {
        $client = self::getDefaultClient();
        $engine = self::getDefaultEngineName();

        $searchResponse = $client->search($engine, 'search engine');
        $clickRecord = current($searchResponse['records']['page']);

        $this->assertEmpty($client->logClickthrough($engine, 'page', $clickRecord['external_id'], 'search engine'));
    }

    /**
     * @return string
     */
    protected static function getDefaultEngineName()
    {
        return 'publisher-demo';
    }
}
