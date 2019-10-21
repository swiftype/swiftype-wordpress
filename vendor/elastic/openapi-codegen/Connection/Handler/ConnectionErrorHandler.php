<?php
/**
 * This file is part of the Elastic OpenAPI PHP code generator.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\OpenApi\Codegen\Connection\Handler;

use Elastic\OpenApi\Codegen\Exception\ConnectionException;
use Elastic\OpenApi\Codegen\Exception\CouldNotConnectToHostException;
use Elastic\OpenApi\Codegen\Exception\CouldNotResolveHostException;
use Elastic\OpenApi\Codegen\Exception\OperationTimeoutException;
use GuzzleHttp\Ring\Core as GuzzleCore;

/**
 * This handler manage connections errors and throw comprehensive exceptions to the user.
 *
 * @package Elastic\OpenApi\Codegen\Connection\Handler
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache2
 */
class ConnectionErrorHandler
{
    /**
     * @var callable
     */
    private $handler;


    /**
     * @var GuzzleCore
     */
    private $ringUtils;

    /**
     * Constructor.
     *
     * @param callable $handler original handler
     */
    public function __construct(callable $handler)
    {
        $this->handler   = $handler;
        $this->ringUtils = new GuzzleCore();
    }

    /**
     * Proxy the response and throw an exception if a connection error is detected.
     *
     * @param array $request request
     *
     * @return array
     */
    public function __invoke($request)
    {
        $handler = $this->handler;
        $response = $this->ringUtils->proxy($handler($request), function ($response) {
            if (true === isset($response['error'])) {
                throw $this->getConnectionErrorException($response);
            }

            return $response;
        });

        return $response;
    }

    /**
     * Process error to raised a more comprehensive exception.
     *
     * @param array $request  request
     * @param array $response response
     *
     * @return ConnectionException
     */
    private function getConnectionErrorException($response)
    {
        $exception = null;
        $message = $response['error']->getMessage();

        $exception = new ConnectionException($message);

        if (isset($response['curl']['errno'])) {
            switch ($response['curl']['errno']) {
                case CURLE_COULDNT_RESOLVE_HOST:
                    $exception = new CouldNotResolveHostException($message, null, $response['error']);
                    break;
                case CURLE_COULDNT_CONNECT:
                    $exception = new CouldNotConnectToHostException($message, null, $response['error']);
                    break;
                case CURLE_OPERATION_TIMEOUTED:
                    $exception = new OperationTimeoutException($message, null, $response['error']);
                    break;
            }
        }

        return $exception;
    }
}
