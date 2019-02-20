<?php
/**
 * This file is part of the Swiftype Common PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swiftype\Connection\Handler;

use Swiftype\Serializer\SerializerInterface;

/**
 * Automatatic unserialization of the response.
 *
 * @package Swiftype\Connection\Handler
 *
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 */
class RequestSerializationHandler
{
    /**
     * @var callable
     */
    private $handler;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var \GuzzleHttp\Ring\Core
     */
    private $ringUtils;

    /**
     * Constructor.
     *
     * @param callable            $handler    original handler
     * @param SerializerInterface $serializer serialize
     */
    public function __construct(callable $handler, SerializerInterface $serializer)
    {
        $this->handler = $handler;
        $this->serializer = $serializer;
        $this->ringUtils = new \GuzzleHttp\Ring\Core();
    }

    public function __invoke($request)
    {
        $handler = $this->handler;
        $request = $this->ringUtils->setHeader($request, 'Content-Type', ['application/json']);

        $body = isset($request['body']) ? $request['body'] : [];

        if (isset($request['query_params'])) {
            $body = array_merge($body, $request['query_params']);
            unset($request['query_params']);
        }

        if (!empty($body)) {
            ksort($body);
            $request['body'] = $this->serializer->serialize($body);
        }

        return $handler($request);
    }
}
