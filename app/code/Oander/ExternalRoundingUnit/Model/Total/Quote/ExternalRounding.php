<?php

namespace Oander\ExternalRoundingUnit\Model\Total\Quote;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Oander\ExternalRoundingUnit\Helper\Config;
use Oander\ExternalRoundingUnit\Enum\Config as EnumConfig;

class ExternalRounding extends AbstractTotal
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param Config $configHelper
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        Config                 $configHelper
    )
    {
        $this->priceCurrency = $priceCurrency;
        $this->configHelper = $configHelper;
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this|ExternalRounding
     */
    public function collect(
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total
    )
    {
        parent::collect($quote, $shippingAssignment, $total);
        return $this;
    }

    /**
     * @param Quote $quote
     * @param Total $total
     * @return array|null
     */
    public function fetch(Quote $quote, Total $total)
    {
        $result = null;
        $amount = $total->getData(EnumConfig::SALES_CODE);

        if ($amount) {
            $result = [
                'code' => $this->getCode(),
                'title' => __('External Rounding'),
                'value' => $amount
            ];
        }
        return $result;
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getLabel()
    {
        return __('External Rounding');
    }
}