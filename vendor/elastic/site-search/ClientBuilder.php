<?php
/**
 * This file is part of the Elastic Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\SiteSearch\Client;

/**
 * Use this class to instantiate new client and all their dependencies.
 *
 * @package Elastic\SiteSearch\Client
 */
class ClientBuilder extends \Elastic\OpenApi\Codegen\AbstractClientBuilder
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
     * @var string
     */
    private $integration;

    /**
     * Instantiate a new client builder.
     *
     * @param string $hostIdentifier
     * @param string $apiKey
     *
     * @return \Elastic\SiteSearch\Client\ClientBuilder
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
     * Set integration name & version for the client.
     *
     * @param string $integration
     *
     * @return ClientBuilder
     */
    public function setIntegration($integration)
    {
        $this->integration = $integration;

        return $this;
    }

    /**
     * Return the configured Site Search client.
     *
     * @return \Elastic\SiteSearch\Client\Client
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
        $handler = new Connection\Handler\RequestClientHeaderHandler($handler, $this->integration);
        $handler = new \Elastic\OpenApi\Codegen\Connection\Handler\RequestUrlPrefixHandler($handler, self::URI_PREFIX);
        $handler = new Connection\Handler\ApiErrorHandler($handler);

        return $handler;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEndpointBuilder()
    {
        return new \Elastic\OpenApi\Codegen\Endpoint\Builder(__NAMESPACE__ . "\Endpoint");
    }
}
