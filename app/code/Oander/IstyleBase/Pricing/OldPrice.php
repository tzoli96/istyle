<?php
/**
 *   /$$$$$$   /$$$$$$  /$$   /$$ /$$$$$$$  /$$$$$$$$ /$$$$$$$
 *  /$$__  $$ /$$__  $$| $$$ | $$| $$__  $$| $$_____/| $$__  $$
 * | $$  \ $$| $$  \ $$| $$$$| $$| $$  \ $$| $$      | $$  \ $$
 * | $$  | $$| $$$$$$$$| $$ $$ $$| $$  | $$| $$$$$   | $$$$$$$/
 * | $$  | $$| $$__  $$| $$  $$$$| $$  | $$| $$__/   | $$__  $$
 * | $$  | $$| $$  | $$| $$\  $$$| $$  | $$| $$      | $$  \ $$
 * |  $$$$$$/| $$  | $$| $$ \  $$| $$$$$$$/| $$$$$$$$| $$  | $$
 *  \______/ |__/  |__/|__/  \__/|_______/ |________/|__/  |__/
 *
 *                            ,-~.
 *                          :  .o \
 *                          `.   _/`.
 *                            `.  `. `.
 *                              `.  ` .`.
 *                                `.  ``.`.
 *                        _._.-. -._`.  `.``.
 *                    _.'            .`.  `. `.
 *                 _.'            )     \   '
 *               .'             _.          "
 *             .'.-.'._     _.-'            "
 *           ;'       _'-.-'              "
 *          ; _._.-.-;  `.,,_;  ,..,,,.:"
 *         %-'      `._.-'   \_/   :;;
 *                           | |
 *                           : :
 *                           | |
 *                           { }
 *                            \|
 *                            ||
 *                            ||
 *                            ||
 *                          _ ;; _
 *                         "-' ` -"
 *
 * Oander_IstyleBase
 *
 * @author  Gabor Kuti <gabor.kuti@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\IstyleBase\Pricing;

use Magento\Framework\Pricing\Price\AbstractPrice;

/**
 * Class OldPrice
 *
 * @package Oander\IstyleBase\Pricing
 */
class OldPrice extends AbstractPrice
{
    /**
     * Price type
     */
    const PRICE_CODE = 'old_price';

    /**
     * Get price value
     *
     * @return float|bool
     */
    public function getValue()
    {
        if ($this->value === null) {
            $price = $this->product->getOldPrice();
            $priceInCurrentCurrency = $this->priceCurrency->convertAndRound($price);
            $this->value = $priceInCurrentCurrency ? (float)$priceInCurrentCurrency : false;
        }
        return $this->value;
    }

    /**
     * overridden for exclude true
     *
     * @return mixed
     */
    public function getAmount()
    {
        if (!isset($this->amount[$this->getValue()])) {
            $this->amount[$this->getValue()] = $this->calculator->getAmount($this->getValue(), $this->getProduct(), true);
        }
        return $this->amount[$this->getValue()];
    }
}