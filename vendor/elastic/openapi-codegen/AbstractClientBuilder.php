<?php
/**
 * This file is part of the Elastic OpenAPI PHP code generator.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\OpenApi\Codegen;

use Elastic\OpenApi\Codegen\Serializer\SerializerInterface;
use Elastic\OpenApi\Codegen\Serializer\SmartSerializer;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use GuzzleHttp\Ring\Client\CurlHandler;
use Elastic\OpenApi\Codegen\Connection\Connection;
use Elastic\OpenApi\Codegen\Connection\Handler\RequestSerializationHandler;
use Elastic\OpenApi\Codegen\Connection\Handler\RequestHostHandler;
use Elastic\OpenApi\Codegen\Connection\Handler\ConnectionErrorHandler;
use Elastic\OpenApi\Codegen\Connection\Handler\ResponseSerializationHandler;
use Elastic\OpenApi\Codegen\Endpoint\Builder as EndpointBuilder;

/**
 * A base client builder implementation.
 *
 * @package Elastic\OpenApi\Codegen
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache2
 */
abstract class AbstractClientBuilder
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $tracer;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $host;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->serializer = new SmartSerializer();
        $this->logger = new NullLogger();
        $this->tracer = new NullLogger();
    }

    /**
     * @return SerializerInterface
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return LoggerInterface
     */
    public function getTracer()
    {
        return $this->tracer;
    }

    /**
     * @param SerializerInterface $serializer
     *
     * @return $this
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param LoggerInterface $tracer
     *
     * @return $this
     */
    public function setTracer(LoggerInterface $tracer)
    {
        $this->tracer = $tracer;

        return $this;
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Return the configured clien.
     */
    abstract public function build();

    /**
     * @return callable
     */
    protected function getHandler()
    {
        $handler = new CurlHandler();

        $handler = new RequestSerializationHandler($handler, $this->serializer);
        $handler = new RequestHostHandler($handler, $this->host);
        $handler = new ConnectionErrorHandler($handler);
        $handler = new ResponseSerializationHandler($handler, $this->serializer);

        return $handler;
    }

    /**
     * @return Connection
     */
    protected function getConnection()
    {
        return new Connection($this->getHandler(), $this->getLogger(), $this->getTracer());
    }

    /**
     * @return EndpointBuilder
     */
    abstract protected function getEndpointBuilder();
}
