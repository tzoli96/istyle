<?php

namespace Oander\IstyleBase\Pricing;

/**
 * Class RegularPrice
 */
class RegularPrice extends \Magento\Catalog\Pricing\Price\RegularPrice
{

    /**
     * Get price value
     *
     * @return float|bool
     */
    public function getValue()
    {
        if ($this->value === null) {
            $price = $this->product->getPrice();
            $oldPrice = $this->product->getOldPrice();
            $finalPrice = $this->product->getFinalPrice();

            if ((float)$oldPrice > (float)$price
                && ((float)$oldPrice < (float)$finalPrice || (float)$finalPrice === (float)$price)
            ) {
                $price = $oldPrice;
            }

            $priceInCurrentCurrency = $this->priceCurrency->convertAndRound($price);
            $this->value = $priceInCurrentCurrency ? floatval($priceInCurrentCurrency) : false;

        }
        return $this->value;
    }
}
