<?php

namespace Swiftype\SiteSearch\Wordpress\Search;

use Swiftype\SiteSearch\Wordpress\Config\Config;
use Swiftype\SiteSearch\Client;
use Swiftype\Exception\NotFoundException;
use Swiftype\Exception\SwiftypeException;

class PostSearch
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $searchResult = null;

    public function __construct(Client $client, Config $config)
    {
        $this->client = $client;
        $this->config = $config;

        if (!is_admin()) {
            add_action('wp_enqueue_scripts', [$this, 'enqueueSwiftypeAssets']);
            add_action('pre_get_posts', [$this, 'getPostsFromSwiftype'], 11);
            add_filter('posts_search', [$this, 'clearSqlSearchClause']);
            add_filter('post_limits', [$this, 'setSqlLimit']);
            add_filter('the_posts', [$this, 'getSearchResultPosts']);
        }
    }

    public function enqueueSwiftypeAssets()
    {
        $rootDir = __DIR__ . '/../swiftype.php';
        \wp_enqueue_style('swiftype', \plugins_url('assets/autocomplete.css', $rootDir));
        \wp_enqueue_script('swiftype', \plugins_url('assets/install_swiftype.min.js', $rootDir));
        \wp_localize_script('swiftype', 'swiftypeParams', ['engineKey' => $this->getEngineKey()]);
    }

    public function getPostsFromSwiftype($wpQuery)
    {
        if( $this->canWeSearch($wpQuery)) {
            // Get query string from 's' url parameter.
            $queryString = $this->getQueryString();

            // Get params from url. page, filters etc.
            $queryParams = $this->getQueryParams();

            try {
                $this->searchResult = $this->client->search($this->config->getEngineSlug(), $queryString, $queryParams);
                set_query_var('post__in', $this->extractPostIds());
                add_filter('post_class', [$this, 'swiftypePostClass']);
                do_action('swiftype_search_result', $this->searchResult);

            } catch (SwiftypeException $e) {
                $this->searchResult = null;
            }
        }
    }

    public function clearSqlSearchClause($search)
    {
        if ($this->searchResult !== null) {
            $search = '';
        }

        return $search;
    }

    public function setSqlLimit( $limit )
    {
        if ($this->searchResult !== null) {
            $limit = '';
        }

        return $limit;
    }

    public function getSearchResultPosts($posts)
    {
        if ($this->searchResult !== null) {
            $resultInfo = $this->searchResult['info'][$this->config->getDocumentType()];

            global $wp_query;
            $wp_query->max_num_pages = $resultInfo['num_pages'];
            $wp_query->found_posts = $resultInfo['total_result_count'];

            $postLookupTable = array();
            foreach($posts as $post) {
                $postLookupTable[$post->ID] = $post;
            }


            $postIds = $this->extractPostIds();

            $posts = [];

            foreach ($postIds as $postId) {
                if (isset($postLookupTable[$postId])) {
                    $posts[] = $postLookupTable[$postId];
                }
            }
        }

        return $posts;
    }


    /**
     * Add a Swiftype-specific post class to the list of post classes.
     */
    public function swiftypePostClass($classes)
    {
        global $post;
        $classes[] = 'swiftype-result';
        $classes[] = 'swiftype-result-' . $post->ID;

        return $classes;
    }

    private function extractPostIds()
    {
        $postIds = [];
        $documentType = $this->config->getDocumentType();

        if ($this->searchResult !== null && isset($this->searchResult['records'][$documentType])) {
            foreach ($this->searchResult['records'][$documentType] as $hit) {
                $postIds[] = (int) $hit['external_id'];
            }
        }

        if (empty($postIds)) {
            $postIds = [0];
        }

        return $postIds;
    }

    /**
     * Get query string from 's' url parameter.
     *
     * @return string
     */
    private function getQueryString()
    {
        return apply_filters('swiftype_search_query_string', stripslashes(get_search_query(false)));;
    }

    /**
     * Get and return common url parameters for swiftype search.
     *
     * @return array
     **/
    private function getQueryParams()
    {
        $documentType = $this->config->getDocumentType();
        $params = [
            'page' => get_query_var('paged') ? get_query_var('paged') : 1,
            'document_types' => [$this->config->getDocumentType()],
        ] ;

        if ( isset( $_GET['st-cat'] ) && ! empty($_GET['st-cat'])) {
            $params['filters'][$documentType]['category'] = sanitize_text_field($_GET['st-cat']);
        }

        if ( isset( $_GET['st-facet-field'] ) && isset( $_GET['st-facet-term'])) {
            $params['filters'][$documentType][$_GET['st-facet-field']] = sanitize_text_field($_GET['st-facet-term']);
        }

        $params['facets'] = ['posts' => ['category', 'tags']];

        $params = apply_filters('swiftype_search_params', $params);

        return $params;
    }

    private function getEngineKey()
    {
        $engine = $this->client->getEngine($this->config->getEngineSlug());

        return $engine['key'];
    }

    private function canWeSearch($wp_query)
    {
        $isMainQuery   = function_exists('is_main_query') && $wp_query->is_main_query();
        $isSearch      = \is_search();
        $hasEngineSlug = !empty($this->config->getEngineSlug());

        $canSearch = $isMainQuery && $isSearch && $hasEngineSlug;

        if ($canSearch) {
            try {
                $this->client->getEngine($this->config->getEngineSlug());
            } catch (NotFoundException $e) {
                    $canSearch = false;
            }
        }

        return $canSearch;
    }
}