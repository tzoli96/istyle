<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */

namespace Oander\IstyleCustomization\Preference\Amasty\Base\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Unserialize\Unserialize;

/**
 * Wrapper for Serialize
 * @since 1.1.0
 */
class Serializer extends \Amasty\Base\Model\Serializer
{
    /**
     * @var null|SerializerInterface
     */
    private $serializer;

    /**
     * @var Unserialize
     */
    private $unserialize;

    public function __construct(
        ObjectManagerInterface $objectManager,
        Unserialize $unserialize
    ) {
        if (interface_exists(SerializerInterface::class)) {
            // for magento later then 2.2
            $this->serializer = $objectManager->get(SerializerInterface::class);
        }
        $this->unserialize = $unserialize;
    }

    public function serialize($value)
    {
        try {
            if ($this->serializer === null) {
                return serialize($value);
            }

            return $this->serializer->serialize($value);
        } catch (\Exception $e) {
            return '{}';
        }
    }

    public function unserialize($value)
    {
        if (false === $value || null === $value || '' === $value) {
            return false;
        }

        if ($this->serializer === null) {
            return $this->unserialize->unserialize($value);
        }

        try {
            return $this->serializer->unserialize($value);
        } catch (\InvalidArgumentException $exception) {
            return unserialize($value);
        }
    }
}