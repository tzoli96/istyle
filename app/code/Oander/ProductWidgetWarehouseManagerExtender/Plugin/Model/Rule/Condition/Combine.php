<?php
/**
 * Oander_ProductWidgetWarehouseManagerExtender
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types = 1);

namespace Oander\ProductWidgetWarehouseManagerExtender\Plugin\Model\Rule\Condition;

use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager\Availability;
use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager\BackOrder;
use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager\PreOrder;
use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager\StockStatus;
use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager\WebsiteQty;
use Oander\WarehouseManager\Api\Data\ProductStockInterface;

/**
 * Class Combine
 * @package Oander\ProductWidgetWarehouseManagerExtender\Plugin\Model\Rule\Condition
 */
class Combine
{
    /**
     * @param \Magento\CatalogWidget\Model\Rule\Condition\Combine $subject
     * @param array                                               $result
     *
     * @return array
     */
    public function afterGetNewChildSelectOptions(\Magento\CatalogWidget\Model\Rule\Condition\Combine $subject, $result)
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