<?php

namespace Oander\IstyleCustomization\Plugin\Magento\Quote\Model;


class PaymentMethodManagement
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Payment\Model\MethodList
     */
    private $methodList;

    /**
     * PaymentMethodManagement constructor.
     * @param \Magento\Payment\Model\MethodList $methodList
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Payment\Model\MethodList $methodList,
        \Magento\Framework\Registry $registry
    )
    {
        $this->registry = $registry;
        $this->methodList = $methodList;
    }

    public function aroundGetList(
        \Magento\Quote\Model\PaymentMethodManagement $subject,
        \Closure $proceed,
        $cartId
    ) {
        $quote = $this->registry->registry("quote_" . $cartId);
        if(!$this->registry->registry("quote_" . $cartId)) {
            return $proceed($cartId);
        }
        else
        {
            return $this->methodList->getAvailableMethods($quote);
        }
    }
}