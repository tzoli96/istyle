<?php

namespace Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition;

use Magento\Rule\Model\Condition\Context;
use Magento\Store\Model\StoreManagerInterface;
use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\Sql\Builder as SqlBuilder;
use Oander\WarehouseManager\Api\Data\ProductStockInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\TypeResolver;
use Oander\WarehouseManager\Helper\ProductStockDisplay;

/**
 * Class WarehouseManager
 * @package Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition
 */
class WarehouseManager extends \Magento\Rule\Model\Condition\AbstractCondition
{
    protected $elementName = 'parameters';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resources;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SqlBuilder
     */
    protected $sqlBuilder;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var TypeResolver
     */
    protected $typeResolver;

    /**
     * @var ProductStockDisplay
     */
    protected $productStockDisplay;

    /**
     * WarehouseManager constructor.
     *
     * @param StoreManagerInterface                               $storeManager
     * @param Context                                             $context
     * @param \Magento\Framework\App\ResourceConnection           $resources
     * @param SqlBuilder                                          $sqlBuilder
     * @param MetadataPool                                        $metadataPool
     * @param TypeResolver                                        $typeResolver
     * @param ProductStockDisplay $productStockDisplay
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\App\ResourceConnection $resources,
        SqlBuilder $sqlBuilder,
        MetadataPool $metadataPool,
        TypeResolver $typeResolver,
        ProductStockDisplay $productStockDisplay,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->resources   = $resources;
        $this->storeManager = $storeManager;
        $this->sqlBuilder = $sqlBuilder;
        $this->metadataPool = $metadataPool;
        $this->typeResolver = $typeResolver;
        $this->productStockDisplay = $productStockDisplay;
    }

    /**
     * Get input type
     * @return string
     */
    public function getInputType()
    {
        return 'select';
    }

    /**
     * Get value element type
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * Add condition to collection
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     *
     * @return $this
     */
    public function addToCollection($collection)
    {
        $warehouseConnection = $this->resources->getConnection('warehousemanager');
        $websiteId           = $this->storeManager->getStore()->getWebsiteId();

        $tableName = $warehouseConnection->getTableName(ProductStockInterface::TABLE_NAME);

        $sql = $collection->getSelect()->__toString();
        if (strpos($sql, $tableName) !== false) {
            return $this;
        }

        $collection->getSelect()->join(
            [$warehouseConnection->getConfig()['dbname'] . '.' . $tableName],
            "($tableName.product_id = e.entity_id) AND ($tableName.website_id = $websiteId)",
            []
        );

        return $this;
    }

    public function getTextCollection($collection)
    {
        $warehouseConnection = $this->resources->getConnection('warehousemanager');
        $websiteId           = $this->storeManager->getStore()->getWebsiteId();

        $tableName = $warehouseConnection->getTableName(ProductStockInterface::TABLE_NAME);

        $sql = $collection->getSelect()->__toString();
        if (strpos($sql, $tableName) !== false) {
            return '';
        }

        return [
            'simple' =>
                "INNER JOIN {$warehouseConnection->getConfig()['dbname']}.{$tableName} as wh
                 ON (wh.product_id = e.entity_id AND wh.website_id = {$websiteId})
                 AND (e.type_id = 'simple' OR e.type_id = 'virtual')",
            'configurable' =>
                "INNER JOIN catalog_product_relation as cpr
                ON (e.entity_id = cpr.parent_id AND e.type_id <> 'simple' AND e.type_id <> 'virtual')
                INNER JOIN {$warehouseConnection->getConfig()['dbname']}.{$tableName} as wh
                ON (wh.product_id = e.entity_id AND wh.website_id = {$websiteId})",
            ];

    }

    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $entityType = $this->typeResolver->resolve($model);
        $metaData = $this->metadataPool->getMetadata($entityType);
        $collection = $model->getCollection();
        $this->addToCollection($collection);
        $conditions = $this->getRule()->getConditions();
        $this->sqlBuilder->attachConditionToCollection($collection, $conditions);

        if ($model->getId()) {
            $productStock = $this->productStockDisplay->getProductStock($model->getId());
            $collection->getSelect()->where($metaData->getIdentifierField() . ' = :id');
            $bind['id'] = $productStock->getProductId();
        }
        if ($collection->getConnection()->fetchRow($collection->getSelect(), $bind)) {
            return true;
        }

        return false;
    }

    /**
     * Collect valid attributes
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @param int|null $productId
     * @param array $additionalParams
     * @return $this
     * @throws \Exception
     */
    public function collectValidatedAttributes($productCollection, $productId = null, $additionalParams = [])
    {
       /* $conditions = $this->getRule()->getConditions();
        $whereCondition = $this->sqlBuilder->getWhereConditionToCollection($productCollection, $conditions);

        if ($productId) {
            if (empty($whereCondition)) {
                $whereCondition = "WHERE `e`.entity_id <> {$productId}";
            } else {
                $whereCondition .= "AND `e`.entity_id <> {$productId}";
            }
        }

        $whJoin = $this->getTextCollection($productCollection);
        $simpleProdSql = $productCollection->getSelect()->__toString();

        $simpleProdSqlTrim = substr($simpleProdSql, strpos($simpleProdSql, 'FROM'));
        $configProdSql = "SELECT `conf`.entity_id, `conf`.attribute_set_id, `conf`.type_id,`conf`.sku, `conf`.has_options, `conf`.required_options, `conf`.created_at,`conf`.updated_at FROM ( 
            SELECT * {$simpleProdSqlTrim}
            {$whJoin['configurable']}
            {$whereCondition}
            ORDER BY `wh`.`stock_status` DESC
        ) as conf
        GROUP BY conf.parent_id, conf.website_id";

        $simpleProdSql .= $whJoin['simple'].$whereCondition;
        $masterSql = $configProdSql." UNION DISTINCT ".$simpleProdSql;*/

        $this->addToCollection($productCollection);
        $conditions = $this->getRule()->getConditions();
        $this->sqlBuilder->getWhereConditionToCollection($productCollection, $conditions);

        if ($productId) {
            $productCollection->getSelect()->where("entity_id <> {$productId}");
        }

        return $this;
    }

}