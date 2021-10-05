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

namespace Oander\SalesforceLoyalty\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Inspection\Exception;
use Oander\SalesforceLoyalty\Enum\Attribute;
use Oander\SalesforceLoyalty\Helper\Data;
use Oander\SalesforceLoyalty\Helper\Config;

class ModelServiceQuoteSubmitBefore implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $loyaltyHelper;
    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @param Data $loyaltyHelper
     * @param Config $configHelper
     */
    public function __construct(
        Data   $loyaltyHelper,
        Config $configHelper
    )
    {
        $this->loyaltyHelper = $loyaltyHelper;
        $this->configHelper = $configHelper;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
    )
    {
        /** @var Quote $quote */
        $quote = $observer->getData('quote');
        /** @var Order $order */
        $order = $observer->getData('order');

        if ($this->configHelper->isSpendingEnabled() &&
            ($this->loyaltyHelper->getMaxRedeemablePoints($quote) >= $quote->getData(Attribute::LOYALTY_POINT)))
        {
            $order->setData(
                Attribute::LOYALTY_DISCOUNT,
                $quote->getData(Attribute::LOYALTY_DISCOUNT)
            );
            $order->setData(
                Attribute::LOYALTY_POINT,
                $quote->getData(Attribute::LOYALTY_POINT)
            );
        }

    }
}