<?php
/**
 * This file is part of the Elastic Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\SiteSearch\Client\Endpoint;

/**
 * Implementation of the GetDocument endpoint.
 *
 * @package Elastic\SiteSearch\Client\Endpoint
 */
class GetDocument extends \Elastic\OpenApi\Codegen\Endpoint\AbstractEndpoint
{
    // phpcs:disable
    /**
     * @var string
     */
    protected $method = 'GET';

    /**
     * @var string
     */
    protected $uri = '/engines/{engine_name}/document_types/{document_type_id}/documents/{external_id}.json';

    protected $routeParams = ['engine_name', 'document_type_id', 'external_id'];
    // phpcs:enable
}
