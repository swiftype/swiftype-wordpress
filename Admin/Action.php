<?php

namespace Swiftype\SiteSearch\Wordpress\Admin;

use Swiftype\SiteSearch\Wordpress\AbstractSwiftypeComponent;

/**
 * Implementation of the admin async actions for the Site Search Wordpress plugin:
 * - index_batch_of_posts
 * - delete_batch_of_trashed_posts
 *
 * @author Matt Riley <mriley@swiftype.com>, Quin Hoxie <qhoxie@swiftype.com>, Aurelien Foucret <aurelien.foucret@elastic.co>
 */
class Action extends AbstractSwiftypeComponent
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        \add_action('wp_ajax_index_batch_of_posts', [$this, 'asyncIndexBatchOfPosts']);
        \add_action('wp_ajax_delete_batch_of_trashed_posts', [$this, 'asyncDeleteBatchOfTrashedPosts']);
    }

    /**
     * Index a batch of posts
     *
     * Sends a batch of posts to the Swiftype API via the client in order to index them in the server-side search engine.
     * This method is called asynchronously via client-side Ajax when an admin clicks the "synchronize with swiftype" button
     * on the plugin Admin page.
     */
    public function asyncIndexBatchOfPosts()
    {
        \check_ajax_referer('swiftype-ajax-nonce');

        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $batchSize = isset($_POST['batch_size']) ? intval($_POST['batch_size']) : 10;

        $posts = $this->getPosts($offset, $batchSize, 'publish');
        $totalPosts = count($posts);

        \add_action('swiftype_batch_post_index_result', function($stats) use ($totalPosts) {
            header('Content-Type: application/json');
            echo \wp_json_encode(['total' => $totalPosts, 'num_written' => $stats['success']]);
            die;
        });

        \do_action('swiftype_batch_post_index', $posts);

        /* TODO : Better error management */
    }

    /**
     * Delete all posts from the index that shouldn't be indexed in the search engine
     *
     * Sends a request to the Swiftype API to remove from the server-side search engine any posts that are not 'published'.
     * This method is called asynchronously via client-side Ajax when an admin clicks the "synchronize with swiftype" button
     * on the plugin Admin page.
     */
    public function asyncDeleteBatchOfTrashedPosts() {
        \check_ajax_referer('swiftype-ajax-nonce');
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $batchSize = isset($_POST['batch_size']) ? intval($_POST['batch_size']) : 10;

        $posts = $this->getPosts($offset, $batchSize, array_diff(get_post_stati(), ['publish']));
        $totalPosts = count($posts);

        $postIds = array_map(function($post) { return $post->ID; }, $posts);

        \do_action('swiftype_batch_post_delete', $postIds);

        header("Content-Type: application/json");
        echo wp_json_encode(['total' => $totalPosts]);
        die();

        /* TODO : Better error management */
    }

    /**
     * Retrieve a list of post filtered on a status or a status list.
     *
     * Only posts that are meant to be indexed are retrieved (see Config::allowedPostTypes).
     *
     * @param int          $offset
     * @param int          $batchSize
     * @param string|array $status
     *
     * @return array
     */
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
}
