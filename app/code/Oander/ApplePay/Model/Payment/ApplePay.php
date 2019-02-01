<?php


namespace Oander\ApplePay\Model\Payment;

class ApplePay extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code = "applepay";
    protected $_isOffline = true;

    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null
    ) {
        return parent::isAvailable($quote);
    }
}
