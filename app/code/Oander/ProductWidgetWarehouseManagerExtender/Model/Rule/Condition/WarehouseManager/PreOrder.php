<?php

namespace Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager;

use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager;
use Oander\WarehouseManager\Enum\ProductStock\PreOrder as PreOrderEnum;

/**
 * Class PreOrder
 * @package Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager
 */
class PreOrder extends WarehouseManager
{
    /**
     * Load attribute options
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(
            [
                'pre_order' => __('Preorder')
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
                    ['value' => PreOrderEnum::ENABLED, 'label' => __('Enabled')],
                    ['value' => PreOrderEnum::DISABLED, 'label' => __('Disabled')]
                ]
            );
        }

        return $this->getData('value_select_options');
    }

}