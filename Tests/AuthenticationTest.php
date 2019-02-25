<?php

namespace Swiftype\SiteSearch\Wordpress\Tests;

use Swiftype\SiteSearch\Wordpress\Config\Config;
use Swiftype\SiteSearch\Wordpress\Config\Loader as ConfigLoader;

/**
 * Test authication using API key read from config.
 */
class AuthenticationTest extends AbstractTestCase
{
    public function testValidApiKey()
    {
        $client = null;

        $config = new Config();
        $config->setApiKey($this->getTestApiKey());

        \add_action('swiftype_client_loaded', function($loadedClient) use (&$client) {
            var_dump($loadedClient);
            $client = $loadedClient;
        });

        ConfigLoader::loadConfig();

        $this->assertNotNull($client);
    }

    public function testUnsetApiKey()
    {
        $client = null;

        \add_action('swiftype_client_loaded', function($loadedClient) use (&$client) {
            $client = $loadedClient;
        });

        ConfigLoader::loadConfig();

        $this->assertNull($client);
    }

    public function testInvalidApiKey()
    {
        $client = null;

        $config = new Config();
        $config->setApiKey('some-invalid-key');

        \add_action('swiftype_client_loaded', function($loadedClient) use (&$client) {
            $client = $loadedClient;
        });

        ConfigLoader::loadConfig();

        $this->assertNull($client);
    }
}
