<?php

namespace Swiftype\SiteSearch\Wordpress\Config;

class Config
{
    const DEFAULT_DOCUMENT_TYPE = 'posts';

    public function __construct()
    {
        $this->processConfigUpdate();
    }

    public function getApiKey()
    {
        return \get_option('swiftype_api_key');
    }

    public function getDocumentType()
    {
        return self::DEFAULT_DOCUMENT_TYPE;
    }

    public function getEngineSlug() {
        return \get_option('swiftype_engine_slug');
    }

    public function setApiKey($apiKey)
    {
        \update_option('swiftype_api_key', $apiKey);
    }

    public function setEngineSlug($engineSlug)
    {
        \update_option('swiftype_engine_slug', $engineSlug);
    }

    public function reset()
    {
        \delete_option('swiftype_api_key');
        \delete_option('swiftype_engine_slug');
    }

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

    public function processConfigUpdate()
    {
        if (\is_admin() && \current_user_can('manage_options') && isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'swiftype_set_api_key':
                    $this->setApiKey($_POST['api_key']);
                    break;
                case 'swiftype_create_engine':
                    $this->setEngineSlug($_POST['engine_name']);
                    break;
                case 'swiftype_clear_config':
                    $this->reset();
                    break;
            }
        }
    }
}
