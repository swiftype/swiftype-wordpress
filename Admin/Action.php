<?php

namespace Swiftype\SiteSearch\Wordpress\Admin;

use Swiftype\SiteSearch\Wordpress\AbstractSwiftypeComponent;

class Action extends AbstractSwiftypeComponent
{
    public function __construct()
    {
        parent::__construct();
        \add_action('wp_ajax_get_indexed_documents_count', [$this, 'asyncGetIndexedDocumentsCount']);
        \add_action('wp_ajax_index_batch_of_posts', [$this, 'asyncIndexBatchOfPosts']);
        \add_action('wp_ajax_delete_batch_of_trashed_posts', [$this, 'asyncDeleteBatchOfTrashedPosts']);
    }

    public function asyncGetIndexedDocumentsCount()
    {
        \check_ajax_referer('swiftype-ajax-nonce');
        header('Content-Type: application/json');
        echo wp_json_encode(['num_indexed_documents' => $this->getDocumentTypeInfo()['document_count']]);
        die;
    }

    public function asyncIndexBatchOfPosts()
    {
        \check_ajax_referer('swiftype-ajax-nonce');

        $offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
        $batchSize = isset( $_POST['batch_size'] ) ? intval( $_POST['batch_size'] ) : 10;

        $posts = $this->getPosts($offset, $batchSize, 'publish');
        $totalPosts = count($posts);

        \add_action('swiftype_batch_post_index_result', function($stats) use ($totalPosts) {
            header('Content-Type: application/json');
            echo \wp_json_encode(['total' => $totalPosts, 'num_written' => $stats['success']]);
            die;
        });

        \do_action('swiftype_batch_post_index', $posts);
    }

    public function asyncDeleteBatchOfTrashedPosts() {
        \check_ajax_referer('swiftype-ajax-nonce');
        $offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
        $batchSize = isset( $_POST['batch_size'] ) ? intval( $_POST['batch_size'] ) : 10;

        $posts = $this->getPosts($offset, $batchSize, array_diff(get_post_stati(), ['publish']));
        $totalPosts = count($posts);

        $postIds = array_map(function($post) { return $post->ID; }, $posts);

        \do_action('swiftype_batch_post_delete', $postIds);

        header( "Content-Type: application/json" );
        echo wp_json_encode(['total' => $totalPosts]);
        die();
    }

    private function getPosts($offset, $batchSize, $status) {
        $query = [
            'numberposts' => $batchSize,
            'offset' => $offset,
            'orderby' => 'id',
            'order' => 'ASC',
            'post_status' => $status,
            'post_type' => $this->getConfig()->allowedPostTypes(),
        ];

        return \get_posts($query);
    }

    private function getDocumentTypeInfo()
    {
        $engineSlug   = $this->getConfig()->getEngineSlug();
        $documentType = $this->getConfig()->getDocumentType();

        return $this->getClient()->getDocumentType($engineSlug, $documentType);
    }
}
