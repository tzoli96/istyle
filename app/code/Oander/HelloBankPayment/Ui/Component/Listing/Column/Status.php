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
        return [
                ['value' => BaremInterface::STATUS_ENABLED, 'label' => __('Enable')],
                ['value' => BaremInterface::STATUS_DISABLED, 'label' => __('Disable')
            ]
        ];
    }

}