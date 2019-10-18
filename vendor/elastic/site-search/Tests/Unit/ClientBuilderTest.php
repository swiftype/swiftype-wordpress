<?php
/**
 * This file is part of the Elastic Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\SiteSearch\Client\Tests\Unit\Connection\Handler;

use PHPUnit\Framework\TestCase;
use Elastic\SiteSearch\Client\Client;
use Elastic\SiteSearch\Client\ClientBuilder;

/**
 * Check the client builder is able to instantiate new clients.
 *
 * @package Elastic\SiteSearch\Client\Test\Unit
 */
class ClientBuilderTest extends TestCase
{
    /**
     * Check client instantiation.
     *
     * @param string $apiEndpoint
     */
    public function testInstantiation()
    {
        $client = ClientBuilder::create('apiKey')->build();
        $this->assertInstanceOf(Client::class, $client);
    }
}
