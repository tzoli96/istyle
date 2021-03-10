<?php
namespace Oander\HelloBankPayment\Ui\Component\Form\Field;

use Oander\HelloBankPayment\Api\Data\BaremInterface;
use Magento\Framework\Option\ArrayInterface;

class InstallmentsType implements ArrayInterface
{
    /**
     * @var null|array
     */
    protected $options;

    public function toOptionArray()
    {
            return [
                ['value' => BaremInterface::INSTALLMENTS_TYPE_FIXED, 'label' => __('Fixed')],
                ['value' => BaremInterface::INSTALLMENTS_TYPE_RANGE, 'label' => __('Range')]
            ];
    }
}