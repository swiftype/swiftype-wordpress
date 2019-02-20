<?php
/**
 * This file is part of the Swiftype Common PHP Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swiftype\Serializer;

use Swiftype\Exception\JsonErrorException;

/**
 * Default serializer used by the client.
 *
 * @package Swiftype\Serializer
 *
 * @author  Aurélien FOUCRET <aurelien.foucret@elastic.co>
 */
class SmartSerializer implements SerializerInterface
{
    /**
     * @var string
     */
    private $phpVersion;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->phpVersion = phpversion();
    }

    /**
     * Serialize assoc array into JSON string.
     *
     * @param string|array $data Assoc array to encode into JSON
     *
     * @return string
     */
    public function serialize($data)
    {
        if (true === is_string($data)) {
            return $data;
        }

        $this->prepareData($data);

        $flags = JSON_PRESERVE_ZERO_FRACTION;

        if (version_compare($this->phpVersion, '5.6.6', '<') || !defined('JSON_PRESERVE_ZERO_FRACTION')) {
            $flags = null;
        }

        $data = json_encode($data, $flags);
      
        if ('[]' === $data) {
            return '{}';
        }

        return $data;
    }

    /**
     * Deserialize by introspecting content_type. Tries to deserialize JSON,
     * otherwise returns string.
     *
     * @throws JsonErrorException
     *
     * @param string $data    JSON encoded string
     * @param array  $headers Response Headers
     *
     * @return array
     */
    public function deserialize($data, $headers)
    {
        if (true === isset($headers['content_type']) && false === strpos($headers['content_type'], 'json')) {
            return $data;
        }

        return $this->decode($data);
    }

    /**
     * Prepare data for serialization :
     * - Convert all empty arrays in stdClass, so we can get an object.
     *
     * @param array $data
     */
    private function prepareData(&$data)
    {
        if (is_array($data) && empty($data)) {
            $data = new \stdClass();
        } elseif (is_array($data)) {
            array_walk($data, [$this, __METHOD__]);
        }
    }

    /**
     * @todo For 2.0, remove the E_NOTICE check before raising the exception.
     *
     * @throws JsonErrorException
     *
     * @param $data
     *
     * @return array
     */
    private function decode($data)
    {
        if (null === $data || 0 === strlen($data)) {
            return '';
        }

        $result = @json_decode($data, true);

        if (JSON_ERROR_NONE !== json_last_error() && E_NOTICE === (error_reporting() & E_NOTICE)) {
            throw new JsonErrorException(json_last_error(), $data, $result);
        }

        return $result;
    }
}
