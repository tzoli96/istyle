<?php

namespace Oander\IstyleCustomization\Plugin\Magento\Eav\Model\Entity;

use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;

/**
 * Class AttributeLoader
 * @package Oander\IstyleCustomization\Plugin\Magento\Eav\Model
 */
class AttributeLoader
{
    const ATTRIBUTES_CACHE_SUFFIX = 'attributes_cache_suffix';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * AttributeLoader constructor.
     *
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * Retrieve configuration for all attributes
     *
     * @param \Magento\Eav\Model\Entity\AttributeLoader $subject
     * @param callable $proceed
     * @param AbstractEntity $resource
     * @param DataObject|null $object
     * @return AbstractEntity
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundLoadAllAttributes(
        \Magento\Eav\Model\Entity\AttributeLoader $subject,
        callable $proceed,
        AbstractEntity $resource,
        DataObject $object = null
    ) {
        $suffix = $this->getLoadAllAttributesCacheSuffix($object);
        $this->registry->unregister(self::ATTRIBUTES_CACHE_SUFFIX);
        $this->registry->register(self::ATTRIBUTES_CACHE_SUFFIX, $suffix);

        $return = $proceed($resource, $object);

        $this->registry->unregister(self::ATTRIBUTES_CACHE_SUFFIX);

        return $return;
    }


    /**
     * @param DataObject|null $object
     * @return string
     */
    private function getLoadAllAttributesCacheSuffix(DataObject $object = null)
    {
        $attributeSetId = 0;
        $storeId = 0;
        if (null !== $object) {
            $attributeSetId = $object->getAttributeSetId() ?: $attributeSetId;
            $storeId = $object->getStoreId() ?: $storeId;
        }
        $suffix = $storeId . '-' . $attributeSetId;

        return $suffix;
    }
}