<?php

namespace Oander\OneyThreeByFourExtender\Plugin;

use Oney\ThreeByFour\Model\Api\Payment\Purchase as Subject;

class Purchase
{
    /**
     * @param Subject $subject
     * @param callable $proceed
     * @param $order
     * @return mixed
     */
    public function aroundPurchase(Subject $subject, callable $proceed, $order)
    {
        $originalShippingPostCode = $order->getShippingAddress()->getPostCode();
        $originalBillingPostCode = $order->getBillingAddress()->getPostCode();
        $order->getBillingAddress()->setPostcode(999999);
        $order->getShippingAddress()->setPostcode(999999);
        $result = $proceed();
        $order->getBillingAddress()->setPostcode($originalBillingPostCode);
        $order->getShippingAddress()->setPostcode($originalShippingPostCode);

        return $result;

    }
}