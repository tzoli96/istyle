<?php

namespace Oander\ExternalRoundingUnit\Plugin\Magento\Checkout\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magento\Customer\Model\Session\Proxy;
use Magento\Checkout\Model\Session as CheckoutSession;
use Oander\ExternalRoundingUnit\Helper\Config;

class ExternalRoundingUnitLayoutProcessor
{
    /**
     * @var Config
     */
    private $helperConfig;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var Proxy
     */
    private $customerSession;

    /**
     * @param Proxy $customerSession
     * @param CheckoutSession $checkoutSession
     * @param Config $helperConfig
     */
    public function __construct(
        Proxy           $customerSession,
        CheckoutSession $checkoutSession,
        Config          $helperConfig
    )
    {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->helperConfig = $helperConfig;
    }

    /**
     * @param LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(LayoutProcessor $subject, array $jsLayout)
    {

        if ($this->helperConfig->IsEnabled()) {
            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['children']['totals']['children']['external_rounding']
                = [
                'component' => "Oander_ExternalRoundingUnit/js/view/checkout/cart/totals/external_rounding",
                'sortOrder' => "100",
                'config' => [
                    'template' => "Oander_ExternalRoundingUnit/checkout/cart/totals/external_rounding",
                    'title' => 'External Rounding'
                ]
            ];
        }

        return $jsLayout;
    }
}