<?php

namespace Oander\AutorelatedWarehouseManagerExtender\Plugin\Model\ResourceModel\Product;

use Oander\WarehouseManager\Api\Data\ProductStockInterface;
use Oander\WarehouseManager\Enum\ProductStock\StockStatus;

class Collection
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Collection constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection  $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
    }

    /**
     * Adding only in stock products filter to product collection
     *
     * @return $this
     */
    public function aroundAddInStockFilter(\Aheadworks\Autorelated\Model\ResourceModel\Product\Collection $subject, \Closure $method)
    {
        if (!$subject->getFlag(\Aheadworks\Autorelated\Model\ResourceModel\Product\Collection::STOCK_FLAG)) {
            $warehouseConnection = $this->resource->getConnection('warehousemanager');
            $websiteId           = $this->storeManager->getStore()->getWebsiteId();

            $tableName = $warehouseConnection->getTableName(ProductStockInterface::TABLE_NAME);
            $sql = $subject->getSelect()->__toString();
            if (strpos($sql, $tableName) !== false) {
                return $this;
            }

            $subject->getSelect()->join(
                [$warehouseConnection->getConfig()['dbname'] . '.' . $tableName],
                "({$tableName}.product_id = e.entity_id) AND ({$tableName}.website_id = $websiteId)"
                . " AND ({$tableName}.stock_status = '".StockStatus::IN_STOCK."')",
                []
            );

            $subject->setFlag(\Aheadworks\Autorelated\Model\ResourceModel\Product\Collection::STOCK_FLAG, true);
        }

        return $subject;
    }
}