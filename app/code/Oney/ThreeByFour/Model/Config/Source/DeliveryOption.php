<?php

namespace Oney\ThreeByFour\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class DeliveryOption implements ArrayInterface
{
    const LABEL = "Delivery Option";

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ["value" => 1, "label" => "Express ( < 24 hours )"],
            ["value" => 2, "label" => "Standard"],
            ["value" => 3, "label" => "Priority"],
        ];
    }
}
