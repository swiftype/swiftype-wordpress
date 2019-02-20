<?php
/**
 * This file is part of the Swiftype Common PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swiftype\Connection;

use Psr\Log\LoggerInterface;

/**
 * Connection bring HTTP connectivity to the Swiftype HTTP API.
 *
 * @package Swiftype\Connection
 *
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 */
class Connection
{
    /**
     * @var callable
     */
    private $handler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LoggerInterface
     */
    private $tracer;

    /**
     * Constructor.
     *
     * @param callable        $handler guzzle handler used to issue request
     * @param LoggerInterface $logger  logger used for warning & error
     * @param LoggerInterface $tracer  logger used for tracing
     */
    public function __construct(callable $handler, LoggerInterface $logger, LoggerInterface $tracer)
    {
        $this->handler = $handler;
        $this->logger = $logger;
        $this->tracer = $tracer;
    }

    /**
     * Run the HTTP request and process the result to be usable by the client.
     *
     * @param string     $method HTTP method (eg. GET, POST, ...).
     * @param string     $uri    URI of the request
     * @param array|null $params query params
     * @param array|null $body   request body
     *
     * @return array
     */
    public function performRequest($method, $uri, $params = null, $body = null)
    {
        $handler = $this->handler;

        $request = [
            'http_method' => $method,
            'uri' => $uri,
            'body' => $body,
            'query_params' => $params,
        ];

        return $handler(array_filter($request));
    }
}
