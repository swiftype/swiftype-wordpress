<?php

namespace Swiftype\SiteSearch\Wordpress\Tests;

use Swiftype\SiteSearch\Wordpress\Config\Config;

/**
 * Test authentication using API key read from config.
 */
class EngineManagerTest extends AbstractTestCase
{
    private $indexingFilters = [
        'future_to_publish', 'save_post', 'transition_post_status', 'trashed_post',
        'swiftype_batch_post_index', 'swiftype_batch_post_delete',
    ];

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

    public function testIndexingHooksNotInstalled()
    {
        foreach($this->indexingFilters as $filter) {
            $this->assertFalse(has_filter($filter));
        }
    }

    public function testIndexingHooksInstalled()
    {
        foreach($this->indexingFilters as $filter) {
            remove_all_actions($filter);
        }

        $this->loadDefaultConfig();

        foreach($this->indexingFilters as $filter) {
            $this->assertTrue(has_filter($filter));
        }

    }
}
