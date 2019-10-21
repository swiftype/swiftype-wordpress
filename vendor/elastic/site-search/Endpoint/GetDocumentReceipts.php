<?php
/**
 * This file is part of the Elastic Site Search PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\SiteSearch\Client\Endpoint;

/**
 * Implementation of the GetDocumentReceipts endpoint.
 *
 * @package Elastic\SiteSearch\Client\Endpoint
 */
class GetDocumentReceipts extends \Elastic\OpenApi\Codegen\Endpoint\AbstractEndpoint
{
    // phpcs:disable
    /**
     * @var string
     */
    protected $method = 'POST';

    /**
     * @var string
     */
    protected $uri = '/document_receipts.json';

    protected $paramWhitelist = ['ids'];
    // phpcs:enable
}
