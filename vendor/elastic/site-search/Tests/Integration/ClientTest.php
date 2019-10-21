<?php
/**
 * This file is part of the Elastic Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\SiteSearch\Client\Tests\Integration;

use Elastic\SiteSearch\Client\ClientBuilder;
use Elastic\SiteSearch\Client\Client;

/**
 * Testing client instantiaton and error handling.
 *
 * @package Elastic\SiteSearch\Client\Test\Integration
 */
class ClientApiTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test instantiation of a client through the client builder.
     */
    public function testClientBuilder()
    {
        $client = ClientBuilder::create($_ENV['ST_API_KEY'])->build();
        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * Test an Authentication exception is thrown when providing an in valid API Key.
     *
     * @expectedException \Elastic\OpenApi\Codegen\Exception\AuthenticationException
     */
    public function testAuthenticationError()
    {
        $client = ClientBuilder::create('not-a-valid-api-key')->build();
        $client->listEngines();
    }
}
