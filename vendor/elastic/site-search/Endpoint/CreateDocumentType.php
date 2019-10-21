<?php
/**
 * This file is part of the Elastic Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\SiteSearch\Client\Endpoint;

/**
 * Implementation of the CreateDocumentType endpoint.
 *
 * @package Elastic\SiteSearch\Client\Endpoint
 */
class CreateDocumentType extends \Elastic\OpenApi\Codegen\Endpoint\AbstractEndpoint
{
    // phpcs:disable
    /**
     * @var string
     */
    protected $method = 'POST';

    /**
     * @var string
     */
    protected $uri = '/engines/{engine_name}/document_types.json';

    protected $routeParams = ['engine_name'];

    protected $paramWhitelist = ['document_type.name'];
    // phpcs:enable
}
