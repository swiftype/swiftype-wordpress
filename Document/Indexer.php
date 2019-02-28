<?php

namespace Swiftype\SiteSearch\Wordpress\Document;

use Swiftype\SiteSearch\Wordpress\AbstractSwiftypeComponent;

/**
 * Provides indexing method for the Swiftype Site Search plugin.
 *
 * @author Matt Riley <mriley@swiftype.com>, Quin Hoxie <qhoxie@swiftype.com>, Aurelien Foucret <aurelien.foucret@elastic.co>
 */
class Indexer extends AbstractSwiftypeComponent
{
    /**
     * @var Mapper
     */
    private $documentMapper;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->documentMapper = new Mapper();
        add_action('swiftype_engine_loaded', [$this, 'installHooks']);
    }

    /**
     * Install hooks only when the engine is created (swiftype_engine_loaded).
     */
    public function installHooks()
    {
        add_action('future_to_publish', [$this, 'handleFutureToPublish']);

        foreach ($this->getConfig()->allowedPostTypes() as $postType) {
            add_action("rest_after_insert_{$postType}", [$this, 'handleRestUpdatePost']);
        }

        add_action('save_post', [$this, 'handleSavePost'], 99, 1);

        add_action('transition_post_status', [$this, 'handleTransitionPostStatus'], 99, 3);
        add_action('trashed_post', [$this, 'handleTrashedPost']);


        add_action('swiftype_batch_post_index', [$this, 'handlePostBatchIndex']);
        add_action('swiftype_batch_post_delete', [$this, 'handlePostBatchDelete']);
    }

    /**
     * Handle post indexing when the post is saved.
     *
     * @param int $postId
     */
    public function handleSavePost($postId)
    {
        if (!defined('REST_REQUEST') || REST_REQUEST != true) {
            $post = get_post($postId);
            $this->handleRestUpdatePost($post);
        }
    }

    /**
     * Handle post indexing when the post is saved.
     *
     * @param \WP_Post $post
     */
    public function handleRestUpdatePost($post)
    {
        if ("publish" == $post->post_status) {
            $this->indexPost($post->ID);
        }
    }

    /**
     * Handle post deletion when a post is unpublished.
     *
     * @param string $newStatus
     * @param string $oldStatus
     * @param object $post
     */
    public function handleTransitionPostStatus($newStatus, $oldStatus, $post)
    {
        if ("publish" == $oldStatus && "publish" != $newStatus) {
            $this->deletePost($post->ID);
        }
    }

    /**
     * Handle post deletion when a post is trashed.
     *
     * @param int $postId
     */
    public function handleTrashedPost($postId)
    {
        $this->deletePost($postId);
    }

    /**
     * Handle post indexing when the post is published.
     *
     * @param object $post
     */
    public function handleFutureToPublish($post) {
        if ("publish" == $post->post_status) {
            $this->indexPost($post->ID);
        }
    }

    /**
     * Batch indexing of a list of post.
     *
     * @param array $posts
     */
    public function handlePostBatchIndex($posts = [])
    {
        $indexedPosts = array_filter($posts, [$this, 'shouldIndexPost']);
        $documents = array_map([$this->documentMapper, 'convertToDocument'], $indexedPosts);

        $stats = ['errors' => 0, 'success' => 0];

        if (!empty($documents)) {
            $client       = $this->getClient();
            $engineSlug   = $this->getConfig()->getEngineSlug();
            $documentType = $this->getConfig()->getDocumentType();

            $indexingResponse = $client->createOrUpdateDocuments($engineSlug, $documentType, $documents);

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

    /**
     * Batch deletion of a list of post.
     *
     * @param array $postIds
     */
    public function handlePostBatchDelete($postIds = [])
    {
        $deleted = 0;

        if (!empty($postIds)) {
            $client       = $this->getClient();
            $engineSlug   = $this->getConfig()->getEngineSlug();
            $documentType = $this->getConfig()->getDocumentType();

            $deleteResponse = $client->deleteDocuments($engineSlug, $documentType, $postIds);

            foreach ($deleteResponse as $currentDocIndexing) {
                if ($currentDocIndexing === true) {
                    $deleted = 0;
                }
            }
        }

        \do_action('swiftype_batch_post_delete_result', $deleted);
    }

    /**
     * Index a single post.
     *
     * @param int $postId
     */
    private function indexPost($postId)
    {
        $post = get_post($postId);

        if ($this->shouldIndexPost($post)) {
            $client       = $this->getClient();
            $document     = $this->documentMapper->convertToDocument($post);
            $engine       = $this->getConfig()->getEngineSlug();
            $documentType = $this->getConfig()->getDocumentType();

            try {
                $client->createOrUpdateDocument($engine, $documentType, $document['external_id'], $document['fields']);
            } catch(\Swiftype\Exception\SwiftypeException $e) {
                # TODO : report error.
                return;
            }
        }
    }

    /**
     * Delete a single post.
     *
     * @param int $postId
     */
    private function deletePost($postId)
    {
        try {
            $client       = $this->getClient();
            $engine       = $this->getConfig()->getEngineSlug();
            $documentType = $this->getConfig()->getDocumentType();

            $client->deleteDocument($engine, $documentType, $postId);
        } catch(\Swiftype\Exception\SwiftypeException $e) {
            # TODO : report error.
            return;
        }
    }

    /**
     * Indicates if a post should be indexed or not.
     *
     * @param object $post
     *
     * @return boolean
     */
    private function shouldIndexPost($post)
    {
        return in_array($post->post_type, $this->getConfig()->allowedPostTypes()) && !empty($post->post_title);
    }
}
