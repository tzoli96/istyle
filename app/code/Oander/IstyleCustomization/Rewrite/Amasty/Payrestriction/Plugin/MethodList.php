<?php

namespace Oander\IstyleCustomization\Rewrite\Amasty\Payrestriction\Plugin;
use Magento\Payment\Helper\Data;

class MethodList
{
    /**
     * @var Data
     */
    protected $paymentHelper;

    /**
     * @param Data $paymentHelper
     *
     */
    public function __construct(
        Data $paymentHelper
    )
    {
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * Fix for Amasty and M2 2.1.3 Update:
     * @param \Magento\Payment\Model\MethodList $subject
     * @param \Closure $proceed
     */

    public function aroundGetAvailableMethods(
        \Magento\Payment\Model\MethodList $subject,
        \Closure $proceed,
        \Magento\Quote\Api\Data\CartInterface $quote
    )
    {
        $paymentMethods = $proceed($quote);
        $store = $quote ? $quote->getStoreId() : null;
        $storePaymentMethods = $this->paymentHelper->getStoreMethods($store, $quote);
        $storePaymentMethods = $this->addOneyMethod($paymentMethods,$storePaymentMethods);
        $retArray = array();
        foreach ($storePaymentMethods as $method) {
            foreach ($paymentMethods as $methodFromList) {
                if ($method->getCode() == $methodFromList->getCode()) {
                    $retArray[] = $method;
                }
            }
        }
        return $retArray;

    }

    private function addOneyMethod($paymentMethods,$fillteredPaymentMethods)
    {
        $oneyCodes = [
            'facilypay_4x001',
            'facilypay_3x001'
        ];

        foreach ($paymentMethods as $paymentMethod)
        {
            if(in_array($paymentMethod->getCode(),$oneyCodes))
            {
                $fillteredPaymentMethods[] = $paymentMethod;
            }
        }
        return $fillteredPaymentMethods;
    }
}