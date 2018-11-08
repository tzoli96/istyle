<?php

namespace Oander\IstyleBase\Pricing;

/**
 * Class FinalPrice
 */
class FinalPrice extends \Magento\Catalog\Pricing\Price\FinalPrice
{

    /**
     * Get Value
     *
     * @return float|bool
     */
    public function getValue()
    {
        $finalPrice = max(0, $this->getBasePrice()->getValue());

        $price = $this->product->getPrice();
        $oldPrice = $this->product->getOldPrice();
        $finalPriceTmp = $this->product->getFinalPrice();

        if ((float)$oldPrice > (float)$price
            && (float)$finalPriceTmp === (float)$price
            && (float)$finalPrice === (float)$oldPrice
        ) {
            $finalPrice = $price;
        }

        return $finalPrice;
    }
}
