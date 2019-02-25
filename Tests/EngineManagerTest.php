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
     *
     * @testWith [null]
     *           ["en"]
     */
    public function testEngineInstalled($language = null)
    {
        if ($language != null) {
            $config = new Config();
            $config->setLanguage($language);
        }

        $this->loadDefaultConfig();

        $engine = $this->client->getEngine($this->getTestEngineName());

        $this->assertEquals($this->getTestEngineName(), $engine['name']);
        $this->assertEquals($language, $engine['language']);
    }
}
