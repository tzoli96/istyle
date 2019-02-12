<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Oander\ApplePay\Plugin\Quote\Model\Quote;

class Address
{
    /**
     * @var \Oander\ApplePay\Helper\PaymentConfig
     */
    private $config;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;


    /**
     * CartButtonProviderDefault constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Oander\ApplePay\Helper\PaymentConfig $config
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Oander\ApplePay\Helper\PaymentConfig $config
        )
    {

        $this->config = $config;
        $this->request = $request;
    }


    public function aroundGetGroupedAllShippingRates(\Magento\Quote\Model\Quote\Address $subject, callable $proceed)
    {
        if($this->request->getParam('isApplePay',false)  && $this->config->isActive()) {
            $rates = $proceed();
            $ratesnew = [];
            $shippingMethods = $this->config->getEnabledShippingMethods();
            foreach ($rates as $code => $rate) {
                if (in_array($code, $shippingMethods)) {
                    $ratesnew[$code] = $rate;
                }
            }
            return $ratesnew;
        }
        return $proceed();
    }
}