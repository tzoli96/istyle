<?php

namespace Oander\ExternalRoundingUnit\Plugin\Magento\Quote\Model\Quote\Address\Total;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\Grand as Subject;
use Oander\ExternalRoundingUnit\Enum\Attribute;
use Oander\ExternalRoundingUnit\Helper\Config;
use Oander\ExternalRoundingUnit\Enum\Config as EnumConfig;

class GrandRounding
{
    /**
     * @var Config
     */
    protected $helperConfig;

    /**
     * @param Config $helperConfig
     */
    public function __construct(
        Config $helperConfig
    )
    {
        $this->helperConfig = $helperConfig;
    }

    /**
     * @param Subject $subject
     * @param \Closure $proceed
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return mixed
     */
    public function aroundCollect(
        Subject                     $subject,
        \Closure                    $proceed,
        Quote                       $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total                       $total
    )
    {

        $result = $proceed($quote, $shippingAssignment, $total);
        if ($this->helperConfig->IsEnabled()) {
            $grandTotal = array_sum($total->getAllTotalAmounts());
            if ($grandTotal) {
                $roundTotalAmmount = $this->helperConfig->getRounding($grandTotal);
                if ($grandTotal > $roundTotalAmmount) {
                    $externalRoundingAmmount = $grandTotal - $roundTotalAmmount;
                    $operator = "-";
                } else {
                    $externalRoundingAmmount = $roundTotalAmmount - $grandTotal;
                    $operator = "";
                }

                $total->setData(EnumConfig::SALES_CODE, $operator . $this->helperConfig->getFormatNumber($externalRoundingAmmount));
                $total->setGrandTotal($roundTotalAmmount);
                $total->setBaseGrandTotal($roundTotalAmmount);
                $quote->setData(Attribute::EXTERNAL_ROUNDING_UNITE_QUOTE_ATTRIBUTE, $operator . $externalRoundingAmmount);
            }

        }

        return $result;
    }
}