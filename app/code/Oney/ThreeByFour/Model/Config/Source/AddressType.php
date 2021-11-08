<?php

namespace Oney\ThreeByFour\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class AddressType implements ArrayInterface
{
    const LABEL = "Address Type";
    public function toOptionArray()
    {
        return [
            ["value" => 1, "label" => "Merchant"],
            ["value" => 2, "label" => "Third party relay point"],
            ["value" => 3, "label" => "Airport, train station, travel agency"],
            ["value" => 4, "label" => "Billing Address"],
            ["value" => 5, "label" => "Delivery Address"],
            ["value" => 6, "label" => "Electronic way (ticket, download)"]
        ];
    }
}
