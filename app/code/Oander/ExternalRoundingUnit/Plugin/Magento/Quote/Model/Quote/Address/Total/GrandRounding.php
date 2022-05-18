<?php

namespace Oander\ExternalRoundingUnit\Plugin\Magento\Quote\Model\Quote\Address\Total;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\Grand as Subject;
use Oander\ExternalRoundingUnit\Helper\Config;

class GrandRounding
{
    const EXTERNAL_ROUNDING_CODE = "external_rounding";
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
            $roundTotalAmmount = $this->helperConfig->getRounding($grandTotal);
            if ($grandTotal > $roundTotalAmmount) {
                $externalRoundingAmmount = $grandTotal - $roundTotalAmmount;
                $operator = "-";
            } else {
                $externalRoundingAmmount = $roundTotalAmmount - $grandTotal;
                $operator = "";
            }

            $total->setData(self::EXTERNAL_ROUNDING_CODE, $operator . $this->helperConfig->getFormatNumber($externalRoundingAmmount));
            $total->setGrandTotal($roundTotalAmmount);
            $total->setBaseGrandTotal($roundTotalAmmount);

        }

        return $result;
    }
}