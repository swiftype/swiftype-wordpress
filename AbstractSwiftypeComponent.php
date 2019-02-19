<?php

namespace Swiftype\SiteSearch\Wordpress;

use Swiftype\SiteSearch\Client;
use Swiftype\SiteSearch\Wordpress\Config\Config;

class AbstractSwiftypeComponent
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Client
     */
    private $client;

    public function __construct()
    {
        \add_action('swiftype_config_loaded', [$this, 'loadConfig']);
        \add_action('swiftype_client_loaded', [$this, 'loadClient']);
    }

    public final function loadConfig(Config $config)
    {
        $this->config = $config;
    }

    public final function loadClient(Client $client)
    {
        $this->client = $client;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getClient()
    {
        return $this->client;
    }
}
