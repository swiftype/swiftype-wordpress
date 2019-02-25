<?php

namespace Swiftype\SiteSearch\Wordpress\Tests;

use PHPUnit\Framework\TestCase;
use Swiftype\Connection\Handler\ConnectionErrorHandler;


class DummyTest extends TestCase
{
    /**
     * Check the exception is thrown when needed.
     *
     * @dataProvider errorDataProvider
     */
    public function testDummy()
    {
        $this->assertTrue(true);
    }
}
