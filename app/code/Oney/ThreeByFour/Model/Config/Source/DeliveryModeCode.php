<?php

namespace Oney\ThreeByFour\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class DeliveryModeCode implements ArrayInterface
{
    const LABEL = "Delivery Mode Code";

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ["value" => 1, "label" => __("Collection of the goods in the merchant store")],
            ["value" => 2, "label" => __("Collection in a third party point")],
            ["value" => 3, "label" => __("Collection in a airport, train station or travel agency")],
            ["value" => 4, "label" => __("Carrier (La poste, Colissimo, UPS, DHL)")],
            ["value" => 5, "label" => __("Electronic ticket issuance, download")]
        ];
    }
}
