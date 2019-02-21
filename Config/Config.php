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
     * Retrieve the configured language.
     *
     * @return string|NULL
     */
    public function getLanguage() {
        $language = \get_option('swiftype_language');

        if (empty($language)) {
            $language = null;
        }

        return $language;
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
     * @param string $engineSlug
     */
    public function setEngineSlug($engineSlug)
    {
        \update_option('swiftype_engine_slug', $engineSlug);
    }

    /**
     * Update the langugage into the configuration.
     *
     * @param string $language
     */
    public function setLanguage($language)
    {
        if (empty(trim($language))) {
            \delete_option('swiftype_language');
        } else {
            \update_option('swiftype_language', $language);
        }
    }

    /**
     * Reset the plugin configuration.
     */
    public function reset()
    {
        \delete_option('swiftype_api_key');
        \delete_option('swiftype_engine_slug');
        \delete_option('swiftype_language');
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
