<?php

namespace Oander\SalesforceLoyalty\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class RegistrationType implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Store with loyalty term')],
            ['value' => 2, 'label' => __('Store with normal registration term')],

        ];
    }
}