<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\SalesforceLoyalty\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    /**
     * @var Config
     */
    private $configHelper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Config $configHelper
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Oander\SalesforceLoyalty\Helper\Config $configHelper
    )
    {
        parent::__construct($context);
        $this->configHelper = $configHelper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param $quote \Magento\Quote\Model\Quote|null
     * @return float
     */
    public function getMaxRedeemablePoints($quote = null) : float
    {
        $maxPoints = 0.0;
        $quote = $this->_getQuote($quote);
        if($this->configHelper->getMaxPercent())
            $maxPoints = floatval($quote->getGrandTotal()) * (floatval($this->configHelper->getMaxPercent())/100);
        return $maxPoints;
    }

    /**
     * @param $quote \Magento\Quote\Model\Quote|null
     * @return int
     */
    public function getEarnableLoyaltyPoints($quote = null) : int
    {
        $earnablePoints = 0.0;
        $quote = $this->_getQuote($quote);
        foreach ($quote->getAllVisibleItems() as $item)
        {
            $regularPrice = $item->getProduct()->getPriceInfo()->getPrice('regular_price')->getValue();
            $itemPrice = floatval($item->getPrice());
            //Check is it not on sale
            if($itemPrice >= $regularPrice)
            {
                $earnablePoints += $this->configHelper->getPointValue() * $itemPrice;
            }
        }
        return (int)$earnablePoints;
    }

    private function _getQuote($quote = null)
    {
        if(is_null($quote))
        {
            $quote = $this->checkoutSession->getQuote();
        }
        return $quote;
    }
}