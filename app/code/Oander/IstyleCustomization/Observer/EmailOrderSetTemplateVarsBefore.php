<?php

namespace Oander\IstyleCustomization\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;


/**
 * Class EmailOrderSetTemplateVarsBefore
 * @package Oander\IstyleCustomization\Observer
 */
class EmailOrderSetTemplateVarsBefore implements ObserverInterface
{

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $transport = $observer->getEvent()->getData('transport');
        $order = $transport->getOrder();
        $shippingAddress = $order->getShippingAddress();
        $order->setData('customer_dob',$shippingAddress->getDob());
        $observer->getEvent()->setData('transport', $transport);
    }

}