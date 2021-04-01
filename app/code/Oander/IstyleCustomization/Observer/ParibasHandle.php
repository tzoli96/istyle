<?php
namespace Oander\IstyleCustomization\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Oander\IstyleCustomization\Enum\OrderAttributeEnum;

class ParibasHandle implements ObserverInterface
{

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $payment = $order->getPayment();
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("mukodik");
        $logger->info(print_r( $payment->getAdditionalInformation(),true));

    }
}