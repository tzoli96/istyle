<?php
/**
 * PWS_Billingo Extend
 * Copyright (C) 2019
 *
 * This file is part of Oander/IstyleCustomization.
 *
 * Oander/IstyleCustomization is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Oander\IstyleCustomization\Plugin\Magento\CatalogUrlRewrite\Observer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Oander\IstyleCustomization\Model\Products\AppendUrlRewritesToProducts;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class ProductProcessUrlRewriteSavingObserver
{

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var AppendUrlRewritesToProducts
     */
    private $appendRewrites;

    /**
     * ProductProcessUrlRewriteSavingObserver constructor.
     * @param UrlPersistInterface $urlPersist
     * @param AppendUrlRewritesToProducts $appendRewrites
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        AppendUrlRewritesToProducts $appendRewrites,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->urlPersist = $urlPersist;
        $this->storeManager = $storeManager;
        $this->appendRewrites = $appendRewrites;
    }

    public function aroundExecute(
        \Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteSavingObserver $subject,
        \Closure $proceed,
        \Magento\Framework\Event\Observer $observer
    ) {
        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();

        if ($this->isNeedUpdateRewrites($product)) {
            $this->deleteObsoleteRewrites($product);
            $oldWebsiteIds = $product->getOrigData('website_ids') ?? [];
            $storesToAdd = $this->_getStoresListExecute(
                array_diff($product->getWebsiteIds(), $oldWebsiteIds)
            );
            $this->appendRewrites->execute([$product], $storesToAdd);
        }

        return;
    }

    /**
     * Remove obsolete Url rewrites
     *
     * @param Product $product
     */
    private function deleteObsoleteRewrites(Product $product)
    {
        //do not perform redundant delete request for new product
        if ($product->getOrigData('entity_id') === null) {
            return;
        }
        $oldWebsiteIds = $product->getOrigData('website_ids') ?? [];
        $storesToRemove = $this->_getStoresListExecute(
            array_diff($oldWebsiteIds, $product->getWebsiteIds())
        );
        if ((int)$product->getVisibility() === Visibility::VISIBILITY_NOT_VISIBLE) {
            $isGlobalScope = $product->getStoreId() == Store::DEFAULT_STORE_ID;
            $storesToRemove[] = $isGlobalScope ? $product->getStoreIds() : $product->getStoreId();
        }
        if ($storesToRemove) {
            $this->urlPersist->deleteByData(
                [
                    UrlRewrite::ENTITY_ID => $product->getId(),
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                    UrlRewrite::STORE_ID => $storesToRemove,
                ]
            );
        }
    }

    /**
     * Is website assignment updated
     *
     * @param Product $product
     * @return bool
     */
    private function isWebsiteChanged(Product $product)
    {
        $oldWebsiteIds = $product->getOrigData('website_ids');
        $newWebsiteIds = $product->getWebsiteIds();

        return array_diff($oldWebsiteIds, $newWebsiteIds) || array_diff($newWebsiteIds, $oldWebsiteIds);
    }

    /**
     * Is product rewrites need to be updated
     *
     * @param Product $product
     * @return bool
     */
    private function isNeedUpdateRewrites(Product $product): bool
    {
        return ($product->dataHasChangedFor('url_key')
                && (int)$product->getVisibility() !== Visibility::VISIBILITY_NOT_VISIBLE)
            || ($product->getIsChangedCategories() && $this->isGenerateCategoryProductRewritesEnabled())
            || $this->isWebsiteChanged($product)
            || $product->dataHasChangedFor('visibility');
    }

    /**
     * Return product use category path in rewrite config value
     *
     * @return bool
     */
    private function isGenerateCategoryProductRewritesEnabled()
    {
        return true;
        //return $this->scopeConfig->isSetFlag('catalog/seo/generate_category_product_rewrites');
    }

    private function _getStoresListExecute(array $websiteIds)
    {
        $storeIdsArray = [];
        foreach ($websiteIds as $websiteId) {
            $storeIdsArray[] = $this->_getStoreByWebsiteId($websiteId);
        }

        return array_merge([], ...$storeIdsArray);
    }

    private function _getStoreByWebsiteId(int $websiteId)
    {
        $storeId = $this->storeManager->getWebsite($websiteId)->getStoreIds();
        return $storeId;
    }
}