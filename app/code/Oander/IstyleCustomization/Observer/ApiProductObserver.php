<?php
/**
 *   /$$$$$$   /$$$$$$  /$$   /$$ /$$$$$$$  /$$$$$$$$ /$$$$$$$
 *  /$$__  $$ /$$__  $$| $$$ | $$| $$__  $$| $$_____/| $$__  $$
 * | $$  \ $$| $$  \ $$| $$$$| $$| $$  \ $$| $$      | $$  \ $$
 * | $$  | $$| $$$$$$$$| $$ $$ $$| $$  | $$| $$$$$   | $$$$$$$/
 * | $$  | $$| $$__  $$| $$  $$$$| $$  | $$| $$__/   | $$__  $$
 * | $$  | $$| $$  | $$| $$\  $$$| $$  | $$| $$      | $$  \ $$
 * |  $$$$$$/| $$  | $$| $$ \  $$| $$$$$$$/| $$$$$$$$| $$  | $$
 *  \______/ |__/  |__/|__/  \__/|_______/ |________/|__/  |__/
 *
 * Oander_Maintenance
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\IstyleCustomization\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Oander\IstyleCustomization\Enum\ProductTypeEnum;

class ApiProductObserver implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $eventName = $observer->getEvent()->getName();

        if ($eventName === 'api_product_attribute_set_before') {
            $product = $observer->getEvent()->getData('product');
            $attributes = $observer->getEvent()->getData('attributes');
            $attributes = $this->apiProductAttributeSetBeforeHandler($product, $attributes);
            $observer->getEvent()->setData('attributes', $attributes);
        }
    }

    private function apiProductAttributeSetBeforeHandler($product, $attributes)
    {
        if (isset($attributes['type_id'])) {
            $productTypeId = $product->getTypeId();
            if ($productTypeId === ProductTypeEnum::PRODUCT_TYPE_ID_INSURANCE && $attributes['type_id'] !== ProductTypeEnum::PRODUCT_TYPE_ID_INSURANCE) {
                unset($attributes['type_id']);
            }
        }

        return $attributes;
    }
}