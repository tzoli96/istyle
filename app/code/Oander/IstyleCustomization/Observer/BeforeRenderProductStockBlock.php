<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class BeforeRenderProductStockBlock
 * @package Oander\IstyleCustomization\Observer
 */
class BeforeRenderProductStockBlock implements ObserverInterface
{

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $productStockBlock = $observer->getData('product_stock_block');

        if ($productStockBlock->getTemplate() == 'Oander_WarehouseManager::product/view/stock_sum.phtml'
            && $productStockBlock->getProductStock()->getData('default_selections') !== null
        ) {
            $productStockBlock->setTemplate('Oander_IstyleCustomization::product/view/stock_sum.phtml');
        }

        $observer->setData('product_stock_block', $productStockBlock);
    }
}