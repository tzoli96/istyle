<?php
namespace Oander\AlgoliaSearchManualReindex\Helper;

use Algolia\AlgoliaSearch\Exception\ProductDeletedException;
use Algolia\AlgoliaSearch\Exception\ProductDisabledException;
use Algolia\AlgoliaSearch\Exception\ProductNotVisibleException;
use Algolia\AlgoliaSearch\Exception\ProductOutOfStockException;
use Algolia\AlgoliaSearch\Helper\AlgoliaHelper;
use Algolia\AlgoliaSearch\Helper\ConfigHelper;
use Algolia\AlgoliaSearch\Helper\Data as OriginalClass;
use Algolia\AlgoliaSearch\Helper\Entity\AdditionalSectionHelper;
use Algolia\AlgoliaSearch\Helper\Entity\CategoryHelper;
use Algolia\AlgoliaSearch\Helper\Entity\PageHelper;
use Algolia\AlgoliaSearch\Helper\Entity\ProductHelper;
use Algolia\AlgoliaSearch\Helper\Entity\SuggestionHelper;
use Algolia\AlgoliaSearch\Helper\Logger;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

class Data extends OriginalClass
{

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    public function __construct(AlgoliaHelper $algoliaHelper, ConfigHelper $configHelper, ProductHelper $producthelper, CategoryHelper $categoryHelper, PageHelper $pageHelper, SuggestionHelper $suggestionHelper, AdditionalSectionHelper $additionalSectionHelper, StockRegistryInterface $stockRegistry, Emulation $emulation, Logger $logger, ResourceConnection $resource, ManagerInterface $eventManager, StoreManagerInterface $storeManager)
    {
        parent::__construct($algoliaHelper, $configHelper, $producthelper, $categoryHelper, $pageHelper, $suggestionHelper, $additionalSectionHelper, $stockRegistry, $emulation, $logger, $resource, $eventManager, $storeManager);
        $this->stockRegistry = $stockRegistry;
        $this->configHelper = $configHelper;
    }

    /**
     * @param Product $product
     * @param int $storeId
     * @return bool
     */
    public function canProductBeReindexed($product, $storeId)
    {

        /*
        if ($product->isDeleted() === true) {
            throw (new ProductDeletedException())
                ->withProduct($product)
                ->withStoreId($storeId);
        }

        Disabled Status
        if ($product->getStatus() == Status::STATUS_DISABLED) {
            throw (new ProductDisabledException())
                ->withProduct($product)
                ->withStoreId($storeId);
        }


        if (!in_array($product->getVisibility(), [
            Visibility::VISIBILITY_BOTH,
            Visibility::VISIBILITY_IN_SEARCH,
            Visibility::VISIBILITY_IN_CATALOG,
        ])) {
            throw (new ProductNotVisibleException())
                ->withProduct($product)
                ->withStoreId($storeId);
        }


        if (!$this->configHelper->getShowOutOfStock($storeId)) {
            $stockItem = $this->stockRegistry->getStockItem($product->getId());
            if (! $product->isSalable() || ! $stockItem->getIsInStock()) {
                throw (new ProductOutOfStockException())
                    ->withProduct($product)
                    ->withStoreId($storeId);
            }
        }

        */
        return true;
    }
}