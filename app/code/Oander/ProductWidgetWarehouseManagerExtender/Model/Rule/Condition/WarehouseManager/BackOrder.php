<?php

namespace Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager;

use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager;
use Oander\WarehouseManager\Enum\ProductStock\BackOrder as BackOrderEnum;

/**
 * Class BackOrder
 * @package Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager
 */
class BackOrder extends WarehouseManager
{

    /**
     * Load attribute options
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(
            [
                'back_order' => __('Backorder')
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
                    ['value' => BackOrderEnum::ENABLED, 'label' => __('Enabled')],
                    ['value' => BackOrderEnum::DISABLED, 'label' => __('Disabled')]
                ]
            );
        }

        return $this->getData('value_select_options');
    }

}