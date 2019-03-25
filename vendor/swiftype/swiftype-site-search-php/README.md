<p align="center"><img src="https://github.com/swiftype/swiftype-site-search-php/blob/master/logo-site-search.png?raw=true" alt="Elastic Site Search Logo"></p>

<p align="center"><a href="https://circleci.com/gh/swiftype/swiftype-site-search-php"><img src="https://circleci.com/gh/swiftype/swiftype-site-search-php.svg?style=svg&circle-token=9a11fb27c1d6961bb8887b684b0c7707b3b4eb6e" alt="CircleCI build"></a></p>

> A first-party PHP client for the [Elastic Site Search API](https://swiftype.com/documentation/site-search/overview).

## Contents

- [Getting started](#getting-started-)
- [Usage](#usage)
- [Development](#development)
- [FAQ](#faq-)
- [Contribute](#contribute-)
- [License](#license-)

***

## Getting started 🐣

Using this client assumes that you have already created a Site Search account on https://swiftype.com/.

You can install the client in your project by using composer:

```bash
composer require swiftype/swiftype-site-search-php
```

## Usage

### Configuring the client

#### Basic client instantiation

To instantiate a new client you can use `\Swiftype\SiteSearch\ClientBuilder`:

```php
  $apiKey        = 'XXXXXXXXXXXX';
  $clientBuilder = \Swiftype\SiteSearch\ClientBuilder::create($apiKey);

  $client = $clientBuilder->build();
```

**Notes:**

- The resulting client will be of type `\Swiftype\SiteSearch\Client`

- You can find the API endpoint and your API key URL in your Site Search account: https://app.swiftype.com/settings/account.

- The Site Search PHP client does not support authentication through Engine Key as described in the [documentation](https://swiftype.com/documentation/site-search/overview#authentication).

### Basic usage

#### Retrieve or create an engine

Most methods of the API require that you have access to an Engine.

To check if an Engine exists and retrieve its configuration, you can use the `Client::getEngine` method :

```php
  $engine = $client->getEngine('my-engine');
```

If the Engine does not exists yet, you can create it by using the `Client::createEngine` method :

```php
  $engine = $client->createEngine('my-engine', 'en');
```

The second parameter (`$language`) is optional or can be set to null. Then the Engine will be created using the `universal` language.
The list of supported language is available here : https://swiftype.com/documentation/site-search/overview#language-optimization

#### Document types

When using Site Search every document has an associated DocumentType.

You can list available document types in an engine by using the `Client::listDocumentTypes` method:

```php
  $documentTypes = $client->listDocumentTypes('my-engine');
```

In order to index documents you need to create at least one DocumentType in your engine. This can be done by using the Client::createDocumentType` method:

```
  $documentType = $client->createDocumentType('my-engine', 'my-document-type');
```

#### Index some documents

In order to index some documents in the Engine you can use the `Client::createOrUpdateDocuments` method:

```php
    $documents = [
      [
        'external_id' => 'first-document',
        'fields'      => [
          ['name' => 'title', 'value' => 'First document title', 'type' => 'string'],
          ['name' => 'content', 'value' => 'Text for the first document.', 'type' => 'string'],
        ]
      ],
      [
        'external_id' => 'other-document',
        'fields'      => [
          ['name' => 'title', 'value' => 'Other document title', 'type' => 'string'],
          ['name' => 'content', 'value' => 'Text for the other document.', 'type' => 'string'],
        ]
      ],
    ];

    $indexingResults = $client->createOrUpdateDocuments('my-engine', 'my-document-type', $documents);
```

**Notes:**

- The `$indexingResults` array will contains the result of the indexation of each documents. You should always check the content of the result.

- A full list of available field types and associated use cases is available here: https://swiftype.com/documentation/site-search/overview#fieldtype

- Full documentation for the endpoint and other method available to index documents is available here: https://swiftype.com/documentation/site-search/indexing.

#### Search

In order to search in your Engine you can use the `Client::search` method :

```php
    $searchResponse = $client->search('my-engine', 'fulltext search query');
```

An optional `$searchRequestParams` can be used to pass additional parameters to the Search API endpoint (pagination, filters, facets, ...):

```php
    $searchParams = ['per_page' => 10, 'page' => 2];
    $searchResponse = $client->search('my-engine', 'fulltext search query', $searchParams);
```

Allowed params are :

Param name                        | Description                             | Documentation URL
----------------------------------|-----------------------------------------|--------------------------------------------------------------------------
`per_page` and `page`             | Control pagination.                     | https://swiftype.com/documentation/site-search/searching/pagination
`document_types`                  | Searched document types.                | https://swiftype.com/documentation/site-search/searching/documenttypes
`filters`                         | Search filters                          | https://swiftype.com/documentation/site-search/searching/filtering
`facets`                          | Search facets.                          | https://swiftype.com/documentation/site-search/searching/faceting
`boosts`                          | Search boosts.                          | https://swiftype.com/documentation/site-search/searching/boosting
`fetch_fields`                    | Fields returned by the search.          | https://swiftype.com/documentation/site-search/searching/fetch-fields
`search_fields`                   | Field (weighted) used by the search.    | https://swiftype.com/documentation/site-search/searching/field-weights
`highlight_fields`                | Field highlighting configuration.       | https://swiftype.com/documentation/site-search/searching/highlight-fields
`sort_field` and `sort_direction` | Result sort order configuration         | https://swiftype.com/documentation/site-search/searching/sorting
`spelling`                        | Control over the spellchecker behavior. | https://swiftype.com/documentation/site-search/searching/spelling

### Clients methods

Method      | Description | Documentation
------------|-------------|--------------
**`asyncCreateOrUpdateDocuments`**| Async bulk creation or update of documents in an engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br />   - `$documents` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/indexing#bulk_indexing)
**`createDocument`**| Create a new document in an engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br />   - `$documentExternalId` (required) <br />   - `$documentFields` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/indexing#add-document)
**`createDocumentType`**| Create a new document type in an engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeName` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/indexing#add-documenttype)
**`createDocuments`**| Bulk creation of documents in an engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br />   - `$documents` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/indexing#bulk_create)
**`createEngine`**| Create a new API based engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$engineLanguage`<br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/engines#create)
**`createOrUpdateDocument`**| Create or update a document in an engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br />   - `$documentExternalId` (required) <br />   - `$documentFields` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/indexing#add-document)
**`createOrUpdateDocuments`**| Bulk creation or update of documents in an engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br />   - `$documents` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/indexing#bulk_create_or_update_verbose)
**`deleteDocument`**| Delete a document from the engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br />   - `$externalId` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/indexing#delete-external-id)
**`deleteDocumentType`**| Delete a document type by id.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/indexing#documenttypes-delete)
**`deleteDocuments`**| Bulk delete of documents in an engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br />   - `$documents` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/indexing#bulk_destroy)
**`deleteEngine`**| Delete an engine by name.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/engines#destroy)
**`getAutoselectsCountAnalyticsDocumentType`**| Retrieve number of autoselects (number of clicked results in the autocomplete) per day over a period for a document type.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br />   - `$startDate`<br />   - `$endDate`<br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/analytics#autoselects)
**`getAutoselectsCountAnalyticsEngine`**| Retrieve number of autoselects (number of clicked results in the autocomplete) per day over a period for an engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$startDate`<br />   - `$endDate`<br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/analytics#autoselects)
**`getClicksCountAnalyticsDocumentType`**| Retrieve number of clicks per day over a period for a document type.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br />   - `$startDate`<br />   - `$endDate`<br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/analytics#clicks)
**`getClicksCountAnalyticsEngine`**| Retrieve number of clicks per day over a period for an engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$startDate`<br />   - `$endDate`<br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/analytics#clicks)
**`getDocument`**| Retrieve a document from the engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br />   - `$externalId` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/indexing#document-single)
**`getDocumentReceipts`**| Check the status of document receipts issued by aync bulk indexing.<br/> <br/> **Parameters :** <br />  - `$receiptIds` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/indexing#bulk_create_or_update_verbose)
**`getDocumentType`**| Get a document type by id.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/indexing#documenttypes-single)
**`getEngine`**| Retrieves an engine by name.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/engines#one-engine)
**`getSearchCountAnalyticsDocumentType`**| Get the number of searches per day for an document type.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br />   - `$startDate`<br />   - `$endDate`<br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/analytics#searches)
**`getSearchCountAnalyticsEngine`**| Get the number of searches per day for an engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$startDate`<br />   - `$endDate`<br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/analytics#searches)
**`getTopNoResultQueriesAnalyticsDocumentType`**| Retrieve top queries with no result and usage count over a period for a document type.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br />   - `$startDate`<br />   - `$endDate`<br />   - `$currentPage`<br />   - `$pageSize`<br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/analytics#top_no_result_queries)
**`getTopNoResultQueriesAnalyticsEngine`**| Retrieve top queries with no result and usage count over a period for an engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$startDate`<br />   - `$endDate`<br />   - `$currentPage`<br />   - `$pageSize`<br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/analytics#top_no_result_queries)
**`getTopQueriesAnalyticsDocumentType`**| Retrieve top queries and usage count over a period for a document type.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br />   - `$startDate`<br />   - `$endDate`<br />   - `$currentPage`<br />   - `$pageSize`<br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/analytics#top_queries)
**`getTopQueriesAnalyticsEngine`**| Retrieve top queries and usage count over a period for an engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$startDate`<br />   - `$endDate`<br />   - `$currentPage`<br />   - `$pageSize`<br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/analytics#top_queries)
**`listDocumentTypes`**| List all document types for an engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/indexing#documenttypes-all)
**`listDocuments`**| List all documents in an engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/indexing#document-all)
**`listEngines`**| Retrieves all engines with optional pagination support.<br/> <br/> **Parameters :** <br />  - `$currentPage`<br />   - `$pageSize`<br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/engines#list)
**`logClickthrough`**| Record a clickthrough for a particular result.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br />   - `$documentId` (required) <br />   - `$queryText` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/analytics#recording_clickthroughs)
**`search`**| Run a search request accross an engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$queryText` (required) <br />   - `$searchRequestParams`<br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/searching)
**`suggest`**| Run an autocomplete search request accross an engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$queryText` (required) <br />   - `$searchRequestParams`<br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/autocomplete)
**`updateDocumentFields`**| Update fields of a document.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br />   - `$externalId` (required) <br />   - `$fields` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/indexing#updating_fields)
**`updateDocuments`**| Bulk update of documents in an engine.<br/> <br/> **Parameters :** <br />  - `$engineName` (required) <br />   - `$documentTypeId` (required) <br />   - `$documents` (required) <br/>|[Endpoint Documentation](https://swiftype.com/documentation/site-search/indexing#bulk_update)

## Development

Code for the endpoints is generated automatically using a custom version of [OpenAPI Generator](https://github.com/openapitools/openapi-generator).

To regenerate endpoints, use the docker laucher packaged in `vendor/bin`:

```bash
./vendor/bin/swiftype-codegen.sh
```

The custom generator will be built and launched using the following Open API spec file : `resources/api/api-spec.yml`.

You can then commit and PR the modified api-spec file and your endpoints code files.

The client class and readme may be changed in some cases. Do not forget to include them in your commit!

## FAQ 🔮

### Where do I report issues with the client?

If something is not working as expected, please open an [issue](https://github.com/swiftype/swiftype-site-search-php/issues/new).

### Where can I find the full API documentation ?

Your best bet is to read the [documentation](https://swiftype.com/documentation/site-search).

### Where else can I go to get help?

You can checkout the [Elastic community discuss forums](https://discuss.elastic.co/c/site-search).

## Contribute 🚀

We welcome contributors to the project. Before you begin, a couple notes...

+ Before opening a pull request, please create an issue to [discuss the scope of your proposal](https://github.com/swiftype/swiftype-site-search-php/issues).
+ Please write simple code and concise documentation, when appropriate.

## License 📗

[Apache 2.0](https://github.com/swiftype/swiftype-site-search-php/blob/master/LICENSE) © [Elastic](https://github.com/elastic)

Thank you to all the [contributors](https://github.com/swiftype/swiftype-site-search-php/graphs/contributors)!

