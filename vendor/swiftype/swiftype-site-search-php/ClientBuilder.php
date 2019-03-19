<?php
/**
 * This file is part of the Swiftype Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swiftype\SiteSearch;

/**
 * Use this class to instantiate new client and all their dependencies.
 *
 * @package Swiftype\Site
 *
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 */
class ClientBuilder extends \Swiftype\AbstractClientBuilder
{
    /**
     * @var string
     */
    const URI_PREFIX = '/api/v1/';

    /**
     * @var string
     */
    const API_ENDPOINT = 'https://api.swiftype.com';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * Instantiate a new client builder.
     *
     * @param string $hostIdentifier
     * @param string $apiKey
     *
     * @return \Swiftype\SiteSearch\ClientBuilder
     */
    public static function create($apiKey = null)
    {
        return (new static())->setHost(self::API_ENDPOINT)->setApiKey($apiKey);
    }

    /**
     * Set the api key for the client.
     *
     * @param string $apiKey
     *
     * @return ClientBuilder
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Return the configured Swiftype client.
     *
     * @return \Swiftype\SiteSearch\Client
     */
    public function build()
    {
        return new Client($this->getEndpointBuilder(), $this->getConnection());
    }

    /**
     * {@inheritdoc}
     */
    protected function getHandler()
    {
        $handler = parent::getHandler();
        $handler = new Connection\Handler\RequestAuthenticationHandler($handler, $this->apiKey);
        $handler = new \Swiftype\Connection\Handler\RequestUrlPrefixHandler($handler, self::URI_PREFIX);
        $handler = new Connection\Handler\ApiErrorHandler($handler);

        return $handler;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEndpointBuilder()
    {
        return new \Swiftype\Endpoint\Builder(__NAMESPACE__ . "\Endpoint");
    }
}
