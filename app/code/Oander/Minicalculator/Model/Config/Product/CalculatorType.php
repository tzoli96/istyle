<?php

namespace Oander\Minicalculator\Model\Config\Product;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class CalculatorType extends AbstractSource
{
    /**
     * @return array|null
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => 'hellobank',
                'label' => __('HelloBank'),
            ],     [
                'value' => 'cetelem',
                'label' => __('Cetelem'),
            ],
        ];
    }
}