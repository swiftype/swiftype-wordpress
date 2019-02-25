<?php

namespace Swiftype\SiteSearch\Wordpress\Tests;

use Swiftype\SiteSearch\Wordpress\Config\Config;

class AbstractTestCase extends \WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->resetConfig();
    }

    public function getTestApiKey()
    {
        return getenv('ST_API_KEY');
    }

    public function getTestEngineName()
    {
        return getenv('ST_ENGINE_NAME');
    }

    public function resetConfig()
    {
        $config = new Config();
        $config->reset();
    }
}
