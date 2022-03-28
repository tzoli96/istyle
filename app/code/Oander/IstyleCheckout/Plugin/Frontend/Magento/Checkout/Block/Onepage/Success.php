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

    public function around__call(
        \Magento\Checkout\Block\Onepage\Success $subject,
        \Closure $proceed,
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
    }

    public function aroundGetCustomerFirstname(
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