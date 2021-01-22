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
     * @var Configurable
     */
    protected $configurableProduct;

    /**
     * ProductSaveAfter constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ProductFactory $productFactory
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param Configurable $configurableProduct
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductFactory $productFactory,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        Configurable $configurableProduct
    ) {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->configurableProduct = $configurableProduct;
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

            /** @var Product $parentProduct */
            $parentProducts = $this->getParents($product, $storeId);
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

    public function getParents($product, $storeId)
    {
        $parentByChild=$this->configurableProduct->getParentIdsByChild($product->getId());
        $parents = [];
        if (isset($parentByChild[0])) {
            foreach ($parentByChild as $parent)
            {
                $parents[]=$parent;
            }
        }
        $parentProduct = [];
        if (count($parents)) {
            foreach ($parents as $parentId) {
                $parentProduct[]=$this->productFactory->create()
                   ->setStoreId($storeId)
                   ->load($parentId);
            }
        }

        return $parentProduct;
    }
}