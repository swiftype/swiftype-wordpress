<?php

namespace Swiftype\SiteSearch\Wordpress\Config;

/**
 * Configuration management for the Site Search Wordpress plugin:
 *
 * @author Matt Riley <mriley@swiftype.com>, Quin Hoxie <qhoxie@swiftype.com>, Aurelien Foucret <aurelien.foucret@elastic.co>
 */
class Config
{
    /**
     * @var string
     */
    const DEFAULT_DOCUMENT_TYPE = 'posts';

    /**
     * Retrieve the configured API Key.
     *
     * @return string|NULL
     */
    public function getApiKey()
    {
        return \get_option('swiftype_api_key');
    }

    /**
     * Return the document type used to index posts.
     *
     * @return string
     */
    public function getDocumentType()
    {
        return self::DEFAULT_DOCUMENT_TYPE;
    }

    /**
     * Retrieve the configured Engine Slug.
     *
     * @return string|NULL
     */
    public function getEngineSlug() {
        return \get_option('swiftype_engine_slug');
    }

    /**
     * Update the API Key into the configuration.
     *
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        \update_option('swiftype_api_key', $apiKey);
    }

    /**
     * Update the Engine Slug into the configuration.
     *
     * @param string $apiKey
     */
    public function setEngineSlug($engineSlug)
    {
        \update_option('swiftype_engine_slug', $engineSlug);
    }

    /**
     * Reset the plugin configuration.
     */
    public function reset()
    {
        \delete_option('swiftype_api_key');
        \delete_option('swiftype_engine_slug');
    }

    /**
     * Return the list of post types that are indexed by the engine.
     *
     * @return string[]
     */
    public function allowedPostTypes()
    {
        $allowedPostTypes = ['post', 'page'];

        if (function_exists('get_post_types')) {
            $allowedPostTypes = array_merge(
                \get_post_types(['exclude_from_search' => '0']),
                \get_post_types(['exclude_from_search' => false])
           );
        }

        return $allowedPostTypes;
    }
}
