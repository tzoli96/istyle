<?php

namespace Oney\ThreeByFour\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PriorityDeliveryCode implements ArrayInterface
{
    const LABEL = "Priority Delivery Code";

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ["value" => 1, "label" => "Less than or equal to 1 hour"],
            ["value" => 2, "label" => "Greater than 1 hour"],
            ["value" => 3, "label" => "Immediate"],
            ["value" => 4, "label" => "24/24 - 7/7"]
        ];
    }
}
