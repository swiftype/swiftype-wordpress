<?php
/**
 * This file is part of the Elastic Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\SiteSearch\Client\Tests\Integration;

use Elastic\OpenApi\Codegen\Exception\NotFoundException;

/**
 * Integrations test for the Document API.
 *
 * @package Elastic\SiteSearch\Client\Test\Integration
 */
class DocumentApiTest extends AbstractEngineTestCase
{
    /**
     * Test the document type API.
     */
    public function testDocumentTypeApi()
    {
        $client = $this->getDefaultClient();
        $engine = $this->getDefaultEngineName();

        $this->assertArrayHasKey('id', $client->createDocumentType($engine, 'document-type'));

        $documentType = $client->getDocumentType($engine, 'document-type');
        $this->assertArrayHasKey('id', $documentType);
        $this->assertEquals('document-type', $documentType['name']);

        $documentTypes = $client->listDocumentTypes($engine);
        $this->assertCount(2, $documentTypes);

        $client->deleteDocumentType($engine, 'document-type');
    }

    /**
     * Test document creation.
     *
     * @param string $typeId
     * @param string $docId
     * @param string $fields
     *
     * @testWith ["doc1", [{"name": "title", "value": "Test doc", "type": "string"}]]
     *           ["doc2", [{"name": "title", "value": ["Test doc", "Multiple"], "type": "string"}]]
     */
    public function testCreateDocument($docId, $fields)
    {
        $client = $this->getDefaultClient();
        $engine = $this->getDefaultEngineName();
        $doc = $client->createDocument($engine, self::getDefaultDocumentType(), $docId, $fields);
        $this->assertArrayHasKey('id', $doc);
        $this->assertArrayHasKey('external_id', $doc);
        $this->assertEquals($docId, $doc['external_id']);

        $documentList = $client->listDocuments($engine, self::getDefaultDocumentType());
        $this->assertNotEmpty($documentList);
        $this->assertContains($docId, array_column($documentList, 'external_id'));
    }

    /**
     * Test document creation and updating.
     *
     * @param string $typeId
     * @param string $docId
     * @param string $fields
     *
     * @testWith ["doc1", [{"name": "title", "value": "Test doc", "type": "string"}]]
     *           ["doc1", [{"name": "title", "value": "Test updated doc", "type": "string"}]]
     */
    public function testCreateOrUpdateDocument($docId, $fields)
    {
        $client = $this->getDefaultClient();
        $engine = $this->getDefaultEngineName();
        $doc = $client->createOrUpdateDocument($engine, self::getDefaultDocumentType(), $docId, $fields);
        $this->assertArrayHasKey('id', $doc);
        $this->assertArrayHasKey('external_id', $doc);
        $this->assertEquals($docId, $doc['external_id']);

        $this->assertEquals($doc, $client->getDocument($engine, self::getDefaultDocumentType(), $docId));
    }

    /**
     * Test deleting a document from the engine.
     */
    public function testDeleteDocument()
    {
        $client = $this->getDefaultClient();
        $engine = $this->getDefaultEngineName();

        $docFields = [['name' => 'title', 'value' => 'Title', 'type' => 'string']];
        $docId = 'deletedDoc';

        $client->createDocument($engine, self::getDefaultDocumentType(), $docId, $docFields);
        $this->assertNotEmpty($client->getDocument($engine, self::getDefaultDocumentType(), $docId));

        $client->deleteDocument($engine, self::getDefaultDocumentType(), $docId);

        $this->expectException(NotFoundException::class);
        $client->getDocument($engine, self::getDefaultDocumentType(), $docId);
    }

    /**
     * Test document partial update.
     */
    public function testPartialDocumentUpdate()
    {
        $client = $this->getDefaultClient();
        $engine = $this->getDefaultEngineName();

        $docFields = [['name' => 'title', 'value' => 'Title', 'type' => 'string']];
        $docId = 'updateDioc';

        $client->createDocument($engine, self::getDefaultDocumentType(), $docId, $docFields);
        $client->updateDocumentFields($engine, self::getDefaultDocumentType(), $docId, ['title' => 'Updated title']);

        $updatedDoc = $client->getDocument($engine, self::getDefaultDocumentType(), $docId);
        $this->assertEquals('Updated title', $updatedDoc['title']);
    }
}
