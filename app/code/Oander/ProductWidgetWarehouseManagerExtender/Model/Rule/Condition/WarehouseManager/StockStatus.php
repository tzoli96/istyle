<?php

namespace Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager;

use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager;
use Oander\WarehouseManager\Enum\ProductStock\StockStatus as StockStatusEnum;

/**
 * Class StockStatus
 * @package Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager
 */
class StockStatus extends WarehouseManager
{

    /**
     * Load attribute options
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(
            [
                'stock_status' => __('Stock Status')
            ]
        );

        return $this;
    }

    /**
     * Get value select options
     * @return array|mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            $this->setData(
                'value_select_options',
                [
                    ['value' => StockStatusEnum::IN_STOCK, 'label' => __('In stock')],
                    ['value' => StockStatusEnum::OUT_OF_STOCK, 'label' => __('Out of stock')]
                ]
            );
        }

        return $this->getData('value_select_options');
    }

}