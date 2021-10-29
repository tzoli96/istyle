<?php

namespace Oney\ThreeByFour\Model\Config\Source;

class Environment implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value'=> 0, "label" => __("Preproduction")],
            ['value'=> 1, "label" => __("Production")]
        ];
    }
}
