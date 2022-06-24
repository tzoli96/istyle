<?php
namespace Oander\SalesforceLoyalty\Plugin\Magento\Sales\Block\Order;

use Magento\Framework\DataObject;

class LoyaltyTotals
{
    /**
     * @param DataObject $totals
     * @param \Closure $proceed
     * @param $total
     * @return string
     */
    public function aroundFormatValue(DataObject $totals, \Closure $proceed, $total )
    {
        $result = $proceed($total);
        if ($total->getCode() == "loyalty_discount") {
            return $totals->getOrder()->formatPrice($total->getValue() * -1);
        }
        return $result;
    }
}
