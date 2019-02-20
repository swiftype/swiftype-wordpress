<?php
/**
 * This file is part of the Swiftype PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swiftype;

use Swiftype\Connection\Connection;

/**
 * A base client implementation implemented by the generator.
 *
 * @package Swiftype
 *
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 */
abstract class AbstractClient
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var callable
     */
    private $endpointBuilder;

    /**
     * Client constructor.
     *
     * @param callable   $endpointBuilder Allow to access endpoints.
     * @param Connection $connection      HTTP connection handler.
     */
    public function __construct(callable $endpointBuilder, Connection $connection)
    {
        $this->endpointBuilder = $endpointBuilder;
        $this->connection = $connection;
    }

    protected function getEndpoint($name)
    {
        $endpointBuilder = $this->endpointBuilder;
        return $endpointBuilder($name);
    }

    protected function performRequest(Endpoint\EndpointInterface $endpoint)
    {
        $method = $endpoint->getMethod();
        $uri = $endpoint->getURI();
        $params = $endpoint->getParams();
        $body = $endpoint->getBody();

        $response = $this->connection->performRequest($method, $uri, $params, $body)->wait();

        return isset($response['body']) ? $response['body'] : $response;
    }
}
