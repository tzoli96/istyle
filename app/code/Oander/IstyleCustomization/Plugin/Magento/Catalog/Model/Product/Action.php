<?php

namespace Oander\IstyleCustomization\Plugin\Magento\Catalog\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Relation as ProductRelation;
use Magento\Framework\App\CacheInterface;
use Magento\Store\Model\StoreManagerInterface;
use Oander\WarehouseManager\Api\Data\WarehouseInterface;
use Oander\WarehouseManager\Api\Data\WarehouseItemInterface;
use Oander\WarehouseManager\Api\WarehouseItemRepositoryInterface;
use Oander\WarehouseManager\Api\WarehouseRepositoryInterface;

/**
 * Class Action
 * @package Oander\IstyleCustomization\Plugin\Magento\Catalog\Model\Product
 */
class Action
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var WarehouseItemRepositoryInterface
     */
    protected $warehouseItemRepository;

    /**
     * @var WarehouseRepositoryInterface
     */
    protected $warehouseRepository;

    /**
     * @var ProductRelation
     */
    protected $productRelation;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * Product constructor.
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param WarehouseItemRepositoryInterface $warehouseItemRepository
     * @param WarehouseRepositoryInterface $warehouseRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        WarehouseItemRepositoryInterface $warehouseItemRepository,
        WarehouseRepositoryInterface $warehouseRepository,
        StoreManagerInterface $storeManager,
        ProductRelation $productRelation,
        CacheInterface $cache
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->warehouseItemRepository = $warehouseItemRepository;
        $this->warehouseRepository = $warehouseRepository;
        $this->productRelation = $productRelation;
        $this->cache = $cache;
    }

    public function aroundUpdateAttributes(
        \Magento\Catalog\Model\Product\Action $subject,
        \Closure $proceed,
        array $productIds,
        array $attrData,
        $storeId
    ) {

        $cacheTags = [];
        if (isset($attrData[\Oander\WarehouseManager\Enum\Attribute::EXTERNAL_STOCK_DISABLE])) {
            foreach ($productIds as $productId) {
                $product = $this->productRepository->getById($productId);
                $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
                $cacheTags[] = Product::CACHE_TAG.'_'.$productId;
                $parentIds = $this->getParentProductIds($productId);
                foreach ($parentIds as $parentId) {
                    $cacheTags[] = Product::CACHE_TAG.'_'.$parentId;
                }

                $warehouseItems = $this->warehouseItemRepository->getByWebsiteIdAndProductId($websiteId, $product->getId())->getItems();
                /** @var WarehouseItemInterface $warehouseItem */
                foreach ($warehouseItems as $warehouseItem) {
                    /** @var WarehouseInterface $warehouse */
                    $warehouse = $this->warehouseRepository->getById($warehouseItem->getWarehouseId());
                    if ($warehouse->getType() == \Oander\WarehouseManager\Model\Source\Type::TYPE_EXTERNAL) {
                        $warehouseItem->setQtyDisabled((bool)$attrData[\Oander\WarehouseManager\Enum\Attribute::EXTERNAL_STOCK_DISABLE]);
                    } else {
                        $warehouseItem->setQtyDisabled(false);
                    }

                    $this->warehouseItemRepository->save(
                        $warehouseItem->getItemId(),
                        $warehouseItem->getWebsiteId(),
                        $warehouseItem->getWarehouseId(),
                        $warehouseItem->getProductId(),
                        $warehouseItem->getQuantity(true),
                        $warehouseItem->isQtyDisabled()
                    );
                }

            }
        }

        $result = $proceed($productIds, $attrData, $storeId);

        if (!empty($cacheTags)) {
            $this->cache->clean($cacheTags);
        }

        return $result;
    }


    /**
     * @param $productId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getParentProductIds($productId)
    {
        $select = $this->productRelation->getConnection()->select()->from(
            $this->productRelation->getMainTable(),
            ['parent_id']
        )->where(
            'child_id = ?',
            $productId
        );

        return $this->productRelation->getConnection()->fetchCol($select);
    }
}
