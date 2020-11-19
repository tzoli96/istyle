<?php

namespace Oander\IstyleCustomization\Plugin\Magento\Eav\Model\Entity;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\AttributeCache;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;

/**
 * Class AttributeLoader
 * @package Oander\IstyleCustomization\Plugin\Magento\Eav\Model
 */
class AttributeLoader
{
    const ATTRIBUTES_CACHE_SUFFIX = 'attributes_cache_suffix';

    /**
     * Default Attributes that are static
     *
     * @var array
     */
    private $defaultAttributes = [];

    /**
     * @var \Magento\Framework\Validator\UniversalFactory
     */
    protected $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var AttributeCache
     */
    private $cache;
    /**
     * @var Registry
     */
    private $registry;

    /**
     * AttributeLoader constructor.
     * @param Config $config
     * @param AttributeCache $cache
     * @param ObjectManagerInterface $objectManager
     * @param Registry $registry
     */
    public function __construct(
        Config $config,
        AttributeCache $cache,
        ObjectManagerInterface $objectManager,
        Registry $registry
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
        $this->cache = $cache;
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

        $typeCode = $resource->getEntityType()->getEntityTypeCode();
        $attributes = $this->cache->getAttributes($typeCode, $suffix);
        if ($attributes) {
            foreach ($attributes as $attribute) {
                $resource->addAttribute($attribute);
            }
            return $resource;
        }
        $attributes = $this->checkAndInitAttributes($resource, $object);

        $this->cache->saveAttributes($typeCode, $attributes, $suffix);
        $this->registry->unregister(self::ATTRIBUTES_CACHE_SUFFIX);
        return $resource;
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

    /**
     * @param AbstractEntity $resource
     * @param DataObject|null $object
     * @return array
     */
    private function checkAndInitAttributes(AbstractEntity $resource, DataObject $object = null)
    {
        $attributeCodes = $this->config->getEntityAttributeCodes($resource->getEntityType(), $object);
        $attributes = [];

        /**
         * Check and init default attributes
         */
        $defaultAttributes = $resource->getDefaultAttributes();
        foreach ($defaultAttributes as $attributeCode) {
            $attributeIndex = array_search($attributeCode, $attributeCodes);
            if ($attributeIndex !== false) {
                $attribute = $resource->getAttribute($attributeCodes[$attributeIndex]);
                $attributes[] = $attribute;
                unset($attributeCodes[$attributeIndex]);
            } else {
                $attribute = $this->_getDefaultAttribute($resource, $attributeCode);
                $attributes[] = $attribute;
                $resource->addAttribute($attribute);
            }
        }
        foreach ($attributeCodes as $code) {
            $attribute = $resource->getAttribute($code);
            $attributes[] = $attribute;
        }
        return $attributes;
    }

    /**
     * Return default static virtual attribute that doesn't exists in EAV attributes
     *
     * @param \Magento\Eav\Model\Entity\AbstractEntity $resource
     * @param string $attributeCode
     * @return Attribute
     */
    protected function _getDefaultAttribute(AbstractEntity $resource, $attributeCode)
    {
        $entityTypeId = $resource->getEntityType()->getId();
        if (!isset($this->defaultAttributes[$entityTypeId][$attributeCode])) {
            $attribute = $this->objectManager->create(
                $resource->getEntityType()->getAttributeModel()
            )->setAttributeCode(
                $attributeCode
            )->setBackendType(
                AbstractAttribute::TYPE_STATIC
            )->setIsGlobal(
                1
            )->setEntityType(
                $resource->getEntityType()
            )->setEntityTypeId(
                $resource->getEntityType()->getId()
            );
            $this->defaultAttributes[$entityTypeId][$attributeCode] = $attribute;
        }
        return $this->defaultAttributes[$entityTypeId][$attributeCode];
    }
}