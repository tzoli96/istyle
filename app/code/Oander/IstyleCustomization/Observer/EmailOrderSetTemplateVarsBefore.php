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
        $transport['paribas_pin'] = $transport->getOrder()->getData("paribas_pin");
        $dob =  null;
        if ($shippingAddress->getDob()) {
            $dob = date('Y-m-d', strtotime((string)$shippingAddress->getDob()));
        }

        $order->setData('customer_dob', $dob);
        $observer->getEvent()->setData('transport', $transport);
    }

}