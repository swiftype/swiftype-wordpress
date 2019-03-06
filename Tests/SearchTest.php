<?php

namespace Swiftype\SiteSearch\Wordpress\Tests;

use Swiftype\SiteSearch\Wordpress\Config\Config;

/**
 * Test search hooks setup.
 */
class SearchTest extends AbstractTestCase
{
    private $hooks = [
        'pre_get_posts', // All other hooks are installed by the this one.
        'wp_enqueue_scripts', // Front assets (not enabled if the engine is not configured
    ];

    /**
     * Test search hooks are not installed if the engine is not configured.
     */
    public function testIndexingHooksNotInstalled()
    {
        foreach($this->hooks as $filter) {
            \remove_all_actions($filter);
        }

        \do_action('swiftype_config_loaded', new Config());

        foreach($this->hooks as $filter) {
            $this->assertFalse(has_filter($filter));
        }
    }

    /**
     * Test search hooks are not installed if the engine configuration is OK.
     */
    public function testIndexingHooksInstalled()
    {
        foreach($this->hooks as $filter) {
            \remove_all_actions($filter);
        }

        $this->loadDefaultConfig();

        foreach($this->hooks as $filter) {
            $this->assertTrue(has_filter($filter));
        }
    }
}
