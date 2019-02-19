<?php

namespace Swiftype\SiteSearch\Wordpress\Document;

use Swiftype\SiteSearch\Wordpress\AbstractSwiftypeComponent;

class Indexer extends AbstractSwiftypeComponent
{
    private $documentMapper;

    public function __construct()
    {
        parent::__construct();
        $this->documentMapper = new Mapper();
        add_action('swiftype_engine_loaded', [$this, 'installHooks']);
    }

    public function installHooks()
    {
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
            $engineSlug = $this->getConfig()->getEngineSlug();
            $documentType = $this->getConfig()->getDocumentType();

            $indexingResponse = $this->getClient()->createOrUpdateDocuments($engineSlug, $documentType, $documents);

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
            $engineSlug = $this->getConfig()->getEngineSlug();
            $documentType = $this->getConfig()->getDocumentType();

            $deleteResponse = $this->getClient()->deleteDocuments($engineSlug, $documentType, $postIds);

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
            $engine       = $this->getConfig()->getEngineSlug();
            $documentType = $this->getConfig()->getDocumentType();
            try {
                $this->getClient()->createOrUpdateDocument($engine, $documentType, $document['external_id'], $document['fields']);
            } catch(\Swiftype\Exception\SwiftypeException $e) {
                # TODO : report error.
                return;
            }
        }
    }

    private function deletePost($postId)
    {
        try {
            $this->getClient()->deleteDocument($this->getConfig()->getEngineSlug(), $this->getConfig()->getDocumentType(), $postId);
        } catch(\Swiftype\Exception\SwiftypeException $e) {
            # TODO : report error.
            return;
        }
    }

    private function shouldIndexPost($post)
    {
        return in_array($post->post_type, $this->getConfig()->allowedPostTypes()) && !empty($post->post_title);
    }
}
