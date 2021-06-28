<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Oander\IstyleCheckout\Block\Cart;

use Magento\Framework\Pricing\Helper\Data;

class Coupon extends \Magento\Checkout\Block\Cart\AbstractCart
{
    protected $priceHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        Data $priceHelper,
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->_isScopePrivate = true;
        $this->priceHelper = $priceHelper;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getCouponCode()
    {
        return $this->getQuote()->getCouponCode();
    }

    /**
     * Get formatted price
     * @param price Number
     * @return float
     */
    private function getFormattedPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }

    /**
     * Get pure value
     * @return float
     */
    public function getPureValue()
    {
        return $this->getFormattedPrice(($this->getQuote()->getBaseSubtotalWithDiscount() - $this->getQuote()->getBaseSubtotal()));
    }
}
