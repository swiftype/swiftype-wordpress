<?php
/**
 * This file is part of the Swiftype PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swiftype\Endpoint;

/**
 * Endpoint builder implementation.
 *
 * @package Swiftype\Endpoint
 *
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 */
class Builder
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * Constructor.
     *
     * @param string $namespace
     */
    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Create an endpoint from name.
     */
    public function __invoke($endpointName)
    {
        $className = sprintf('%s\\%s', $this->namespace, $endpointName);

        return new $className();
    }
}
