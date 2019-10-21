<?php
/**
 * This file is part of the Elastic OpenAPI PHP code generator.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\OpenApi\Codegen\Tests\Unit\Connection\Handler;

use PHPUnit\Framework\TestCase;
use Elastic\OpenApi\Codegen\Connection\Handler\RequestUrlPrefixHandler;

/**
 * Unit tests for the request url handler.
 *
 * @package Elastic\OpenApi\Codegen\Test\Unit\Connection\Handler
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache2
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
