<?php
namespace Oander\IstyleCustomization\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ParibasHandle implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        if($method->getCode() === "innobyte_bnpparibas")
        {
            $order->setData("paribas_pin",$payment->getAdditionalInformation("pin"));
            $order->save();
        }
    }
}