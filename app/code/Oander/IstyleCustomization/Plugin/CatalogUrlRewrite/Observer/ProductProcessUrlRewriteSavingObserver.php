<?php


namespace Oander\IstyleCustomization\Plugin\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Product;

/**
 * Class ProductProcessUrlRewriteSavingObserver
 * @package Oander\IstyleCustomization\Plugin\CatalogUrlRewrite\Observer
 */
class ProductProcessUrlRewriteSavingObserver
{

    /**
     * @param \Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteSavingObserver $subject
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function beforeExecute(
        \Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteSavingObserver $subject,
        \Magento\Framework\Event\Observer $observer
    ) {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();

        if (!($product->dataHasChangedFor('url_key')
            || $product->getIsChangedCategories()
            || $product->getIsChangedWebsites()
            || $product->dataHasChangedFor('visibility')
        ) && $product->getCopyFromView()
        ) {
            $product->setIsChangedWebsites(true);
            $observer->getEvent()->setData('product', $product);
        }
    }
}