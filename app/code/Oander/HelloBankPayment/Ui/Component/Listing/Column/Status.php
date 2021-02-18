<?php
namespace Oander\HelloBankPayment\Ui\Component\Listing\Column;

use Oander\HelloBankPayment\Api\Data\BaremInterface;
use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        $options = [
            0 => [
                'label' => __('Disable'),
                'value' => BaremInterface::STATUS_DISABLED
            ],
            1  => [
                'label' => __('Enable'),
                'value' => BaremInterface::STATUS_ENABLED
            ]
        ];

        return $options;
    }

}