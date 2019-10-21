<?php
/**
 * This file is part of the Elastic Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\SiteSearch\Client\Tests\Integration;

/**
 * Integrations test for the Bulk Document API.
 *
 * @package Elastic\SiteSearch\Client\Test\Integration
 */
class BulkDocumentApiTest extends AbstractEngineTestCase
{
    /**
     * Test the document type API.
     */
    public function testBulkOperations()
    {
        $client = $this->getDefaultClient();
        $engine = $this->getDefaultEngineName();
        $typeId = self::getDefaultDocumentType();

        $documents = [
            ['external_id' => 'doc1', 'fields' => [['name' => 'title', 'value' => 'Doc 1', 'type' => 'string']]],
            ['external_id' => 'doc2', 'fields' => [['name' => 'title', 'value' => 'Doc 2', 'type' => 'string']]],
        ];
        $bulkResponse = $client->createDocuments($engine, $typeId, $documents);
        $this->assertCount(count($documents), $bulkResponse);
        $this->assertNotContains(false, $bulkResponse);
        $this->assertCount(count($documents), $client->listDocuments($engine, $typeId));

        $documents[] = [
            'external_id' => 'doc3',
            'fields' => [['name' => 'title', 'value' => 'Doc 3', 'type' => 'string']],
        ];
        $bulkResponse = $client->createOrUpdateDocuments($engine, $typeId, $documents);
        $this->assertCount(count($documents), $bulkResponse);
        $this->assertNotContains(false, $bulkResponse);
        $this->assertCount(count($documents), $client->listDocuments($engine, $typeId));

        $documentUpdates = [
            ['external_id' => 'doc1', 'fields' => ['title' => 'Doc 1 updated']],
            ['external_id' => 'doc2', 'fields' => ['title' => 'Doc 2 updated']],
        ];
        $bulkResponse = $client->updateDocuments($engine, $typeId, $documentUpdates);
        $this->assertCount(count($documentUpdates), $bulkResponse);
        $this->assertNotContains(false, $bulkResponse);

        $deletedDocIds = array_column($documents, 'external_id');
        $bulkResponse = $client->deleteDocuments($engine, $typeId, $deletedDocIds);
        $this->assertCount(count($deletedDocIds), $bulkResponse);
        $this->assertNotContains(false, $bulkResponse);
        $this->assertEmpty($client->listDocuments($engine, $typeId));
    }

    /**
     * Test asynchronous bulk indexing and receipt check.
     */
    public function testAsyncBulkOperations()
    {
        $client = $this->getDefaultClient();
        $engine = $this->getDefaultEngineName();
        $typeId = self::getDefaultDocumentType();

        $documents = [
            ['external_id' => 'doc1', 'fields' => [['name' => 'title', 'value' => 'Doc 1', 'type' => 'string']]],
            ['external_id' => 'doc2', 'fields' => [['name' => 'title', 'value' => 'Doc 2', 'type' => 'string']]],
        ];

        $bulkResponse = $client->asyncCreateOrUpdateDocuments($engine, $typeId, $documents);
        $this->assertCount(count($documents), $bulkResponse);
        $receiptIds = array_column($bulkResponse['document_receipts'], 'id');

        while (!empty($receiptIds)) {
            $receiptCheckResponse = $client->getDocumentReceipts($receiptIds);
            $this->assertNotEmpty($receiptCheckResponse);
            $receiptIds = [];
            foreach ($receiptCheckResponse as $receipt) {
                if ('pending' != $receipt['status']) {
                    $receiptIds = $receipt['id'];
                }
            }
            usleep(1000);
        }
    }
}
