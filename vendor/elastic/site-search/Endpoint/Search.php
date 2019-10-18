<?php
/**
 * This file is part of the Elastic Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\SiteSearch\Client\Endpoint;

/**
 * Implementation of the Search endpoint.
 *
 * @package Elastic\SiteSearch\Client\Endpoint
 */
class Search extends \Elastic\OpenApi\Codegen\Endpoint\AbstractEndpoint
{
    // phpcs:disable
    /**
     * @var string
     */
    protected $method = 'POST';

    /**
     * @var string
     */
    protected $uri = '/engines/{engine_name}/search.json';

    protected $routeParams = ['engine_name'];

    protected $paramWhitelist = ['q'];
    // phpcs:enable
}
