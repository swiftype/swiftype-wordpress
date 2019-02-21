<?php

namespace Swiftype\SiteSearch\Wordpress\Config;

/**
 * Configuration loading and management.
 *
 * @author Matt Riley <mriley@swiftype.com>, Quin Hoxie <qhoxie@swiftype.com>, Aurelien Foucret <aurelien.foucret@elastic.co>
 */
class Loader
{
    /**
     * @var Config
     */
    private $config;

    /**
     * Load configuration.
     *
     * @param callable $callback Optional function to be called when the configuration is loaded.
     *
     * @return \Swiftype\SiteSearch\Wordpress\Config\Loader
     */
    public static function loadConfig(callable $callback = null)
    {
        if ($callback !== null) {
            \add_action('swiftype_config_loaded', $callback);
        }

        return (new static());
    }

    /**
     * Constructor.
     * Use loadConfig method instead.
     */
    private function __construct()
    {
        $this->config = new Config();
        \add_action('init', function() {$this->applyUpdates();});
    }

    /**
     * Load the config from admin post data.
     *
     * If you want to be notified when the config is ready you should use the swiftype_config_loaded hook.
     */
    private function applyUpdates()
    {
        if (\is_admin() && \current_user_can('manage_options') && isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'swiftype_set_api_key':
                    $this->config->reset();
                    $this->config->setApiKey($_POST['api_key']);
                    break;
                case 'swiftype_create_engine':
                    $this->config->setLanguage(isset($_POST['language']) ? $_POST['language'] : null);
                    $this->config->setEngineSlug(trim($_POST['engine_name']));
                    break;
                case 'swiftype_clear_config':
                    $this->config->reset();
                    break;
            }
        }

        \do_action('swiftype_config_loaded', $this->config);
    }
}
