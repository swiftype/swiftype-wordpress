<?php
/**
 * This file is part of the Elastic OpenAPI PHP code generator.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\OpenApi\Codegen\Connection\Handler;

use GuzzleHttp\Ring\Core as GuzzleCore;

/**
 * This handler add automatically all URIs data to the request.
 *
 * @package Elastic\OpenApi\Codegen\Connection\Handler
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache2
 */
class RequestHostHandler
{
    /**
     * @var callable
     */
    private $handler;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $scheme;

    /**
     * @var GuzzleCore
     */
    private $ringUtils;

    /**
     * Constructor.
     *
     * @param callable $handler Original handler
     * @param string   $host    API host (eg. http://myserver/).
     */
    public function __construct(callable $handler, $apiEndpoint)
    {
        $this->handler = $handler;

        $urlComponents = parse_url($apiEndpoint);

        $this->scheme = $urlComponents['scheme'];
        $this->host = $urlComponents['host'];

        if (isset($urlComponents['port'])) {
            $this->host = sprintf('%s:%s', $this->host, $urlComponents['port']);
        }

        $this->ringUtils = new GuzzleCore();
    }

    /**
     * Add host, scheme and uri prefix to the request before calling the original handler.
     *
     * @param array $request original request
     *
     * @return array
     */
    public function __invoke($request)
    {
        $handler = $this->handler;
        $request = $this->ringUtils->setHeader($request, 'host', [$this->host]);
        $request['scheme'] = $this->scheme;

        return $handler($request);
    }
}
