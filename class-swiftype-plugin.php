<?php

use Swiftype\SiteSearch\Wordpress\Document\Indexer;
use Swiftype\SiteSearch\Wordpress\Config\Config;
use Swiftype\SiteSearch\Wordpress\Search\PostSearch;

/**
 * The Swiftype Search Wordpress Plugin
 *
 * This class encapsulates all of the Swiftype Search plugin's functionality.
 *
 * @author Matt Riley <mriley@swiftype.com>, Quin Hoxie <qhoxie@swiftype.com>
 */
class SwiftypePlugin
{
    public function __construct()
    {
        add_action('init', [$this, 'init']);
    }

    public function init()
    {
        $config = new Config();
        $client = $this->initClient($config);

        if ($client !== null) {
            new Indexer($client, $config);
            new PostSearch($client, $config);
        }

        new \Swiftype\SiteSearch\Wordpress\Admin\Page($client, $config);
    }

    private function initClient(Config $config)
    {
        $client = null;
        $apiKey = $config->getApiKey();

        if ($apiKey && strlen($apiKey) > 0) {
            $client = \Swiftype\SiteSearch\ClientBuilder::create($apiKey)->build();
            try {
                $client->listEngines();
            } catch (\Swiftype\Exception\SwiftypeException $e) {
                $client = null;
            }
        }

        return $client;
    }
}
