<?php

namespace Swiftype\SiteSearch\Wordpress\Tests;

use Swiftype\SiteSearch\Wordpress\Config\Config;

/**
 * Test authentication using API key read from config.
 */
class EngineManagerTest extends AbstractTestCase
{
    /**
     * Test an engine is created when the engine is fully configured.
     */
    public function testEngineInstalled($language = null)
    {
        $config = new Config();
        $config->setApiKey($this->getTestApiKey());
        $config->setEngineSlug($this->getTestEngineName());

        if ($language != null) {
            $config->setLanguage($language);
        }

        \do_action('swiftype_config_loaded', $config);

        $engine = $this->client->getEngine($this->getTestEngineName());

        $this->assertEquals($this->getTestEngineName(), $engine['name']);
        $this->assertEquals($language, $engine['language']);
    }
}
