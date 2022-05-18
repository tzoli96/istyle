<?php

namespace Oander\ExternalRoundingUnit\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Oander\ExternalRoundingUnit\Enum\Attribute;
use Oander\ExternalRoundingUnit\Helper\Config;

class ModelServiceQuoteSubmitBefore implements ObserverInterface
{
    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @param Config $configHelper
     */
    public function __construct(
        Config $configHelper
    )
    {
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
        if ($this->configHelper->IsEnabled()) {
            /** @var Quote $quote */
            $quote = $observer->getData('quote');
            /** @var Order $order */
            $order = $observer->getData('order');

            $order->setData(
                Attribute::EXTERNAL_ROUNDING_UNITE_ORDER_ATTRIBUTE,
                $quote->getData(Attribute::EXTERNAL_ROUNDING_UNITE_QUOTE_ATTRIBUTE)
            );
        }
    }
}