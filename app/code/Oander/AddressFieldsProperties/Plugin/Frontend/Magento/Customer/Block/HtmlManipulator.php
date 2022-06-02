<?php

namespace Oander\AddressFieldsProperties\Plugin\Frontend\Magento\Customer\Block;

use Magento\Framework\View\Element\AbstractBlock as AbstractBlock;
use Oander\AddressFieldsProperties\Helper\Config as ConfigHelper;
use Oander\AddressFieldsProperties\Plugin\Frontend\Magento\Customer\Helper\Address as AddressHelper;

/**
 * Manipulate Block HTML output with placeholder and error message
 */
class HtmlManipulator
{
    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * Edit constructor.
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ConfigHelper $configHelper
    )
    {
        $this->configHelper = $configHelper;
    }

    /**
     * Manipulate Block HTML output with placeholder and error message
     * @param $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(
        AbstractBlock $subject,
        string        $result
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

    /**
     * Manipulate placeholder and error message to DOMNode
     * @param \DOMNode $node
     * @param string $result
     * @return void
     */
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

    /**
     * Try to replace existing DOMNode property if exist and return true, in other case just return false
     * @param \DOMNode $node
     * @param string $result
     * @param string $property
     * @param string $value
     * @return bool
     */
    private function _tryReplaceProperty(&$node, &$result, $property, $value)
    {
        if ($oldValue = $node->getAttribute($property)) {
            $result = str_replace($oldValue, $value, $result);
            return true;
        }
        return false;
    }

    /**
     * Get attribute code placed in classes in previous steps
     * @param \DOMNode $node
     * @return string|null
     */
    private function _getAttributeCode(&$node)
    {
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