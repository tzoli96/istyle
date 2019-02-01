<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Oander\ApplePay\Plugin\CartButton\Model;

use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\App\Area;

class CartButtonProviderDefault
{
    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * CartButtonProviderDefault constructor.
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        BlockFactory $blockFactory
        )
    {

        $this->blockFactory = $blockFactory;
    }


    public function aroundGetAjaxButtonHtml(\Oander\CartButton\Model\CartButtonProviderDefault $subject, callable $proceed, $product)
    {
        /** @var \Oander\CartButton\Block\Ajax\Button $productStockBlock */
        $productStockBlock = $this->blockFactory->createBlock(\Oander\CartButton\Block\Ajax\Button::class);
        $productStockBlock->setData('area', Area::AREA_FRONTEND);
        $productStockBlock->setTemplate('Oander_ApplePay::ajax/applepaybutton.phtml');

        return $productStockBlock->toHtml();
    }
}
