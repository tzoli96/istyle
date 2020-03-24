<?php
/**
 * Magento Module developed by NoStress Commerce
 *
 * NOTICE OF LICENSE
 *
 * This program is licensed under the Koongo software licence (by NoStress Commerce).
 * With the purchase, download of the software or the installation of the software
 * in your application you accept the licence agreement. The allowed usage is outlined in the
 * Koongo software licence which can be found under https://docs.koongo.com/display/koongo/License+Conditions
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at https://store.koongo.com/.
 *
 * See the Koongo software licence agreement for more details.
 * @copyright Copyright (c) 2017 NoStress Commerce (http://www.nostresscommerce.cz, http://www.koongo.com/)
 *
 */

/**
 * Observer for Product Save After for Kaas webhooks
 *
 * @category Nostress
 * @package Nostress_Koongo
 */

namespace Nostress\Koongo\Observer;

class ProductSaveAfterObserver extends \Nostress\Koongo\Observer\BaseObserver
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {              
        $product = $observer->getEvent()->getProduct();
        
        $item = $observer->getDataObject();
        if($item->getCallKoongoNewObjectEvent())
        {
            //Product created webhook
            $this->_addNewProductEvent($product);
        }
        else
        {
            //Product updated webhook
            $this->_addUpdateProductEvent($product);
        }        

        //DELETE
        //Simulate event cataloginventory_stock_item_save_after
        // $objectManager =  \Magento\Framework\App\ObjectManager::getInstance(); 
        // $stockItem = $objectManager->get('\Magento\CatalogInventory\Model\Stock\StockItemRepository');        
        // $productStock = $stockItem->get($product->getId());
        // // $productStock->save();

        // $eventManager = $objectManager->get('\Magento\Framework\Event\ManagerInterface');        
        // $eventManager->dispatch('cataloginventory_stock_item_save_after', ['item' => $productStock]);        
    }
}