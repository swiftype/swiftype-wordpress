<?php

namespace Swiftype\SiteSearch\Wordpress\Tests;

use Swiftype\SiteSearch\Wordpress\Config\Config;
use Swiftype\SiteSearch\Wordpress\SwiftypePlugin;

/**
 * Test authentication using API key read from config.
 */
class AuthenticationTest extends AbstractTestCase
{
    /**
     * Test configuraton loading hooks are registred.
     */
    public function testConfigLoadingtHooks()
    {
        $this->assertHasFilter('swiftype_config_loaded', [SwiftypePlugin::class, 'initClient']);
    }

    /**
     * Test a valid client is instantiated when a valid API Key is configured.
     */
    public function testValidApiKey()
    {
        $config = new Config();
        $config->setApiKey($this->getTestApiKey());

        \do_action('swiftype_config_loaded', $config);

        $this->assertNotNull($this->client);
    }

    /**
     * Test no client is instantiated when API key is not configured.
     */
    public function testUnsetApiKey()
    {
        \do_action('swiftype_config_loaded', new Config());

        $this->assertNull($this->client);
    }

    /**
     * Test no client is instantiated when an invalid API key is configured.
     */
    public function testInvalidApiKey()
    {
        $config = new Config();
        $config->setApiKey('some-invalid-key');

        \do_action('swiftype_config_loaded', $config);

        $this->assertNull($this->client);
    }
}
