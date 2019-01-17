<?php

declare(strict_types=1);

namespace Oander\IstyleCustomization\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

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
     * ProductSaveAfter constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
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
            $parentProduct = $this->getParent($product);
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