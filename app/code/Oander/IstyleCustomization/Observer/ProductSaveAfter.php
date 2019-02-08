<?php

declare(strict_types=1);

namespace Oander\IstyleCustomization\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ResourceModel\Product\Relation as ProductRelation;
use Magento\Store\Model\StoreManagerInterface;


/**
 * Class ProductSaveAfter
 * @package Oander\IstyleCustomization\Observer
 */
class ProductSaveAfter implements ObserverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductRelation
     */
    protected $productRelation;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ProductSaveAfter constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ProductRelation $productRelation
     * @param ProductFactory $productFactory
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductRelation $productRelation,
        ProductFactory $productFactory,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager
    ) {
        $this->productRepository = $productRepository;
        $this->productRelation = $productRelation;
        $this->productFactory = $productFactory;
        $this->resource = $resource;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getProduct();
        if ($product->getTypeId() == Type::TYPE_SIMPLE && $product->getStatus() == Status::STATUS_DISABLED) {
            $storeId = (int)$product->getStoreId();
            $store = $this->storeManager->getStore($storeId);
            $this->storeManager->setCurrentStore($store);
            $websiteId = $store->getWebsiteId();

            /** @var Product $parentProduct */
            $parentProducts = $this->getParents($product, $websiteId);
            foreach ($parentProducts as $parentProduct) {
                if ($parentProduct && $parentProduct->getTypeId() == Configurable::TYPE_CODE
                    && $parentProduct->getStatus() == Status::STATUS_ENABLED
                ) {
                    $childrenProducts = $parentProduct->getTypeInstance()->getUsedProducts($parentProduct);
                    $isAllChildrenProductsDisabled = true;
                    foreach ($childrenProducts as $childrenProduct) {
                        if ($childrenProduct->getStatus() == Status::STATUS_ENABLED) {
                            $isAllChildrenProductsDisabled = false;
                            break;
                        }
                    }

                    if ($isAllChildrenProductsDisabled) {
                        $parentProduct->setStoreId($storeId);
                        $parentProduct->setStatus(Status::STATUS_DISABLED);
                        $this->productRepository->save($parentProduct);

                    }
                }
            }
        }

    }

    public function getParents($product, $websiteId)
    {
        $parents = [];

        $select = $this->productRelation->getConnection()->select()->from(
            $this->productRelation->getMainTable(),
            ['parent_id']
        )->where(
            'child_id = ?',
            $product->getId()
        );
        $parentIds = $this->productRelation->getConnection()->fetchCol($select);
        if (count($parentIds)) {
            /** @var  $store */
            $parentIdsstring = implode(',', $parentIds);
            $select = $this->resource->getConnection()->select()->from(
                $this->resource->getTableName('catalog_product_website'),
                ['product_id']
            )->where(
                "product_id IN ({$parentIdsstring}) and website_id = {$websiteId}"
            );
            $parentIds = $this->productRelation->getConnection()->fetchCol($select);
            if (count($parentIds)) {
                foreach ($parentIds as $parentId) {
                    $parents[] = $this->productFactory->create()->load($parentId);
                }
            }
        }

        return $parents;
    }
}