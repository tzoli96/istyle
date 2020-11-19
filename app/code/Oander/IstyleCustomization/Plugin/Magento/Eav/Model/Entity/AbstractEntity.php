<?php

namespace Oander\IstyleCustomization\Plugin\Magento\Eav\Model\Entity;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Context;
use Magento\Framework\Registry;

/**
 * Class AbstractEntity
 * @package Oander\IstyleCustomization\Plugin\Magento\Eav\Model\Entity
 */
class AbstractEntity
{
    /**
     * @var \Magento\Framework\Validator\UniversalFactory
     */
    protected $_universalFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Attributes stored by scope (store id and attribute set id).
     *
     * @var array
     */
    protected $attributesByScope = [];

    /**
     * AbstractEntity constructor.
     *
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->_universalFactory = $context->getUniversalFactory();
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Eav\Model\Entity\AbstractEntity $subject
     * @param AbstractAttribute $attribute
     * @return AbstractAttribute[]
     */
    public function beforeAddAttribute(
        \Magento\Eav\Model\Entity\AbstractEntity $subject,
        AbstractAttribute $attribute
    ) {
        $suffix = $this->registry->registry(AttributeLoader::ATTRIBUTES_CACHE_SUFFIX);
        if ($suffix) {
            $attributeCode = $attribute->getAttributeCode();
            $this->attributesByScope[$suffix][$attributeCode] = $attribute;
            $this->registry->registry(AttributeLoader::ATTRIBUTES_CACHE_SUFFIX);
        }

        return [$attribute];
    }

    /**
     * Walk through the attributes and run method with optional arguments
     * Returns array with results for each attribute
     *
     * if $partMethod is in format "part/method" will run method on specified part
     * for example: $this->walkAttributes('backend/validate');
     *
     * @param \Magento\Eav\Model\Entity\AbstractEntity $subject
     * @param callable $proceed
     * @param $partMethod
     * @param array $args
     * @param null $collectExceptionMessages
     * @return array
     * @throws \Magento\Eav\Model\Entity\Attribute\Exception
     */
    public function aroundWalkAttributes(
        \Magento\Eav\Model\Entity\AbstractEntity $subject,
        callable $proceed,
        $partMethod,
        array $args = [],
        $collectExceptionMessages = null
    ) {
        $methodArr = explode('/', $partMethod);
        switch (sizeof($methodArr)) {
            case 1:
                $part = 'attribute';
                $method = $methodArr[0];
                break;

            case 2:
                $part = $methodArr[0];
                $method = $methodArr[1];
                break;

            default:
                break;
        }
        $results = [];
        $suffix = $this->getAttributesCacheSuffix($args[0]);
        $instance = null;
        foreach ($this->getAttributesByScope($suffix, $subject) as $attrCode => $attribute) {
            if (isset($args[0]) && is_object($args[0]) && !$this->_isApplicableAttribute($args[0], $attribute)) {
                continue;
            }

            switch ($part) {
                case 'attribute':
                    $instance = $attribute;
                    break;

                case 'backend':
                    $instance = $attribute->getBackend();
                    break;

                case 'frontend':
                    $instance = $attribute->getFrontend();
                    break;

                case 'source':
                    $instance = $attribute->getSource();
                    break;

                default:
                    break;
            }

            if (!$this->_isCallableAttributeInstance($instance, $method, $args)) {
                continue;
            }

            try {
                $results[$attrCode] = call_user_func_array([$instance, $method], $args);
            } catch (\Magento\Eav\Model\Entity\Attribute\Exception $e) {
                if ($collectExceptionMessages) {
                    $results[$attrCode] = $e->getMessage();
                } else {
                    throw $e;
                }
            } catch (\Exception $e) {
                if ($collectExceptionMessages) {
                    $results[$attrCode] = $e->getMessage();
                } else {
                    /** @var \Magento\Eav\Model\Entity\Attribute\Exception $e */
                    $e = $this->_universalFactory->create(
                        'Magento\Eav\Model\Entity\Attribute\Exception',
                        ['phrase' => __($e->getMessage())]
                    );
                    $e->setAttributeCode($attrCode)->setPart($part);
                    throw $e;
                }
            }
        }

        return $results;
    }

    /**
     * Get attributes cache suffix.
     *
     * @param $object
     * @return string
     */
    protected function getAttributesCacheSuffix($object)
    {
        $attributeSetId = $object->getAttributeSetId() ?: 0;
        $storeId = $object->getStoreId() ?: 0;
        return $storeId . '-' . $attributeSetId;
    }

    /**
     * Get attributes by scope
     *
     * @param $suffix
     * @param \Magento\Eav\Model\Entity\AbstractEntity  $subject
     * @return mixed
     */
    protected function getAttributesByScope($suffix, $subject)
    {
        return !empty($this->attributesByScope[$suffix])
            ? $this->attributesByScope[$suffix]
            : $subject->getAttributesByCode();
    }

    /**
     * Check whether the attribute is Applicable to the object
     *
     * @param \Magento\Framework\DataObject $object
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return boolean
     */
    protected function _isApplicableAttribute($object, $attribute)
    {
        $applyTo = $attribute->getApplyTo();

        return (count($applyTo) == 0 || in_array($object->getTypeId(), $applyTo))
            && $attribute->isInSet($object->getAttributeSetId());
    }

    /**
     * Check whether attribute instance (attribute, backend, frontend or source) has method and applicable
     *
     * @param AbstractAttribute|AbstractBackend|AbstractFrontend|AbstractSource $instance
     * @param string $method
     * @param array $args array of arguments
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _isCallableAttributeInstance($instance, $method, $args)
    {
        if (!is_object($instance) || !method_exists($instance, $method) || !is_callable([$instance, $method])) {
            return false;
        }

        return true;
    }
}