<?php

namespace Swiftype\SiteSearch\Wordpress\Document;

use Swiftype\SiteSearch\Client;
use Swiftype\SiteSearch\Wordpress\Config\Config;

class Indexer
{
    private $documentMapper;

    private $client;

    private $config;


    public function __construct(Client $client, Config $config)
    {
        $this->documentMapper = new Mapper();
        $this->client         = $client;
        $this->config         = $config;

        add_action('future_to_publish', [$this, 'handleFutureToPublish']);
        add_action('save_post', [$this, 'handleSavePost'], 99, 1);
        add_action('transition_post_status', [$this, 'handleTransitionPostStatus'], 99, 3);
        add_action('trashed_post', [$this, 'handleTrashedPost']);

        add_action('swiftype_batch_post_index', [$this, 'handlePostBatchIndex']);
        add_action('swiftype_batch_post_delete', [$this, 'handlePostBatchDelete']);
    }

    public function handleSavePost($postId)
    {
        $post = get_post($postId);

        if ("publish" == $post->post_status ) {
            $this->indexPost($postId);
        }
    }

    public function handleTransitionPostStatus($newStatus, $oldStatus, $post)
    {
        if ("publish" == $oldStatus && "publish" != $newStatus) {
            $this->deletePost($post->ID);
        }
    }

    public function handleTrashedPost($postId)
    {
        $this->deletePost($postId);
    }

    public function handleFutureToPublish($post) {
        if ("publish" == $post->post_status) {
            $this->indexPost($post->ID);
        }
    }

    public function handlePostBatchIndex($posts = [])
    {
        $indexedPosts = array_filter($posts, [$this, 'shouldIndexPost']);
        $documents = array_map([$this->documentMapper, 'convertToDocument'], $indexedPosts);

        $stats = ['errors' => 0, 'success' => 0];

        if (!empty($documents)) {
            $engineSlug = $this->config->getEngineSlug();
            $documentType = $this->config->getDocumentType();

            $indexingResponse = $this->client->createOrUpdateDocuments($engineSlug, $documentType, $documents);

            foreach ($indexingResponse as $currentDocIndexing) {
                if ($currentDocIndexing === true) {
                    $stats['success']++;
                } else {
                    $stats['errors']++;
                }
            }
        }

        \do_action('swiftype_batch_post_index_result', $stats);
    }

    public function handlePostBatchDelete($postIds = [])
    {
        $deleted = 0;

        if (!empty($postIds)) {
            $engineSlug = $this->config->getEngineSlug();
            $documentType = $this->config->getDocumentType();

            $deleteResponse = $this->client->deleteDocuments($engineSlug, $documentType, $postIds);

            foreach ($deleteResponse as $currentDocIndexing) {
                if ($currentDocIndexing === true) {
                    $deleted = 0;
                }
            }
        }

        \do_action('swiftype_batch_post_delete_result', $deleted);
    }

    private function indexPost($postId)
    {
        $post = get_post($postId);

        if ($this->shouldIndexPost($post)) {
            $document     = $this->documentMapper->convertToDocument($post);
            $engine       = $this->config->getEngineSlug();
            $documentType = $this->config->getDocumentType();
            try {
                $this->client->createOrUpdateDocument($engine, $documentType, $document['external_id'], $document['fields']);
            } catch(\Swiftype\Exception\SwiftypeException $e) {
                # TODO : report error.
                return;
            }
        }
    }

    private function deletePost($postId)
    {
        try {
            $this->client->deleteDocument($this->config->getEngineSlug(), $this->config->getDocumentType(), $postId);
        } catch(\Swiftype\Exception\SwiftypeException $e) {
            # TODO : report error.
            return;
        }
    }

    private function shouldIndexPost($post)
    {
        return in_array($post->post_type, $this->config->allowedPostTypes()) && !empty($post->post_title );
    }
}
