<?php
/**
 * Salesforce Loyalty module
 * Copyright (C) 2019
 *
 * This file is part of Oander/SalesforceLoyalty.
 *
 * Oander/SalesforceLoyalty is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Oander\SalesforceLoyalty\Block\Cart;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Oander\SalesforceLoyalty\Helper\Data;
use Oander\SalesforceLoyalty\Enum\CustomerAttribute;
use Oander\SalesforceLoyalty\Helper\Config;

class Loyaltypoints extends Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Oander\SalesforceLoyalty\Helper\Data
     */
    private $loyaltyHelper;
    /**
     * @var \Oander\SalesforceLoyalty\Helper\Salesforce
     */
    private $salesforceHelper;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;
    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @param Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param Data $loyaltyHelper
     * @param \Oander\SalesforceLoyalty\Helper\Salesforce $salesforceHelper
     * @param Data $helperData
     * @param Config $configHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context  $context,
        \Magento\Customer\Model\Session                   $customerSession,
        \Magento\Checkout\Model\Session                   $checkoutSession,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Oander\SalesforceLoyalty\Helper\Data             $loyaltyHelper,
        \Oander\SalesforceLoyalty\Helper\Salesforce       $salesforceHelper,
        Data                                              $helperData,
        Config                                            $configHelper,
        array                                             $data = []
    )
    {
        parent::__construct($context, $data);
        $this->priceCurrency = $priceCurrency;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->loyaltyHelper = $loyaltyHelper;
        $this->salesforceHelper = $salesforceHelper;
        $this->configHelper = $configHelper;
        $this->helperData = $helperData;
    }

    /**
     * @return bool
     */
    private function isLoggedIn()
    {
        if(!$this->configHelper->getLoyaltyServiceEnabled())
        {
            return false;
        }
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return bool
     */
    public function isItLoyaltyMember()
    {
        return ($this->isLoggedIn()) ? (bool)$this->customerSession->getCustomer()->getData(CustomerAttribute::REGISTERED_TO_LOYALTY) : false;
    }

    /**
     * @return string
     */
    public function getLoyaltyPostUrl()
    {
        return $this->getUrl(\Oander\SalesforceLoyalty\Controller\Checkout\LoyaltyPost::ROUTE);
    }

    /**
     * @return int
     */
    public function getMaxRedeemablePoints()
    {
        return $this->loyaltyHelper->getMaxRedeemablePoints();
    }

    /**
     * @return float|null
     */
    public function getLoyaltyDiscount()
    {
        return $this->checkoutSession->getQuote()->getData(\Oander\SalesforceLoyalty\Enum\Attribute::LOYALTY_DISCOUNT);
    }

    /**
     * @return string|null
     */
    public function getLoyaltyDiscountFormated()
    {
        if ($this->getLoyaltyDiscount())
            return $this->priceCurrency->format($this->getLoyaltyDiscount() * -1, false);
        else
            return null;
    }

    /**
     * @return string
     */
    public function getCartInfoText()
    {
        return $this->loyaltyHelper->getCartInfoText();
    }

    /**
     * @return false|int
     */
    public function getAvailablePoints()
    {
        try {
            return $this->salesforceHelper->getCustomerAffiliatePoints();
        } catch (LocalizedException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return string
     */
    public function getAvailablePointsCashConverted()
    {
        try {
            return $this->salesforceHelper->getCustomerAffiliatePointsCashConverted();
        } catch (LocalizedException $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param int $point
     * @return string
     */
    public function getFormatedPoint($point): string
    {
        return $this->loyaltyHelper->formatPoint($point);
    }
}