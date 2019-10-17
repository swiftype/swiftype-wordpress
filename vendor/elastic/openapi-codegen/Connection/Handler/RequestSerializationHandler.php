<?php
/**
 * This file is part of the Elastic OpenAPI PHP code generator.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\OpenApi\Codegen\Connection\Handler;

use Elastic\OpenApi\Codegen\Serializer\SerializerInterface;
use GuzzleHttp\Ring\Core as GuzzleCore;

/**
 * Automatatic unserialization of the response.
 *
 * @package Elastic\OpenApi\Codegen\Connection\Handler
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache2
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
     * @var GuzzleCore
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
        $this->ringUtils = new GuzzleCore();
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
