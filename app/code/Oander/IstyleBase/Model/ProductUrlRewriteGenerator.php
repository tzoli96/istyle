<?php
/**
 * Oander_IstyleBase
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleBase\Model;

use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator as MagentoProductUrlRewriteGenerator;

use Magento\Catalog\Model\Product;

/**
 * Class ProductUrlRewriteGenerator
 *
 * @package Oander\IstyleBase\Model
 */
class ProductUrlRewriteGenerator extends MagentoProductUrlRewriteGenerator
{
    /**
     * @param \Magento\Framework\Data\Collection $productCategories
     *
     * @return array|\Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    protected function generateForGlobalScope($productCategories)
    {
        $urls = [];
        $productId = $this->product->getEntityId();
        foreach ($this->product->getStoreIds() as $id) {
            if (!$this->isGlobalScope($id)
                && !$this->storeViewService->doesEntityHaveOverriddenUrlKeyForStore($id, $productId, Product::ENTITY)
            ) {
                $storeSpecificProductCategories = clone $productCategories;
                $storeSpecificProductCategories->setStoreId($id);

                $urls = array_merge($urls, $this->generateForSpecificStoreView($id, $storeSpecificProductCategories));
            }
        }
        return $urls;
    }
}
