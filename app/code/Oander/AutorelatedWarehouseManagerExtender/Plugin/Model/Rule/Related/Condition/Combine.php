<?php

namespace Oander\AutorelatedWarehouseManagerExtender\Plugin\Model\Rule\Related\Condition;

use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager\Rule\Availability;
use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager\Rule\BackOrder;
use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager\Rule\PreOrder;
use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager\Rule\StockStatus;
use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager\Rule\WebsiteQty;
use Oander\WarehouseManager\Api\Data\ProductStockInterface;

/**
 * Class Combine
 * @package Oander\AutorelatedWarehouseManagerExtender\Plugin\Model\Rule\Related\Condition
 */
class Combine
{
    /**
     * @param \Aheadworks\Autorelated\Model\Rule\Related\Condition\Combine $subject
     * @param                                                              $result
     *
     * @return array
     */
    public function afterGetNewChildSelectOptions(\Aheadworks\Autorelated\Model\Rule\Related\Condition\Combine $subject, $result)
    {
        $result[] = [
            'value' => [
                [
                    'value' => StockStatus::class . '|' . ProductStockInterface::STOCK_STATUS,
                    'label' => __('Stock Status'),
                ],
                [
                    'value' => Availability::class . '|' . ProductStockInterface::AVAILABILITY,
                    'label' => __('Availability'),
                ],
                [
                    'value' => WebsiteQty::class . '|' . ProductStockInterface::WEBSITE_QTY,
                    'label' => __('Website Qty'),
                ],
                [
                    'value' => PreOrder::class . '|' . ProductStockInterface::PRE_ORDER,
                    'label' => __('Pre Order'),
                ],
                [
                    'value' => BackOrder::class . '|' . ProductStockInterface::BACK_ORDER,
                    'label' => __('Back Order'),
                ],
            ],
            'label' => __('Warehouse Manager'),
        ];

        return $result;
    }
}