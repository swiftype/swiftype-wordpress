<?php

namespace Swiftype\SiteSearch\Wordpress\Tests;

use Swiftype\SiteSearch\Wordpress\Config\Config;

/**
 * Test authentication using API key read from config.
 */
class AuthenticationTest extends AbstractTestCase
{
    public function testValidApiKey()
    {
        $client = null;

        $config = new Config();
        $config->setApiKey($this->getTestApiKey());

        \add_action('swiftype_client_loaded', function($loadedClient) use (&$client) {
            $client = $loadedClient;
        });

        \do_action('swiftype_config_loaded', $config);

        $this->assertNotNull($client);
    }

    public function testUnsetApiKey()
    {
        $client = null;

        \add_action('swiftype_client_loaded', function($loadedClient) use (&$client) {
            $client = $loadedClient;
        });

        \do_action('swiftype_config_loaded', new Config());

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

        \do_action('swiftype_config_loaded', $config);

        $this->assertNull($client);
    }
}
