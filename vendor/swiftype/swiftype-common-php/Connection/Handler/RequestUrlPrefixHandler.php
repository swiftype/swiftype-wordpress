<?php
/**
 * This file is part of the Swiftype PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swiftype\Connection\Handler;

/**
 * This handler add automatically all URIs data to the request.
 *
 * @package Swiftype\Connection\Handler
 *
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 */
class RequestUrlPrefixHandler
{
    /**
     * @var string
     */
    private $uriPrefix;

    /**
     * @var callable
     */
    private $handler;

    /**
     * @var \GuzzleHttp\Ring\Core
     */
    private $ringUtils;

    /**
     * Constructor.
     *
     * @param callable $handler   Original handler.
     * @param string   $uriPrefix A prefix to be added to all URIs.
     */
    public function __construct(callable $handler, $uriPrefix)
    {
        $this->handler   = $handler;
        $this->uriPrefix = $uriPrefix;
        $this->ringUtils = new \GuzzleHttp\Ring\Core();
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
        $request['uri'] = $this->addURIPrefix($request['uri']);

        return $handler($request);
    }

    /**
     * Add prefix for the URI.
     *
     * @param string $uri
     *
     * @return string
     */
    private function addURIPrefix($uri)
    {
        return sprintf('%s%s', '/' == substr($uri, 0, 1) ? rtrim($this->uriPrefix, '/') : $this->uriPrefix, $uri);
    }
}
