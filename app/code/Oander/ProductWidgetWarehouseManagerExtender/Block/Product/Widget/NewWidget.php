<?php

namespace Oander\ProductWidgetWarehouseManagerExtender\Block\Product\Widget;

use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\Sql\Builder as WarehouseSqlBuilder;
use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager as WarehouseManagerCondition;
use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager\Availability;
use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager\BackOrder;
use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager\PreOrder;
use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager\StockStatus;
use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager\WebsiteQty;
use Oander\WarehouseManager\Api\Data\ProductStockInterface;

/**
 * Class NewWidget
 * @package Oander\ProductWidgetWarehouseManagerExtender\Block\Product\Widget
 */
class NewWidget extends \Magento\Catalog\Block\Product\Widget\NewWidget
{
    /**
     * @var WarehouseSqlBuilder
     */
    protected $warehouseSqlBuilder;

    /**
     * @var array
     */
    protected $warehouseConditionTypes = [
        StockStatus::class,
        Availability::class,
        BackOrder::class,
        PreOrder::class,
        WebsiteQty::class
    ];

    protected $originalTemplate = null;

    /**
     * @var WarehouseManagerCondition
     */
    protected $warehouseManagerCondition;

    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $conditionsHelper;
    /**
     * @var \Magento\Rule\Model\Condition\Sql\Builder
     */
    protected $sqlBuilder;
    /**
     * @var \Magento\CatalogWidget\Model\Rule
     */
    protected $rule;

    /**
     * NewProduct constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context                         $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility                      $catalogProductVisibility
     * @param \Magento\Framework\App\Http\Context                            $httpContext
     * @param WarehouseSqlBuilder                                            $warehouseSqlBuilder
     * @param WarehouseManagerCondition                                      $warehouseManagerCondition
     * @param \Magento\Widget\Helper\Conditions                              $conditionsHelper
     * @param \Magento\Rule\Model\Condition\Sql\Builder                      $sqlBuilder
     * @param \Magento\CatalogWidget\Model\Rule                              $rule
     * @param array                                                          $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\Http\Context $httpContext,
        WarehouseSqlBuilder $warehouseSqlBuilder,
        WarehouseManagerCondition $warehouseManagerCondition,
        \Magento\Widget\Helper\Conditions $conditionsHelper,
        \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder,
        \Magento\CatalogWidget\Model\Rule $rule,
        array $data = []
    ) {

        parent::__construct($context, $productCollectionFactory, $catalogProductVisibility, $httpContext, $data);

        $this->warehouseSqlBuilder = $warehouseSqlBuilder;

        $template = (isset($data['template']) && $data['template'] !== '') ? $data['template'] : $this->getTemplate();
        if (!empty($template)) {
            $this->originalTemplate = 'Magento_Catalog::'.$template;
        }

        $this->warehouseManagerCondition = $warehouseManagerCondition;
        $this->conditionsHelper = $conditionsHelper;
        $this->sqlBuilder = $sqlBuilder;
        $this->rule = $rule;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        if ($this->getData('is_ajax')) {
            $this->unsetData('cache_lifetime');
            $this->unsetData('cache_tags');
        }
    }

    /**
     * Get key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        if ($this->getData('is_ajax')) {
            parent::getCacheKeyInfo();
        } else {
            return [];
        }
    }

    /**
     * Product collection initialize process
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    protected function _getProductCollection()
    {
        if ($this->isAjaxTemplate()) {
            return null;
        }

        $collection = parent::_getProductCollection();

        $conditions = $this->getConditions();
        $conditions->collectValidatedAttributes($collection);

        $this->attachConditionToCollection($collection, $conditions);

        if ($this->hasData('sort_by_warehouse_stock_qty') && $this->getData('sort_by_warehouse_stock_qty')) {
            $this->warehouseManagerCondition->addToCollection($collection);
            $collection->getSelect()->order(ProductStockInterface::TABLE_NAME.'.'.ProductStockInterface::WEBSITE_QTY.' DESC');
        }

        return $collection;
    }

    protected function attachConditionToCollection(
        \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection,
        \Magento\Rule\Model\Condition\Combine $combine
    ) {

        $productConditions = $combine->getConditions();
        $warehouseConditions = [];
        foreach ($productConditions as $key => $condition) {
            if (in_array($condition->getType(), $this->warehouseConditionTypes)) {
                $condition->setAttribute(ProductStockInterface::TABLE_NAME.'.'.$condition->getAttribute());
                $warehouseConditions[] = $condition;
                unset($productConditions[$key]);
            }
        }

        if (!empty($warehouseConditions)) {
            $combine->setConditions($productConditions);

            $warehouseCombine = clone $combine;
            $warehouseCombine->setConditions($warehouseConditions);
            $this->warehouseSqlBuilder->attachConditionToCollection($collection, $warehouseCombine);
        }

        $this->sqlBuilder->attachConditionToCollection($collection, $combine);

    }

    /**
     * @return \Magento\Rule\Model\Condition\Combine
     */
    protected function getConditions()
    {
        $conditions = $this->getData('conditions_encoded')
            ? $this->getData('conditions_encoded')
            : $this->getData('conditions');

        if ($conditions) {
            $conditions = $this->conditionsHelper->decode($conditions);
        }

        $this->rule->loadPost(['conditions' => $conditions]);
        return $this->rule->getConditions();
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        if ($this->isAjaxTemplate()) {
            $this->setTemplate('Oander_ProductWidgetWarehouseManagerExtender::product/widget/content/ajax.phtml');
            return $this;
        } elseif (!($this->hasData('use_original') && $this->getData('use_original')) && !empty($this->getOriginalTemplate())) {
            $this->setTemplate($this->getOriginalTemplate());
        }

        return parent::_beforeToHtml();
    }

    public function getAjaxWidgetUrl()
    {
        return $this->getUrl('oander_product_widget/catalog/widget');
    }

    public function getOriginalTemplate()
    {
        return $this->originalTemplate;
    }

    /**
     * @return bool
     */
    public function isAjaxTemplate()
    {
        return (($this->hasData('is_ajax') && $this->getData('is_ajax'))
                && !($this->hasData('use_original') && $this->getData('use_original')));
    }

    public function getClassName()
    {
        return self::class;
    }
}
