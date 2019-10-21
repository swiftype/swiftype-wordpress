<?php
/**
 * This file is part of the Elastic Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\SiteSearch\Client;

/**
 * Client implementation.
 *
 * @package Elastic\SiteSearch\Client
 */
class Client extends \Elastic\OpenApi\Codegen\AbstractClient
{
    // phpcs:disable

    /**
     * Async bulk creation or update of documents in an engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/indexing#bulk_indexing
     *
     * @param string $engineName     Name of the engine.
     * @param string $documentTypeId Document type id.
     * @param array  $documents      List of documents to index.
     *
     * @return array
     */
    public function asyncCreateOrUpdateDocuments($engineName, $documentTypeId, $documents)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
            'documents' => $documents,
        ];

        $endpoint = $this->getEndpoint('AsyncCreateOrUpdateDocuments');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Create a new document in an engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/indexing#add-document
     *
     * @param string $engineName         Name of the engine.
     * @param string $documentTypeId     Document type id.
     * @param string $documentExternalId Document external id.
     * @param array  $documentFields     Document fields.
     *
     * @return array
     */
    public function createDocument($engineName, $documentTypeId, $documentExternalId, $documentFields)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
            'document.external_id' => $documentExternalId,
            'document.fields' => $documentFields,
        ];

        $endpoint = $this->getEndpoint('CreateDocument');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Create a new document type in an engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/indexing#add-documenttype
     *
     * @param string $engineName       Name of the engine.
     * @param string $documentTypeName Document type name.
     *
     * @return array
     */
    public function createDocumentType($engineName, $documentTypeName)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type.name' => $documentTypeName,
        ];

        $endpoint = $this->getEndpoint('CreateDocumentType');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Bulk creation of documents in an engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/indexing#bulk_create
     *
     * @param string $engineName     Name of the engine.
     * @param string $documentTypeId Document type id.
     * @param array  $documents      List of documents to create.
     *
     * @return array
     */
    public function createDocuments($engineName, $documentTypeId, $documents)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
            'documents' => $documents,
        ];

        $endpoint = $this->getEndpoint('CreateDocuments');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Create a new API based engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/engines#create
     *
     * @param string $engineName     Engine name.
     * @param string $engineLanguage Engine language (null for universal).
     *
     * @return array
     */
    public function createEngine($engineName, $engineLanguage = null)
    {
        $params = [
            'engine.name' => $engineName,
            'engine.language' => $engineLanguage,
        ];

        $endpoint = $this->getEndpoint('CreateEngine');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Create or update a document in an engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/indexing#add-document
     *
     * @param string $engineName         Name of the engine.
     * @param string $documentTypeId     Document type id.
     * @param string $documentExternalId Document external id.
     * @param array  $documentFields     Document fields.
     *
     * @return array
     */
    public function createOrUpdateDocument($engineName, $documentTypeId, $documentExternalId, $documentFields)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
            'document.external_id' => $documentExternalId,
            'document.fields' => $documentFields,
        ];

        $endpoint = $this->getEndpoint('CreateOrUpdateDocument');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Bulk creation or update of documents in an engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/indexing#bulk_create_or_update_verbose
     *
     * @param string $engineName     Name of the engine.
     * @param string $documentTypeId Document type id.
     * @param array  $documents      List of documents to index.
     *
     * @return array
     */
    public function createOrUpdateDocuments($engineName, $documentTypeId, $documents)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
            'documents' => $documents,
        ];

        $endpoint = $this->getEndpoint('CreateOrUpdateDocuments');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Delete a document from the engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/indexing#delete-external-id
     *
     * @param string $engineName     Name of the engine.
     * @param string $documentTypeId Document type id.
     * @param string $externalId     Document external id.
     *
     * @return array
     */
    public function deleteDocument($engineName, $documentTypeId, $externalId)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
            'external_id' => $externalId,
        ];

        $endpoint = $this->getEndpoint('DeleteDocument');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Delete a document type by id.
     *
     * Documentation: https://swiftype.com/documentation/site-search/indexing#documenttypes-delete
     *
     * @param string $engineName     Name of the engine.
     * @param string $documentTypeId Document type id.
     *
     * @return array
     */
    public function deleteDocumentType($engineName, $documentTypeId)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
        ];

        $endpoint = $this->getEndpoint('DeleteDocumentType');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Bulk delete of documents in an engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/indexing#bulk_destroy
     *
     * @param string $engineName     Name of the engine.
     * @param string $documentTypeId Document type id.
     * @param array  $documents      List of deleted documents external ids.
     *
     * @return array
     */
    public function deleteDocuments($engineName, $documentTypeId, $documents)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
            'documents' => $documents,
        ];

        $endpoint = $this->getEndpoint('DeleteDocuments');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Delete an engine by name.
     *
     * Documentation: https://swiftype.com/documentation/site-search/engines#destroy
     *
     * @param string $engineName Name of the engine.
     *
     * @return array
     */
    public function deleteEngine($engineName)
    {
        $params = [
            'engine_name' => $engineName,
        ];

        $endpoint = $this->getEndpoint('DeleteEngine');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Retrieve number of autoselects (number of clicked results in the autocomplete) per day over a period for a document type.
     *
     * Documentation: https://swiftype.com/documentation/site-search/analytics#autoselects
     *
     * @param string $engineName     Name of the engine.
     * @param string $documentTypeId Document type id.
     * @param string $startDate      The first day from which to capture searches. Defaults to 2 weeks.
     * @param string $endDate        The last date from which to capture searches. Defaults to current date.
     *
     * @return array
     */
    public function getAutoselectsCountAnalyticsDocumentType($engineName, $documentTypeId, $startDate = null, $endDate = null)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $endpoint = $this->getEndpoint('GetAutoselectsCountAnalyticsDocumentType');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Retrieve number of autoselects (number of clicked results in the autocomplete) per day over a period for an engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/analytics#autoselects
     *
     * @param string $engineName Name of the engine.
     * @param string $startDate  The first day from which to capture searches. Defaults to 2 weeks.
     * @param string $endDate    The last date from which to capture searches. Defaults to current date.
     *
     * @return array
     */
    public function getAutoselectsCountAnalyticsEngine($engineName, $startDate = null, $endDate = null)
    {
        $params = [
            'engine_name' => $engineName,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $endpoint = $this->getEndpoint('GetAutoselectsCountAnalyticsEngine');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Retrieve number of clicks per day over a period for a document type.
     *
     * Documentation: https://swiftype.com/documentation/site-search/analytics#clicks
     *
     * @param string $engineName     Name of the engine.
     * @param string $documentTypeId Document type id.
     * @param string $startDate      The first day from which to capture searches. Defaults to 2 weeks.
     * @param string $endDate        The last date from which to capture searches. Defaults to current date.
     *
     * @return array
     */
    public function getClicksCountAnalyticsDocumentType($engineName, $documentTypeId, $startDate = null, $endDate = null)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $endpoint = $this->getEndpoint('GetClicksCountAnalyticsDocumentType');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Retrieve number of clicks per day over a period for an engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/analytics#clicks
     *
     * @param string $engineName Name of the engine.
     * @param string $startDate  The first day from which to capture searches. Defaults to 2 weeks.
     * @param string $endDate    The last date from which to capture searches. Defaults to current date.
     *
     * @return array
     */
    public function getClicksCountAnalyticsEngine($engineName, $startDate = null, $endDate = null)
    {
        $params = [
            'engine_name' => $engineName,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $endpoint = $this->getEndpoint('GetClicksCountAnalyticsEngine');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Retrieve a document from the engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/indexing#document-single
     *
     * @param string $engineName     Name of the engine.
     * @param string $documentTypeId Document type id.
     * @param string $externalId     Document external id.
     *
     * @return array
     */
    public function getDocument($engineName, $documentTypeId, $externalId)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
            'external_id' => $externalId,
        ];

        $endpoint = $this->getEndpoint('GetDocument');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Check the status of document receipts issued by aync bulk indexing.
     *
     * Documentation: https://swiftype.com/documentation/site-search/indexing#bulk_create_or_update_verbose
     *
     * @param array $receiptIds List of ids of documents receipts to check.
     *
     * @return array
     */
    public function getDocumentReceipts($receiptIds)
    {
        $params = [
            'ids' => $receiptIds,
        ];

        $endpoint = $this->getEndpoint('GetDocumentReceipts');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Get a document type by id.
     *
     * Documentation: https://swiftype.com/documentation/site-search/indexing#documenttypes-single
     *
     * @param string $engineName     Name of the engine.
     * @param string $documentTypeId Document type id.
     *
     * @return array
     */
    public function getDocumentType($engineName, $documentTypeId)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
        ];

        $endpoint = $this->getEndpoint('GetDocumentType');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Retrieves an engine by name.
     *
     * Documentation: https://swiftype.com/documentation/site-search/engines#one-engine
     *
     * @param string $engineName Name of the engine.
     *
     * @return array
     */
    public function getEngine($engineName)
    {
        $params = [
            'engine_name' => $engineName,
        ];

        $endpoint = $this->getEndpoint('GetEngine');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Get the number of searches per day for an document type.
     *
     * Documentation: https://swiftype.com/documentation/site-search/analytics#searches
     *
     * @param string $engineName     Name of the engine.
     * @param string $documentTypeId Document type id.
     * @param string $startDate      The first day from which to capture searches. Defaults to 2 weeks.
     * @param string $endDate        The last date from which to capture searches. Defaults to current date.
     *
     * @return array
     */
    public function getSearchCountAnalyticsDocumentType($engineName, $documentTypeId, $startDate = null, $endDate = null)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $endpoint = $this->getEndpoint('GetSearchCountAnalyticsDocumentType');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Get the number of searches per day for an engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/analytics#searches
     *
     * @param string $engineName Name of the engine.
     * @param string $startDate  The first day from which to capture searches. Defaults to 2 weeks.
     * @param string $endDate    The last date from which to capture searches. Defaults to current date.
     *
     * @return array
     */
    public function getSearchCountAnalyticsEngine($engineName, $startDate = null, $endDate = null)
    {
        $params = [
            'engine_name' => $engineName,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        $endpoint = $this->getEndpoint('GetSearchCountAnalyticsEngine');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Retrieve top queries with no result and usage count over a period for a document type.
     *
     * Documentation: https://swiftype.com/documentation/site-search/analytics#top_no_result_queries
     *
     * @param string $engineName     Name of the engine.
     * @param string $documentTypeId Document type id.
     * @param string $startDate      The first day from which to capture searches. Defaults to 2 weeks.
     * @param string $endDate        The last date from which to capture searches. Defaults to current date.
     * @param string $currentPage    The page to fetch. Defaults to 1.
     * @param string $pageSize       The number of results per page.
     *
     * @return array
     */
    public function getTopNoResultQueriesAnalyticsDocumentType($engineName, $documentTypeId, $startDate = null, $endDate = null, $currentPage = null, $pageSize = null)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'page' => $currentPage,
            'per_page' => $pageSize,
        ];

        $endpoint = $this->getEndpoint('GetTopNoResultQueriesAnalyticsDocumentType');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Retrieve top queries with no result and usage count over a period for an engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/analytics#top_no_result_queries
     *
     * @param string $engineName  Name of the engine.
     * @param string $startDate   The first day from which to capture searches. Defaults to 2 weeks.
     * @param string $endDate     The last date from which to capture searches. Defaults to current date.
     * @param string $currentPage The page to fetch. Defaults to 1.
     * @param string $pageSize    The number of results per page.
     *
     * @return array
     */
    public function getTopNoResultQueriesAnalyticsEngine($engineName, $startDate = null, $endDate = null, $currentPage = null, $pageSize = null)
    {
        $params = [
            'engine_name' => $engineName,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'page' => $currentPage,
            'per_page' => $pageSize,
        ];

        $endpoint = $this->getEndpoint('GetTopNoResultQueriesAnalyticsEngine');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Retrieve top queries and usage count over a period for a document type.
     *
     * Documentation: https://swiftype.com/documentation/site-search/analytics#top_queries
     *
     * @param string $engineName     Name of the engine.
     * @param string $documentTypeId Document type id.
     * @param string $startDate      The first day from which to capture searches. Defaults to 2 weeks.
     * @param string $endDate        The last date from which to capture searches. Defaults to current date.
     * @param string $currentPage    The page to fetch. Defaults to 1.
     * @param string $pageSize       The number of results per page.
     *
     * @return array
     */
    public function getTopQueriesAnalyticsDocumentType($engineName, $documentTypeId, $startDate = null, $endDate = null, $currentPage = null, $pageSize = null)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'page' => $currentPage,
            'per_page' => $pageSize,
        ];

        $endpoint = $this->getEndpoint('GetTopQueriesAnalyticsDocumentType');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Retrieve top queries and usage count over a period for an engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/analytics#top_queries
     *
     * @param string $engineName  Name of the engine.
     * @param string $startDate   The first day from which to capture searches. Defaults to 2 weeks.
     * @param string $endDate     The last date from which to capture searches. Defaults to current date.
     * @param string $currentPage The page to fetch. Defaults to 1.
     * @param string $pageSize    The number of results per page.
     *
     * @return array
     */
    public function getTopQueriesAnalyticsEngine($engineName, $startDate = null, $endDate = null, $currentPage = null, $pageSize = null)
    {
        $params = [
            'engine_name' => $engineName,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'page' => $currentPage,
            'per_page' => $pageSize,
        ];

        $endpoint = $this->getEndpoint('GetTopQueriesAnalyticsEngine');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * List all document types for an engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/indexing#documenttypes-all
     *
     * @param string $engineName Name of the engine.
     *
     * @return array
     */
    public function listDocumentTypes($engineName)
    {
        $params = [
            'engine_name' => $engineName,
        ];

        $endpoint = $this->getEndpoint('ListDocumentTypes');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * List all documents in an engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/indexing#document-all
     *
     * @param string $engineName     Name of the engine.
     * @param string $documentTypeId Document type id.
     *
     * @return array
     */
    public function listDocuments($engineName, $documentTypeId)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
        ];

        $endpoint = $this->getEndpoint('ListDocuments');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Retrieves all engines with optional pagination support.
     *
     * Documentation: https://swiftype.com/documentation/site-search/engines#list
     *
     * @param string $currentPage The page to fetch. Defaults to 1.
     * @param string $pageSize    The number of results per page.
     *
     * @return array
     */
    public function listEngines($currentPage = null, $pageSize = null)
    {
        $params = [
            'page' => $currentPage,
            'per_page' => $pageSize,
        ];

        $endpoint = $this->getEndpoint('ListEngines');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Record a clickthrough for a particular result.
     *
     * Documentation: https://swiftype.com/documentation/site-search/analytics#recording_clickthroughs
     *
     * @param string $engineName     Name of the engine.
     * @param string $documentTypeId Document type id.
     * @param string $documentId     The external_id or id of the document clicked by the user.
     * @param string $queryText      Search query text.
     *
     * @return array
     */
    public function logClickthrough($engineName, $documentTypeId, $documentId, $queryText)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
            'id' => $documentId,
            'q' => $queryText,
        ];

        $endpoint = $this->getEndpoint('LogClickthrough');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Run a search request accross an engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/searching
     *
     * @param string $engineName          Name of the engine.
     * @param string $queryText           Search query text.
     * @param array  $searchRequestParams Search request parameters.
     *
     * @return array
     */
    public function search($engineName, $queryText, $searchRequestParams = null)
    {
        $params = [
            'engine_name' => $engineName,
            'q' => $queryText,
        ];

        $endpoint = $this->getEndpoint('Search');
        $endpoint->setParams($params);
        $endpoint->setBody($searchRequestParams);

        return $this->performRequest($endpoint);
    }

    /**
     * Run an autocomplete search request accross an engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/autocomplete
     *
     * @param string $engineName          Name of the engine.
     * @param string $queryText           Search query text.
     * @param array  $searchRequestParams Search request parameters.
     *
     * @return array
     */
    public function suggest($engineName, $queryText, $searchRequestParams = null)
    {
        $params = [
            'engine_name' => $engineName,
            'q' => $queryText,
        ];

        $endpoint = $this->getEndpoint('Suggest');
        $endpoint->setParams($params);
        $endpoint->setBody($searchRequestParams);

        return $this->performRequest($endpoint);
    }

    /**
     * Update fields of a document.
     *
     * Documentation: https://swiftype.com/documentation/site-search/indexing#updating_fields
     *
     * @param string $engineName     Name of the engine.
     * @param string $documentTypeId Document type id.
     * @param string $externalId     Document external id.
     * @param array  $fields         Updated fields.
     *
     * @return array
     */
    public function updateDocumentFields($engineName, $documentTypeId, $externalId, $fields)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
            'external_id' => $externalId,
            'fields' => $fields,
        ];

        $endpoint = $this->getEndpoint('UpdateDocumentFields');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    /**
     * Bulk update of documents in an engine.
     *
     * Documentation: https://swiftype.com/documentation/site-search/indexing#bulk_update
     *
     * @param string $engineName     Name of the engine.
     * @param string $documentTypeId Document type id.
     * @param array  $documents      List of documents to update.
     *
     * @return array
     */
    public function updateDocuments($engineName, $documentTypeId, $documents)
    {
        $params = [
            'engine_name' => $engineName,
            'document_type_id' => $documentTypeId,
            'documents' => $documents,
        ];

        $endpoint = $this->getEndpoint('UpdateDocuments');
        $endpoint->setParams($params);

        return $this->performRequest($endpoint);
    }

    // phpcs:enable
}
