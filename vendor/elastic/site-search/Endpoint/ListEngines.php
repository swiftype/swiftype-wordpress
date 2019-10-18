<?php
/**
 * This file is part of the Elastic Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\SiteSearch\Client\Endpoint;

/**
 * Implementation of the ListEngines endpoint.
 *
 * @package Elastic\SiteSearch\Client\Endpoint
 */
class ListEngines extends \Elastic\OpenApi\Codegen\Endpoint\AbstractEndpoint
{
    // phpcs:disable
    /**
     * @var string
     */
    protected $method = 'GET';

    /**
     * @var string
     */
    protected $uri = '/engines.json';

    protected $paramWhitelist = ['page', 'per_page'];
    // phpcs:enable
}
