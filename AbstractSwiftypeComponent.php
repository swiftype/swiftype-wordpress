<?php

namespace Swiftype\SiteSearch\Wordpress;

use Swiftype\SiteSearch\Client;
use Swiftype\SiteSearch\Wordpress\Config\Config;

/**
 * A base class for all components used by the Swiftype Site Search plugin.
 *
 * @author Matt Riley <mriley@swiftype.com>, Quin Hoxie <qhoxie@swiftype.com>, Aurelien Foucret <aurelien.foucret@elastic.co>
 */
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

    /**
     * Constructor.
     */
    public function __construct()
    {
        \add_action('swiftype_config_loaded', [$this, 'loadConfig']);
        \add_action('swiftype_client_loaded', [$this, 'loadClient']);
    }

    /**
     * Install the config.
     *
     * @param Config $config
     */
    public final function loadConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Install the client.
     *
     * @param Client $client
     */
    public final function loadClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get the config.
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get the client.
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
