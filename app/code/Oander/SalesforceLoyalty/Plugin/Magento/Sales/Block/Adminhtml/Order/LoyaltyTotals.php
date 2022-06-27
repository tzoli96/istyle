<?php
namespace Oander\SalesforceLoyalty\Plugin\Magento\Sales\Block\Adminhtml\Order;

use Magento\Framework\DataObject;

class LoyaltyTotals
{
    /**
     * @param DataObject $subject
     * @param $result
     * @return string
     */
    public function afterGetTotals(DataObject $subject, $result )
    {
        if (intval($subject->getSource()->getLoyaltyDiscount()) > 0) {
            $subject->addTotal(new \Magento\Framework\DataObject(
                [
                    'code' => 'loyalty_discount',
                    'value' => $subject->getSource()->getLoyaltyDiscount(),
                    'base_value' => $subject->getSource()->getBaseLoyaltyDiscount(),
                    'label' => __('Loyalty Discount'),
                ]));
        }

        return $result;
    }
}
