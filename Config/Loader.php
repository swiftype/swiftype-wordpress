<?php

namespace Swiftype\SiteSearch\Wordpress\Config;

class Loader
{
    private $config;

    public static function create(callable $callback = null)
    {
        if ($callback !== null) {
            \add_action('swiftype_config_loaded', $callback);
        }

        return (new static());
    }

    private function __construct()
    {
        $this->config = new Config();
        \add_action('init', function() {$this->loadConfig();});
    }

    private function loadConfig()
    {
        if (\is_admin() && \current_user_can('manage_options') && isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'swiftype_set_api_key':
                    $this->config->reset();
                    $this->config->setApiKey($_POST['api_key']);
                    break;
                case 'swiftype_create_engine':
                    $this->config->setEngineSlug($_POST['engine_name']);
                    break;
                case 'swiftype_clear_config':
                    $this->config->reset();
                    break;
            }
        }

        \do_action('swiftype_config_loaded', $this->config);
    }
}
