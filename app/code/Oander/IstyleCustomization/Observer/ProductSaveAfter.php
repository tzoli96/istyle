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
     * ProductSaveAfter constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ProductRelation            $productRelation
     * @param ProductFactory             $productFactory
     * @param ResourceConnection         $resource
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductRelation $productRelation,
        ProductFactory $productFactory,
        ResourceConnection $resource
    ) {
        $this->productRepository = $productRepository;
        $this->productRelation = $productRelation;
        $this->productFactory = $productFactory;
        $this->resource = $resource;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getProduct();
        // TODO check product save before status too
        if ($product->getTypeId() == Type::TYPE_SIMPLE && $product->getStatus() == Status::STATUS_ENABLED) {

            /** @var Product $parentProduct */
            $parentProducts = $this->getParents($product);
            foreach ($parentProducts as $parentProduct) {
                if ($parentProduct && $parentProduct->getTypeId() == Configurable::TYPE_CODE
                    && $parentProduct->getStatus() == Status::STATUS_DISABLED
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
                        $parentProduct->setStatus(Status::STATUS_DISABLED);
                        $this->productRepository->save($parentProduct);
                    }
                }
            }
        }

    }

    /**
     * @param $product
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getParents($product)
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
            $websiteIds = $product->getWebsiteIds();
            $websiteIdsString = implode(',', $websiteIds);
            $parentIdsString = implode(',', $parentIds);
            $select = $this->resource->getConnection()->select()->from(
                $this->resource->getTableName('catalog_product_website'),
                ['product_id']
            )->where(
                "product_id IN ({$parentIdsString}) and website_id IN {$websiteIdsString}"
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