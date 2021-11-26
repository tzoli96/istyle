<?php

namespace Oney\ThreeByFour\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class OrderEmail implements ObserverInterface
{
    const PAYMENT_METHOD_CODES = ['oney_facilypay', 'facilypay_3x001', 'facilypay_4x001'];

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getData('order');
        if (in_array($order->getPayment()->getMethod(), self::PAYMENT_METHOD_CODES)) {
            $order->setCanSendNewEmailFlag(false);
        }
    }


}
