<?php

namespace Oander\IstyleCustomization\Plugin\Magento\Catalog\Model\Product;

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
        StoreManagerInterface $storeManager
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->warehouseItemRepository = $warehouseItemRepository;
        $this->warehouseRepository = $warehouseRepository;
    }

    public function beforeUpdateAttributes(
        \Magento\Catalog\Model\Product\Action $subject,
        array $productIds,
        array $attrData,
        $storeId
    ) {

        if (isset($attrData[\Oander\WarehouseManager\Enum\Attribute::EXTERNAL_STOCK_DISABLE])) {
            foreach ($productIds as $productId) {
                $product = $this->productRepository->getById($productId);
                $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();

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

        return [$productIds,$attrData,$storeId];
    }
}
