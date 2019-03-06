<?php

namespace Swiftype\SiteSearch\Wordpress\Admin;

use Swiftype\SiteSearch\Wordpress\AbstractSwiftypeComponent;
use Swiftype\Exception\SwiftypeException;

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
        \add_action('wp_ajax_update_facet_config', [$this, 'asyncUpdateFacetConfig']);
        \add_action('admin_action_swiftype_set_api_key', [$this, 'setApiKey']);
        \add_action('admin_action_swiftype_create_engine', [$this, 'createEngine']);
        \add_action('admin_action_swiftype_clear_config', [$this, 'clearConfig']);
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
    public function asyncDeleteBatchOfTrashedPosts()
    {
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
     * Persist facet configuration into the config.
     */
    public function asyncUpdateFacetConfig()
    {
        \check_ajax_referer('swiftype-ajax-nonce');
        header("Content-Type: application/json");

        $facetConfig = isset($_POST['facet_config']) ? $_POST['facet_config'] : '[]';
        $this->getConfig()->setFacetConfig($facetConfig);

        echo wp_json_encode(['success' => true]);
        die();
    }

    /**
     * Admin action used to configure API Key.
     */
    public function setApiKey()
    {
        $this->getConfig()->reset();
        $this->getConfig()->setApiKey(trim($_POST['api_key']));

        $redirectParams = [];

        \do_action('loadConfig', $this->getConfig());

        if (null === $this->getClient()) {
            $redirectParams['error'] = true;
        }

        $this->redirect($redirectParams);
    }

    /**
     * Admin action used to create the new engine.
     */
    public function createEngine()
    {
        $this->getConfig()->setLanguage(isset($_POST['language']) ? $_POST['language'] : null);
        $this->getConfig()->setEngineSlug(trim($_POST['engine_name']));

        try {
            \do_action('swiftype_create_engine');
            $this->redirect();
        } catch (SwiftypeException $e) {
            $this->redirect(['error' => true]);
        }
    }

    /**
     * Admin action used to reset the config.
     */
    public function clearConfig()
    {
        $this->getConfig()->reset();
        $this->redirect();
    }

    /**
     * Redirect to Swiftype Admin homepage.
     *
     * @param array $params
     */
    private function redirect($params = [])
    {
        $params['page'] = Page::MENU_SLUG;
        $redirectUrl = \add_query_arg($params, \admin_url());
        \wp_redirect($redirectUrl);
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
