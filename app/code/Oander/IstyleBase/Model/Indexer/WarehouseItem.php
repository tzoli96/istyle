<?php
/**
 * Oander_IstyleBase
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleBase\Model\Indexer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Oander\WarehouseManager\Api\Data\ProductStockInterface;

/**
 * Class WarehouseItem
 * @package Oander\IstyleBase\Model\Indexer\WarehouseItem
 */
class WarehouseItem extends \Oander\WarehouseManager\Model\Indexer\WarehouseItem
{
    /**
     * @param array $fromWarehouseItemIds
     */
    protected function removeDeletedProducts($fromWarehouseItemIds = [])
    {
        if (!empty($fromWarehouseItemIds)) {
            return null;
        }

        $this->writeLog('removeDeletedProducts', 1, 0, 100);

        $productIds = $this->productCollectionFactory->create()
            ->addAttributeToFilter(
                Product::TYPE_ID,
                ['in' => [Type::TYPE_SIMPLE,Type::TYPE_VIRTUAL, 'insurance']]
            )
            ->addFieldToSelect('product_id')
            ->getAllIds();

        if (!empty($productIds)) {
            $this->warehouseItemApi->deleteWhereProductIdNotIn($productIds);
            $this->productStockApi->deleteWhereProductIdNotIn($productIds);
        }

        $this->writeLog('removeDeletedProducts', 1, 100, 100);
    }

    /**
     * @param array $fromWarehouseItemIds
     */
    protected function createWarehouseItems($fromWarehouseItemIds = [])
    {
        if (!empty($fromWarehouseItemIds)) {
            return null;
        }

        $productCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect(Product::TYPE_ID)
            ->addAttributeToFilter(Product::TYPE_ID, ['in' => [Type::TYPE_SIMPLE,Type::TYPE_VIRTUAL,'insurance']]);

        $websiteIds = [];
        $websiteCollection = $this->storeManager->getWebsites(true);
        /** @var WebsiteInterface $website */
        foreach ($websiteCollection as $website) {
            $websiteIds[] = $website->getId();
        }
        $warehouseItemProductIds = $this->warehouseItemApi->getProductIds($websiteIds);
        $warehouseItemProductIds = array_unique($warehouseItemProductIds);

        $allEntity = count($productCollection);
        $currEntity = 0;
        /** @var Product $product */
        foreach ($productCollection as $product) {
            $currEntity++;
            $this->writeLog('createWarehouseItems', 3, $currEntity, $allEntity);
            if (!in_array($product->getId(), $warehouseItemProductIds)) {
                $this->saveWarehouseItems($product);
            }
        }
    }

    /**
     * @param array $fromWarehouseItemIds
     */
    protected function createProductStock($fromWarehouseItemIds = [])
    {
        if (!empty($fromWarehouseItemIds)) {
            return null;
        }

        $productCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect(Product::TYPE_ID)
            ->addAttributeToFilter(Product::TYPE_ID, ['in' => [Type::TYPE_SIMPLE,Type::TYPE_VIRTUAL,'insurance']]);

        $productStockCollection = $this->productStockApi->getAllProductStock();
        $productStockIds = [];
        /** @var ProductStockInterface $productStock */
        foreach ($productStockCollection as $productStock) {
            if (!isset($productStockIds[$productStock->getWebsiteId()])) {
                $productStockIds[$productStock->getWebsiteId()] = [];
            }
            $productStockIds[$productStock->getWebsiteId()][] = $productStock->getProductId();
        }

        $allEntity = count($productCollection);
        $currEntity = 0;
        /** @var Product $product */
        foreach ($productCollection as $product) {
            $currEntity++;
            $this->writeLog('createProductStock', 4, $currEntity, $allEntity);
            $productWebsiteIds = $product->getWebsiteIds();
            foreach ($productStockIds as $websiteId => $productWebsiteStockIds) {
                if (!in_array($product->getId(), $productWebsiteStockIds)
                    && in_array($websiteId, $productWebsiteIds)) {
                    $this->saveWarehouseProductStock($product, $websiteId);
                    $this->saveStockStatus($product);
                }
            }
        }
    }
}
