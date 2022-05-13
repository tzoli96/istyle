<?php

namespace Oander\ExternalRoundingUnit\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class RoundingRules implements ArrayInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => '5,00'],
            ['value' => 2, 'label' => '0,5'],
            ['value' => 3, 'label' => '0,000'],
            ['value' => 4, 'label' => '1,0'],
            ['value' => 5, 'label' => '0.01'],
            ['value' => 6, 'label' => '0.1'],
        ];
    }
}