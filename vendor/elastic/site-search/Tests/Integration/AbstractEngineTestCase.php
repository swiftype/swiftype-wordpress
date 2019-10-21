<?php
/**
 * This file is part of the Elastic Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\SiteSearch\Client\Tests\Integration;

/**
 * A base class for running client tests with a default engine and some sample optional docs.
 *
 * @package Elastic\SiteSearch\Client\Test\Integration
 */
class AbstractEngineTestCase extends AbstractClientTestCase
{
    protected static $defaultDocumentType = 'my-type';

    /**
     * Create the default engine before lauching tests.
     */
    public static function setupBeforeClass()
    {
        parent::setUpBeforeClass();
        self::getDefaultClient()->createEngine(self::getDefaultEngineName());
        self::getDefaultClient()->createDocumentType(self::getDefaultEngineName(), self::getDefaultDocumentType());
    }

    /**
     * Delete the default engine before exiting the class.
     */
    public static function tearDownAfterClass()
    {
        self::getDefaultClient()->deleteEngine(self::getDefaultEngineName());
    }

    protected static function getDefaultDocumentType()
    {
        return static::$defaultDocumentType;
    }
}
