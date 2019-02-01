<?php


namespace Oander\ApplePay\Observer;

class CaptainHookJsEvent implements \Magento\Framework\Event\ObserverInterface
{

    const OUTPUT_NAME = 'applepay';

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $output = $observer->getData('output');
    }
}
