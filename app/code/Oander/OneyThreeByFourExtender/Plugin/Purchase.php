<?php

namespace Oander\OneyThreeByFourExtender\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Oney\ThreeByFour\Model\Api\Payment\Purchase as Subject;

class Purchase
{
    /**
     * @param Subject $subject
     * @param $order
     * @return array
     */
    public function beforePurchase(Subject $subject, $order)
    {
        // Ha nem mentjük el az orderben nem fog változni csak a változóba :3
        $order->getBillingAddress()->setPostcode(999999);
        $order->getShippingAddress()->setPostcode(999999);
        return [$order];
    }
}