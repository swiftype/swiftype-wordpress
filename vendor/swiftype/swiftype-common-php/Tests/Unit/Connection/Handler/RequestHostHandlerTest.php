<?php
/**
 * This file is part of the Swiftype Common PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swiftype\Tests\Unit\Connection\Handler;

use PHPUnit\Framework\TestCase;
use Swiftype\Connection\Handler\RequestHostHandler;

/**
 * Unit tests for the request url handler.
 *
 * @package Swiftype\Test\Unit\Connection\Handler
 *
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 */
class RequestHostHandlerTest extends TestCase
{
    /**
     * Check the data are correct for host and scheme in the request.
     * Additionally check if the api prefix is append to the URI.
     *
     * @testWith ["http://test.com", "test.com", "http"]
     *           ["https://test.com", "test.com", "https"]
     *           ["http://test", "test", "http"]
     *           ["https://test", "test", "https"]
     *           ["http://localhost:3200/foo", "localhost:3200", "http"]
     *           ["https://localhost:3200", "localhost:3200", "https"]
     */
    public function testUrlData($apiEndpoint, $expectedHost, $expectedScheme)
    {
        $handler = function ($request) {
            return $request;
        };

        $urlHandler = new RequestHostHandler($handler, $apiEndpoint);
        $request = $urlHandler(["foo" => "bar"]);

        $this->assertEquals([$expectedHost], $request['headers']['host']);
        $this->assertEquals($expectedScheme, $request['scheme']);
    }
}
