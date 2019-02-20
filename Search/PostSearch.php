<?php

namespace Swiftype\SiteSearch\Wordpress\Search;

use Swiftype\SiteSearch\Wordpress\AbstractSwiftypeComponent;
use Swiftype\Exception\SwiftypeException;

/**
 * Use Site Search as the main search engine of WP.
 *
 * @author Matt Riley <mriley@swiftype.com>, Quin Hoxie <qhoxie@swiftype.com>, Aurelien Foucret <aurelien.foucret@elastic.co>
 */
class PostSearch extends AbstractSwiftypeComponent
{
    /**
     * @var array
     */
    private $searchResult = null;

    /**
     * Constructor.
     *
     * Install hooks only if not in admin and the search engine is successfully loaded.
     */
    public function __construct()
    {
        parent::__construct();

        if (!is_admin()) {
            add_action('swiftype_engine_loaded', [$this, 'initHooks']);
        }
    }

    /**
     * Install hooks.
     *
     * @param array $engine Engine data
     */
    public function initHooks($engine)
    {
        add_action('wp_enqueue_scripts', function() use($engine) {return $this->enqueueSwiftypeAssets($engine);});
        add_action('pre_get_posts', [$this, 'getPostsFromSwiftype'], 11);
    }

    /**
     * Add Site Search assets.
     *
     * @param array $engine
     */
    public function enqueueSwiftypeAssets($engine)
    {
        $rootDir = __DIR__ . '/../swiftype.php';
        \wp_enqueue_style('swiftype', \plugins_url('assets/autocomplete.css', $rootDir));
        \wp_enqueue_script('swiftype', \plugins_url('assets/install_swiftype.min.js', $rootDir));
        \wp_localize_script('swiftype', 'swiftypeParams', ['engineKey' => $engine['key']]);
    }

    /**
     * Run the search query using Site Search and save the search results.
     *
     * If the search result is successfull we replace the search text by a filter on the matched product ids.
     *
     * @param \WP_Query $wpQuery
     */
    public function getPostsFromSwiftype($wpQuery)
    {
        if ($this->canSearch($wpQuery)) {
            // Get query string from 's' url parameter.
            $queryString = $this->getQueryString();

            // Get params from url. page, filters etc.
            $queryParams = $this->getQueryParams();

            try {
                $this->searchResult = $this->getClient()->search($this->getConfig()->getEngineSlug(), $queryString, $queryParams);
                set_query_var('post__in', $this->extractPostIds());

                add_filter('post_class', [$this, 'swiftypePostClass']);
                add_filter('posts_search', [$this, 'clearSqlSearchClause']);
                add_filter('post_limits', [$this, 'setSqlLimit']);
                add_filter('the_posts', [$this, 'getSearchResultPosts']);

                do_action('swiftype_search_result', $this->searchResult);

            } catch (SwiftypeException $e) {
                $this->searchResult = null;
            }
        }
    }

    /**
     * Remove the fulltext search from the query when the search result is sucessfull.
     *
     * @param string $search
     *
     * @return string
     */
    public function clearSqlSearchClause($search)
    {
        if ($this->searchResult !== null) {
            $search = '';
        }

        return $search;
    }

    /**
     * Remove the limit clause from the query when the search result is sucessfull.
     *
     * @param string $limit
     *
     * @return string
     */
    public function setSqlLimit($limit)
    {
        if ($this->searchResult !== null) {
            $limit = '';
        }

        return $limit;
    }

    /**
     * Reorder the post list using the document order contained in the saved search result.
     *
     * @param \WP_Post[] $posts
     *
     * @return \WP_Post[][]
     */
    public function getSearchResultPosts($posts)
    {
        if ($this->searchResult !== null) {
            $resultInfo = $this->searchResult['info'][$this->getConfig()->getDocumentType()];

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
     *
     * @param array $classes
     *
     * @return array
     */
    public function swiftypePostClass($classes)
    {
        global $post;
        $classes[] = 'swiftype-result';
        $classes[] = 'swiftype-result-' . $post->ID;

        return $classes;
    }

    /**
     * Extract the list of matching post ids from the search result.
     *
     * @return int[]
     */
    private function extractPostIds()
    {
        $postIds = [];
        $documentType = $this->getConfig()->getDocumentType();

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
        $documentType = $this->getConfig()->getDocumentType();
        $params = [
            'page' => get_query_var('paged') ? get_query_var('paged') : 1,
            'document_types' => [$this->getConfig()->getDocumentType()],
        ] ;

        if (isset($_GET['st-cat']) && ! empty($_GET['st-cat'])) {
            $params['filters'][$documentType]['category'] = sanitize_text_field($_GET['st-cat']);
        }

        if (isset($_GET['st-facet-field']) && isset($_GET['st-facet-term'])) {
            $params['filters'][$documentType][$_GET['st-facet-field']] = sanitize_text_field($_GET['st-facet-term']);
        }

        $params = apply_filters('swiftype_search_params', $params);

        return $params;
    }

    /**
     * Indicate if we can search using Site Search.
     *
     * @param \WP_Query $wpQuery
     *
     * @return boolean
     */
    private function canSearch($wpQuery)
    {
        $isMainQuery   = function_exists('is_main_query') && $wpQuery->is_main_query();
        $isSearch      = \is_search();

        return $isMainQuery && $isSearch;;
    }
}
