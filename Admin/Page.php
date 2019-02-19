<?php

namespace Swiftype\SiteSearch\Wordpress\Admin;

use Swiftype\SiteSearch\Wordpress\Config\Config;
use Swiftype\SiteSearch\Client;
use Swiftype\Exception\NotFoundException;

class Page
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Config
     */
    private $config;


    public function __construct($client, Config $config)
    {
        $this->client = $client;
        $this->config = $config;

        new \Swiftype\SiteSearch\Wordpress\Admin\Menu($this);
        \add_action('admin_init', [$this, 'initializeAdmin']);

        \add_action('wp_ajax_get_indexed_documents_count', [$this, 'asyncGetIndexedDocumentsCount']);
        \add_action('wp_ajax_index_batch_of_posts', [$this, 'asyncIndexBatchOfPosts']);
        \add_action('wp_ajax_delete_batch_of_trashed_posts', [$this, 'asyncDeleteBatchOfTrashedPosts']);
    }

    public function getContent()
    {
        $isAuth = $this->config->getApiKey() && $this->client !== null;

        if (!$isAuth) {
            include(__DIR__ . '/../swiftype-authorize.php');
        } else if (!$this->config->getEngineSlug()) {
            include(__DIR__ . '/../swiftype-choose-engine.php');
        } else {
            include(__DIR__ . '/../swiftype-controls.php');
        }
    }

    public function initializeAdmin()
    {
        if (\current_user_can('manage_options')) {
            \add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        }

        $this->initEngine();
    }

    public function enqueueAdminAssets($hook)
    {
        if ('toplevel_page_swiftype' == $hook) {
            \wp_enqueue_style('admin_styles', \plugins_url('assets/admin_styles.css', __DIR__ . '/../swiftype.php'));
        }
    }

    public function getIndexedDocumentsCount()
    {
        $documentTypeInfo = $this->client->getDocumentType($this->config->getEngineSlug(), $this->config->getDocumentType());

        return $documentTypeInfo['document_count'];
    }

    public function asyncGetIndexedDocumentsCount()
    {
        \check_ajax_referer('swiftype-ajax-nonce');
        header('Content-Type: application/json');
        echo wp_json_encode(['num_indexed_documents' => $this->getIndexedDocumentsCount()]);
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
            'post_type' => $this->config->allowedPostTypes(),
        ];

        return \get_posts($query);
    }

    private function initEngine()
    {
        if ($this->client && $this->config->getEngineSlug()) {
            try {
                $this->client->getEngine($this->config->getEngineSlug());
            } catch(NotFoundException $e) {
                $this->client->createEngine($this->config->getEngineSlug());
            }
            try {
                $this->client->getDocumentType($this->config->getEngineSlug(), $this->config->getDocumentType());
            } catch(NotFoundException $e) {
                $this->client->createDocumentType($this->config->getEngineSlug(), $this->config->getDocumentType());
            }
        }
    }
}
