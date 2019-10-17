<?php
/**
 * This file is part of the Elastic OpenAPI PHP code generator.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\OpenApi\Codegen;

use Elastic\OpenApi\Codegen\Connection\Connection;

/**
 * A base client implementation implemented by the generator.
 *
 * @package Elastic\OpenApi\Codegen
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache2
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

        $response = $this->connection->performRequest($method, $uri, $params, $body);

        return isset($response['body']) ? $response['body'] : $response;
    }
}
