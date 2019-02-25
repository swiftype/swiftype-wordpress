<?php

namespace Swiftype\SiteSearch\Wordpress\Tests;

use Swiftype\SiteSearch\Wordpress\Config\Config;

class AbstractTestCase extends \WP_UnitTestCase
{
    public function getValidApiKey()
    {
        return getenv('ST_API_KEY');
    }

    public function resetSettings()
    {
        $config = new Config();
        $config->resetSettings();
    }
}
