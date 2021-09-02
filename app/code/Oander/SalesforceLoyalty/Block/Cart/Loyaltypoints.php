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

class Loyaltypoints extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Oander\SalesforceLoyalty\Helper\Data
     */
    private $loyaltyHelper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var \Oander\SalesforceLoyalty\Helper\Salesforce
     */
    private $salesforceHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Oander\SalesforceLoyalty\Helper\Data $loyaltyHelper
     * @param \Oander\SalesforceLoyalty\Helper\Salesforce $salesforceHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Oander\SalesforceLoyalty\Helper\Data $loyaltyHelper,
        \Oander\SalesforceLoyalty\Helper\Salesforce $salesforceHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->loyaltyHelper = $loyaltyHelper;
        $this->checkoutSession = $checkoutSession;
        $this->salesforceHelper = $salesforceHelper;
    }

    public function getLoyaltyPostUrl()
    {
        return $this->getUrl(\Oander\SalesforceLoyalty\Controller\Checkout\LoyaltyPost::ROUTE);
    }

    public function getMaxRedeemablePoints()
    {
        return $this->loyaltyHelper->getMaxRedeemablePoints();
    }

    public function getUsedPoints()
    {
        return $this->checkoutSession->getQuote()->getLoyaltyDiscount();
    }

    /**
     * @return string
     */
    public function getAvailablePoints()
    {
        return $this->salesforceHelper->getCustomerAffiliatePoints();
    }

    /**
     * @return string
     */
    public function getAvailablePointsInCash()
    {
        return $this->salesforceHelper->getCustomerAffiliatePoints();
    }
}