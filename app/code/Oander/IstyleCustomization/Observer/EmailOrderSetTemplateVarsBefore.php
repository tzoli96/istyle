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

        $dob = $shippingAddress->getDob();

        $order->setData('customer_dob',date('Y-m-d', strtotime((string)$dob)));
        $observer->getEvent()->setData('transport', $transport);
    }

}