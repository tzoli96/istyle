<?php

namespace Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager;

use Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager;

/**
 * Class WebsiteQty
 * @package Oander\ProductWidgetWarehouseManagerExtender\Model\Rule\Condition\WarehouseManager
 */
class WebsiteQty extends WarehouseManager
{

    /**
     * Load attribute options
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(
            [
                'website_qty' => __('Website Qty')
            ]
        );

        return $this;
    }

    /**
     * Get input type for comparison operator
     *
     * @return string
     */
    public function getInputType()
    {
        return 'numeric';
    }

    /**
     * Get value input renderer
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

}