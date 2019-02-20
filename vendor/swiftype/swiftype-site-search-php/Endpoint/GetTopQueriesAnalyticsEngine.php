<?php
/**
 * This file is part of the Swiftype PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swiftype\SiteSearch\Endpoint;

/**
 * Implementation of the  endpoint.
 *
 * @package Swiftype\SiteSearch\Endpoint
 */
class GetTopQueriesAnalyticsEngine extends \Swiftype\Endpoint\AbstractEndpoint
{
    // phpcs:disable
    /**
     * @var string
     */
    protected $method = 'POST';

    /**
     * @var string
     */
    protected $uri = '/engines/{engine_name}/analytics/top_queries.json';

    protected $routeParams = ['engine_name'];

    protected $paramWhitelist = ['start_date', 'end_date', 'page', 'per_page'];
    // phpcs:enable
}
