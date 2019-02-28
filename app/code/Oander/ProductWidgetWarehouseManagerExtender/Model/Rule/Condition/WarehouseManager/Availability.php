<?php

namespace Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager;

use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager;
use Oander\WarehouseManager\Enum\ProductStock\Availability as AvailabilityEnum;

/**
 * Class Availability
 * @package Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager
 */
class Availability extends WarehouseManager
{

    /**
     * Load attribute options
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(
            [
                'availability' => __('Availability')
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
                    ['value' => AvailabilityEnum::GREEN, 'label' => __('In stock')],
                    ['value' => AvailabilityEnum::YELLOW, 'label' => __('Only in external warehouse')],
                    ['value' => AvailabilityEnum::RED, 'label' => __('Out of stock')]
                ]
            );
        }

        return $this->getData('value_select_options');
    }

}