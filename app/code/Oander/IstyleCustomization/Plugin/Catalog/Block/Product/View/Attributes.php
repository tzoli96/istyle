<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */


declare(strict_types=1);

namespace Oander\IstyleCustomization\Plugin\Catalog\Block\Product\View;

/**
 * Class Attributes
 * @package Oander\ConfigurableProductAttribute\Magento\Catalog\Block\Product\View
 */
class Attributes
{
    /**
     * @param          $subject
     * @param callable $proceed
     * @param array    $excludeAttr
     *
     * @return array
     */
    public function aroundGetAdditionalData($subject, callable $proceed, array $excludeAttr = [])
    {
        $data = [];
        $product = $subject->getProduct();
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
                $value = $attribute->getFrontend()->getValue($product);
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                if (!$product->hasData($attribute->getAttributeCode())) {
                    continue;
                } elseif ((string)$value == '') {
                    continue;
                    $value = __('No');
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = $subject->priceCurrency->convertAndFormat($value);
                }

                if(($value instanceof \Magento\Framework\Phrase))
                {
                    $value = $value->render();
                }

                if (is_string($value) && strlen($value)) {
                    $data['config'][$attribute->getAttributeCode()] = [
                        'label' => __($attribute->getStoreLabel()),
                        'value' => $value,
                        'code' => $attribute->getAttributeCode(),
                    ];
                }
            }
        }

        if ($subject->getProduct()->getTypeId() == 'configurable') {
            $_children = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($_children as $child) {
                $attributes = $child->getAttributes();
                foreach ($attributes as $attribute) {
                    if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
                        $value = $attribute->getFrontend()->getValue($child);
                        if (is_array($value)) {
                            $value = implode(', ', $value);
                        }

                        if (!$child->hasData($attribute->getAttributeCode())) {
                            continue;
                        } elseif ((string)$value == '') {
                            continue;
                            $value = __('No');
                        } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                            $value = $subject->priceCurrency->convertAndFormat($value);
                        }

                        if (is_string($value) && strlen($value)) {
                            $data['simple'][$child->getSku()][$attribute->getAttributeCode()] = [
                                'label' => __($attribute->getStoreLabel()),
                                'value' => $value,
                                'code' => $attribute->getAttributeCode(),
                            ];
                        }
                    }
                }
            }
        }

        return $data;
    }
}
