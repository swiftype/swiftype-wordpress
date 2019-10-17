<?php
/**
 * This file is part of the Elastic OpenAPI PHP code generator.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Elastic\OpenApi\Codegen\Exception;

/**
 * Exception raised when the client can not resolve the hostname specified.
 *
 * @package Elastic\OpenApi\Codegen\Exception
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache2
 */
class CouldNotResolveHostException extends ConnectionException implements ClientException
{
}
