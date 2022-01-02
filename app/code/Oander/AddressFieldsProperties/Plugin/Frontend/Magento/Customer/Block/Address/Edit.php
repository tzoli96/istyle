<?php
/**
 * Address Fields Validation
 * Copyright (C) 2019
 *
 * This file is part of Oander/AddressFieldsProperties.
 *
 * Oander/AddressFieldsProperties is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

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