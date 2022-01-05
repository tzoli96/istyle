<?php

namespace Oander\AddressFieldsProperties\Plugin\Frontend\Magento\Customer\Block\Address;

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
        $classname = "oanderplaceholder";
        /** @var \DOMNodeList $nodes */
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        foreach ($nodes as $node)
        {
            $attributeCode = null;
            $classes = $node->getAttribute("class");
            if(!empty($classes))
            {
                $classes = explode(" ", $classes);
                foreach ($classes as $class)
                {
                    if(strpos($class, "oanderplaceholder") === 0)
                    {
                        if($class!=="oanderplaceholder")
                        {
                            $attributeCode = str_replace("oanderplaceholder-", "", $class);
                        }
                    }
                }
            }
            if(!empty($attributeCode)) {
                $placeholder = $this->configHelper->getPlaceholder($attributeCode);
                if ($oldPlaceholder = $node->getAttribute("placeholder")) {
                    $result = str_replace($oldPlaceholder, $placeholder, $result);
                }
                else {
                    $pos = strpos($result, "oanderplaceholder-" . $attributeCode);
                    $classPos = strrpos(substr($result, 0, $pos), "class=");
                    $result = substr_replace($result, 'placeholder="' . $placeholder . '" ', $classPos, 0);
                }
                $result = str_replace("oanderplaceholder-" . $attributeCode, "", $result);
            }
        }
        $result = str_replace("oanderplaceholder", "", $result);
        return $result;
    }
}