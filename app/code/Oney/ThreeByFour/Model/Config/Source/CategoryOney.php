<?php

namespace Oney\ThreeByFour\Model\Config\Source;

class CategoryOney implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ["value" => null, "label" => __("-- Please Select --")],
            ["value" => 1, "label" => __("Food & drinks")],
            ["value" => 2, "label" => __("Auto & motorcycle")],
            ["value" => 3, "label" => __("Culture & entertainment")],
            ["value" => 4, "label" => __("Home & garden")],
            ["value" => 5, "label" => __("Home appliances")],
            ["value" => 6, "label" => __("Bidding and multi purchasing")],
            ["value" => 7, "label" => __("Flowers & gifts")],
            ["value" => 8, "label" => __("Computers & software")],
            ["value" => 9, "label" => __("Health & beauty")],
            ["value" => 10, "label" => __("Personal services")],
            ["value" => 11, "label" => __("Professional services")],
            ["value" => 12, "label" => __("Sport")],
            ["value" => 13, "label" => __("Clothing & accessories")],
            ["value" => 14, "label" => __("Travel & tourism")],
            ["value" => 15, "label" => __("Hifi, photo & video")],
            ["value" => 16, "label" => __("Telephone & communication")]
        ];
    }
}

?>

