<?php
/**
 * This file is part of the Swiftype Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swiftype\SiteSearch\Tests\Unit\Connection\Handler;

use PHPUnit\Framework\TestCase;
use Swiftype\SiteSearch\Client;
use Swiftype\SiteSearch\ClientBuilder;

/**
 * Check the client builder is able to instantiate new clients.
 *
 * @package Swiftype\SiteSearch\Test\Unit
 *
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
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
