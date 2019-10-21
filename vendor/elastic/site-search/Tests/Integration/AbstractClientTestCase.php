<?php
/**
 * This file is part of the Elastic Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\SiteSearch\Client\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Elastic\SiteSearch\Client\ClientBuilder;

/**
 * A base class for running client tests.
 *
 * @package Elastic\SiteSearch\Client\Test\Integration
 */
class AbstractClientTestCase extends TestCase
{
    /**
     * @var \Elastic\SiteSearch\Client\Client
     */
    private static $defaultClient;

    /**
     * Init a default client to run all the tests.
     */
    public static function setupBeforeClass()
    {
        self::$defaultClient = ClientBuilder::create($_ENV['ST_API_KEY'])->build();
    }

    /**
     * @return \Elastic\SiteSearch\Client\Client
     */
    protected static function getDefaultClient()
    {
        return self::$defaultClient;
    }

    /**
     * @return string
     */
    protected static function getDefaultEngineName()
    {
        $enginePrefix = isset($_ENV['ST_ENGINE_NAME']) ? $_ENV['ST_ENGINE_NAME'] : 'php-integration-test';
        $className = explode('\\', get_called_class());
        $engineSuffix = strtolower(end($className));

        return  sprintf('%s-%s', $enginePrefix, $engineSuffix);
    }
}
