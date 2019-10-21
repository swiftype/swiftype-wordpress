<?php
/**
 * This file is part of the Elastic OpenAPI PHP code generator.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\OpenApi\Codegen\Tests\Unit\Connection\Handler;

use PHPUnit\Framework\TestCase;
use Elastic\OpenApi\Codegen\Connection\Handler\RequestHostHandler;

/**
 * Unit tests for the request url handler.
 *
 * @package Elastic\OpenApi\Codegen\Test\Unit\Connection\Handler
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache2
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
