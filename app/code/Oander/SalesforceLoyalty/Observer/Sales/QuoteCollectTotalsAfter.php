<?php

namespace Oander\SalesforceLoyalty\Observer\Sales;

class QuoteCollectTotalsAfter implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getData('quote');
        $discountAmount = $quote->getShippingAddress()->getData('loyalty_discount_amount');
        $quote->setData(\Oander\SalesforceLoyalty\Enum\Attribute::LOYALTY_DISCOUNT, $discountAmount * -1);
        if(!($discountAmount<0) && $quote->getData(\Oander\SalesforceLoyalty\Enum\Attribute::LOYALTY_POINT)>0) {
            $quote->setData(\Oander\SalesforceLoyalty\Enum\Attribute::LOYALTY_POINT, 0);
        }
    }
}