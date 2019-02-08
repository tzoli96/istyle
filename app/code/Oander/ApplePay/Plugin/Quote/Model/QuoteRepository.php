<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Oander\ApplePay\Plugin\Quote\Model;

use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\App\Area;

class QuoteRepository
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


    public function aroundGetActive(\Magento\Quote\Model\QuoteRepository $subject, callable $proceed, $cartId, array $sharedStoreIds = [])
    {
        if($this->request->getParam('isApplePay',false) && $this->config->isActive()){
            $quote = $subject->get($cartId, $sharedStoreIds);
            return $quote;
        }
        return $proceed($cartId, $sharedStoreIds);
    }
}
