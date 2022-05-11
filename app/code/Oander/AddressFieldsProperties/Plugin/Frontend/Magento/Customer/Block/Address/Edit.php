<?php

namespace Oander\AddressFieldsProperties\Plugin\Frontend\Magento\Customer\Block\Address;

use Oander\AddressFieldsProperties\Plugin\Frontend\Magento\Customer\Helper\Address as AddressHelper;

class Edit
{
    /**
     * @var \Oander\AddressFieldsProperties\Helper\Config
     */
    private $configHelper;

    /**
     * Edit constructor.
     * @param \Oander\AddressFieldsProperties\Helper\Config $configHelper
     */
    public function __construct(
        \Oander\AddressFieldsProperties\Helper\Config $configHelper
    )
    {
        $this->configHelper = $configHelper;
    }

    public function afterToHtml(
        \Magento\Customer\Block\Address\Edit $subject,
        $result
    ) {
        $dom = new \DomDocument();
        $dom->loadHTML($result);
        $finder = new \DomXPath($dom);
        /** @var \DOMNodeList $nodes */
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' " . AddressHelper::ADDRESSATTRIBUTE_CLASS . " ')]");
        foreach ($nodes as $node)
        {
            $this->addProperties($node, $result);
        }
        $result = str_replace(AddressHelper::ADDRESSATTRIBUTE_CLASS, "", $result);
        return $result;
    }

    private function addProperties(&$node, &$result)
    {
        $attributeCode = $this->_getAttributeCode($node);
        if(!empty($attributeCode))
        {
            $newProperties = [];
            $placeholder = $this->configHelper->getPlaceholder($attributeCode);
            if($placeholder) {
                if (!$this->_tryReplaceProperty($node, $result, "placeholder", $placeholder)) {
                    $newProperties[] = "placeholder='" . $placeholder . "'";
                }
            }
            $errormessage = $this->configHelper->getErrorMessage($attributeCode);
            $errormessage = str_replace('"', "'", $errormessage);
            if($errormessage) {
                if (!$this->_tryReplaceProperty($node, $result, "data-errormessage", $errormessage)) {
                    $newProperties[] = "data-errormessage='" . $errormessage . "'";
                }
            }
            if(!empty($newProperties))
            {
                $pos = strpos($result, AddressHelper::ADDRESSATTRIBUTE_CLASS . "-" . $attributeCode);
                if($pos) {
                    $classPos = strrpos(substr($result, 0, $pos), "class=");
                    $result = substr_replace($result, implode(" ", $newProperties), $classPos, 0);
                }
            }
            $result = str_replace(AddressHelper::ADDRESSATTRIBUTE_CLASS . "-" . $attributeCode, "", $result);
        }
    }

    private function _tryReplaceProperty(&$node, &$result, $property, $value)
    {
        if ($oldValue = $node->getAttribute($property)) {
            $result = str_replace($oldValue, $value, $result);
            return true;
        }
        return false;
    }

    private function _getAttributeCode(&$node)
    {
        $attributeCode = null;
        $classes = $node->getAttribute("class");
        if(!empty($classes))
        {
            $classes = explode(" ", $classes);
            foreach ($classes as $class)
            {
                if(strpos($class, AddressHelper::ADDRESSATTRIBUTE_CLASS) === 0)
                {
                    if($class!==AddressHelper::ADDRESSATTRIBUTE_CLASS)
                    {
                        return str_replace(AddressHelper::ADDRESSATTRIBUTE_CLASS . "-", "", $class);
                    }
                }
            }
        }
        return null;
    }
}