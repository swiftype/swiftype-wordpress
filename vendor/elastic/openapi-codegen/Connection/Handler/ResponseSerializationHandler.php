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
 * Automatatic serialization of the request params and body.
 *
 * @package Elastic\OpenApi\Codegen\Connection\Handler
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache2
 */
class ResponseSerializationHandler
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

    /**
     * Unseriallize the response body to an array.
     *
     * @param array $request original request
     *
     * @return array
     */
    public function __invoke($request)
    {
        $handler = $this->handler;
        $response = $this->ringUtils->proxy($handler($request), function ($response) {
            if (true === isset($response['body'])) {
                $response['body'] = stream_get_contents($response['body']);
                $headers = isset($response['transfer_stats']) ? $response['transfer_stats'] : [];
                $response['body'] = $this->serializer->deserialize($response['body'], $headers);
            }

            return $response;
        });

        return $response;
    }
}
