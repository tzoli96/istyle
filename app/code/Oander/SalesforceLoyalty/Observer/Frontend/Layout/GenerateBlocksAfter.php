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

namespace Oander\SalesforceLoyalty\Observer\Frontend\Layout;

use Oander\SalesforceLoyalty\Helper\Config;

class GenerateBlocksAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Config
     */
    private $helperConfig;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    /**
     * @var \Oander\SalesforceLoyalty\Helper\Data
     */
    private $loyaltyHelper;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Oander\SalesforceLoyalty\Helper\Data $loyaltyHelper
     * @param Config $helperConfig
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Oander\SalesforceLoyalty\Helper\Data $loyaltyHelper,
        Config $helperConfig
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->loyaltyHelper = $loyaltyHelper;
        $this->helperConfig = $helperConfig;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $action = $observer->getData('full_action_name');
        if($action=="checkout_cart_index")
        {
            /** @var \Magento\Framework\View\LayoutInterface $layout */
            $layout = $observer->getData('layout');
            if(!($this->checkoutSession->getQuote()->getData(\Oander\SalesforceLoyalty\Enum\Attribute::LOYALTY_DISCOUNT)>0))
            {
                $earnablePoints = $this->loyaltyHelper->getEarnableLoyaltyPoints();
                if($earnablePoints>0 && $this->helperConfig->getLoyaltyServiceEnabled()) {
                    $earnableBlock = $layout->addBlock(\Magento\Framework\View\Element\Template::class, "salesforceloyalty.cart.methods.earnable", "checkout.cart.methods");
                    $earnableBlock->setTemplate("Oander_SalesforceLoyalty::cart/earnable.phtml");
                    $earnableBlock->setFormatedEarnablePoints(__("+ %1 points", $earnablePoints));
                    $layout->reorderChild("checkout.cart.methods", "salesforceloyalty.cart.methods.earnable", "checkout.cart.methods.onepage.bottom", false);
                }
            }
            $loyaltypoints = $layout->getBlock("salesforceloyalty.cart.loyaltypoints");
            if($loyaltypoints) {
                $parentName = $layout->getParentName("salesforceloyalty.cart.loyaltypoints");
                $checkoutCartCoupon = $layout->getBlock("checkout.cart.coupon");
                if($parentName && $checkoutCartCoupon)
                    $layout->reorderChild($parentName, "salesforceloyalty.cart.loyaltypoints", "checkout.cart.coupon", true);
            }
        }
    }
}