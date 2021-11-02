<?php

namespace Oander\SalesforceLoyalty\Plugin\Magento\Checkout\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magento\Customer\Model\Session\Proxy;
use Magento\Checkout\Model\Session as CheckoutSession;

class LoyaltyLayoutProcessor
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var
     */
    private $customerSession;

    public function __construct(
        Proxy           $customerSession,
        CheckoutSession $checkoutSession
    )
    {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(LayoutProcessor $subject, array $jsLayout)
    {
        if ($this->customerSession->isLoggedIn() && $this->checkoutSession->getQuote()->getLoyaltyPoint()) {
            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['loyalty_discount']
                = [
                'component' => "Oander_SalesforceLoyalty/js/view/checkout/cart/totals/loyaltydiscount",
                'config' => [
                    'template' => "Oander_SalesforceLoyalty/checkout/cart/totals/loyaltydiscount",
                    'title' => 'Loyalty Discount'
                ]
            ];
        }

        return $jsLayout;
    }
}