<?php
/**
 * This file is part of the Swiftype Common PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swiftype\Tests\Unit\Connection\Handler;

use PHPUnit\Framework\TestCase;
use Swiftype\Connection\Handler\RequestUrlPrefixHandler;

/**
 * Unit tests for the request url handler.
 *
 * @package Swiftype\Test\Unit\Connection\Handler
 *
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 */
class RequestUrlPrefixHandlerTest extends TestCase
{
    /**
     * Check the data are correct for host and scheme in the request.
     * Additionally check if the api prefix is append to the URI.
     *
     * @testWith ["/foo", "/api/v1/foo"]
     *           ["/", "/api/v1/"]
     */
    public function testUrlData($baseUri, $expectedUri)
    {
        $handler = function ($request) {
            return $request;
        };

        $urlHandler = new RequestUrlPrefixHandler($handler, '/api/v1/');
        $request = $urlHandler(['uri' => $baseUri]);

        $this->assertEquals($expectedUri, $request['uri']);
    }
}
