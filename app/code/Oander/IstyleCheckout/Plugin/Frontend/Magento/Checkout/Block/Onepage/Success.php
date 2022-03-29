<?php

namespace Oander\IstyleCheckout\Plugin\Frontend\Magento\Checkout\Block\Onepage;

class Success
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->checkoutSession = $checkoutSession;
    }

    public function before__call(
        \Magento\Checkout\Block\Onepage\Success $subject,
        $method,
        $args
    ) {
        if($method=="getCustomerFirstname") {
            $firstname = $subject->getData('customer_firstname');
            if (!$firstname) {
                $order = $this->checkoutSession->getLastRealOrder();
                $subject->setData('customer_firstname', $order->getBillingAddress()->getFirstname());
            }
        }
        return [$method, $args];
    }

    public function beforeGetCustomerFirstname(
        \Magento\Checkout\Block\Onepage\Success $subject,
        \Closure $proceed
    ) {
        $firstname = $subject->getData('customer_firstname');
        if(!$firstname) {
            $order = $this->checkoutSession->getLastRealOrder();
            $subject->setData('customer_firstname', $order->getBillingAddress()->getFirstname());
        }
    }
}