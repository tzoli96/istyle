<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Oander\IstyleCustomization\Plugin\Bundle\Helper\Catalog\Product;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

/**
 * Bundle Price Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configuration
{
    /**
     * Obtain final price of selection in a bundle product
     *
     * @param ItemInterface $item
     * @param \Magento\Catalog\Model\Product $selectionProduct
     * @return float
     */
    public function aroundGetSelectionFinalPrice(
        \Magento\Bundle\Helper\Catalog\Product\Configuration $subject,
        callable $proceed,
        ItemInterface $item,
        \Magento\Catalog\Model\Product $selectionProduct)
    {
        $product = $item->getProduct();
        $selectionQty = $subject->getSelectionQty($product, $selectionProduct->getSelectionId());
        $originalprice = $proceed($item, $selectionProduct);
        $frompriceinfo = $selectionQty * $selectionProduct->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $tax = $selectionProduct->getPriceInfo()->getPrice('final_price')->getAmount()->getAdjustmentAmounts()['tax'];
        if(($frompriceinfo-$tax) == $originalprice)
            $originalprice = $frompriceinfo;
        //$originalprice = $proceed($item, $selectionProduct);
        return $originalprice;
    }
}
