<?php

namespace Oney\ThreeByFour\Model\Config\Source;

class ShowHide implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['value'=> 0, "label" => __("Hide")],
            ['value'=> 1, "label" => __("Show")]
        ];
    }
}
