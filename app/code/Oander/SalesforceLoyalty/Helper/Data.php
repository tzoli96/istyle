<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Oander\SalesforceLoyalty\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Registry;
use Oander\SalesforceLoyalty\Enum\Attribute;
use Magento\Store\Model\StoreManagerInterface;
use Oander\SalesforceLoyalty\Enum\CustomerAttribute;
use Magento\Customer\Model\Session as CustomerSession;
use Oander\SalesforceLoyalty\Enum\LoyaltyStatus as LoyaltyStatusEnum;

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
     * @return string
     */
    public function getCartInfoText()
    {
        return $this->configHelper->getCartInfo();
    }

    /**
     * @param $point float
     * @return float
     */
    public function convertPointToAmount($point)
    {
        return $this->configHelper->getPointValue() * $point;
    }

    /**
     * @param $point float
     * @return float
     */
    public function convertAmountToPoint($point)
    {
        if($this->configHelper->getPointValue())
            return $point / $this->configHelper->getPointValue();
        return 0.0;
    }

    /**
     * @param $quote \Magento\Quote\Model\Quote|null
     * @return int
     */
    public function getMaxRedeemablePoints($quote = null)
    {
        $quote = $this->_getQuote($quote);
        $calcTotal = floatval($quote->getShippingAddress()->getShippingInclTax()) + floatval($quote->getShippingAddress()->getSubtotalInclTax()) + floatval($quote->getShippingAddress()->getDiscountAmount());
        return $this->getMaxRedeemablePointsBySum($calcTotal);
    }

    public function getMaxRedeemablePointsBySum($calcTotal) {
        $maxPoints = 0.0;
        if($this->configHelper->getMaxPercent() && $this->configHelper->getPointValue()>0) {
            $maxSum = floatval($calcTotal) * (floatval($this->configHelper->getMaxPercent()) / 100);
            $maxPoints = $this->convertAmountToPoint($maxSum);
        }
        return round($maxPoints);
    }

    /**
     * @param $quote \Magento\Quote\Model\Quote|null
     * @return int
     */
    public function getEarnableLoyaltyPoints($quote = null)
    {
        $earnablePoints = 0.0;
        $quote = $this->_getQuote($quote);
        //If customer not using loyalty points calculate earnable points
        if(!($quote->getData(\Oander\SalesforceLoyalty\Enum\Attribute::LOYALTY_DISCOUNT)>0)) {
            foreach ($quote->getAllVisibleItems() as $item) {
                if($item->getChildren())
                {
                    foreach ($item->getChildren() as $childItem)
                    {
                        if($item->getProductType()=="configurable")
                        {
                            $earnablePoints += $this->_getAvailablePointsOfItemConfigurable($childItem);
                        }
                        else
                        {
                            $earnablePoints += $this->_getAvailablePointsOfItem($childItem);
                        }
                    }
                }
                else
                {
                    $earnablePoints += $this->_getAvailablePointsOfItem($item);
                }
            }
        }
        return round($earnablePoints);
    }

    /**
     * @param $item \Magento\Quote\Model\Quote\Item
     * @return float
     */
    private function _getAvailablePointsOfItem($item)
    {
        $loyaltyPointsPercent = floatval($item->getProduct()->getData(\Oander\SalesforceLoyalty\Enum\ProductAttribute::LOYALTY_POINTS_PERCENT));
        //Check has any value in loyalty points percent
        if ($loyaltyPointsPercent > 0) {
            $regularPrice = $item->getProduct()->getPriceInfo()->getPrice('regular_price')->getValue();
            $itemPrice = floatval($item->getPrice());
            //Check is it not on sale
            if ($itemPrice >= $regularPrice) {
                return ($item->getRowTotalInclTax() * ($loyaltyPointsPercent/100));
            }
        }
        return 0.0;
    }

    /**
     * @param $item \Magento\Quote\Model\Quote\Item
     * @return float
     */
    private function _getAvailablePointsOfItemConfigurable($item)
    {
        $loyaltyPointsPercent = floatval($item->getProduct()->getData(\Oander\SalesforceLoyalty\Enum\ProductAttribute::LOYALTY_POINTS_PERCENT));
        //Check has any value in loyalty points percent
        if ($loyaltyPointsPercent > 0) {
            $regularPrice = $item->getProduct()->getPriceInfo()->getPrice('regular_price')->getValue();
            $itemPrice = floatval($item->getParentItem()->getPrice());
            //Check is it not on sale
            if ($itemPrice >= $regularPrice) {
                return ($item->getParentItem()->getRowTotalInclTax() * ($loyaltyPointsPercent/100));
            }
        }
        return 0.0;
    }

    private function _getQuote($quote = null)
    {
        if(is_null($quote))
        {
            $quote = $this->checkoutSession->getQuote();
        }
        return $quote;
    }

    /**
     * @param int $amount
     * @return string
     */
    public function formatPoint(int $amount) : string
    {
        return number_format($amount,0," "," ");
    }
}