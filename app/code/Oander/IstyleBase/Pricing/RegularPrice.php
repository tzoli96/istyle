<?php

namespace Oander\IstyleBase\Pricing;

use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Oander\BundlePriceSwitcher\Enum\Link as LinkEnum;
use Oander\BundlePriceSwitcher\Helper\Config;

/**
 * Class RegularPrice
 */
class RegularPrice extends \Magento\Catalog\Pricing\Price\RegularPrice
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * RegularPrice constructor.
     *
     * @param SaleableInterface      $saleableItem
     * @param                        $quantity
     * @param CalculatorInterface    $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param Config                 $config
     */
    public function __construct(
        SaleableInterface $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        PriceCurrencyInterface $priceCurrency,
        Config $config
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->config = $config;
    }

    /**
     * Get price value
     *
     * @return float|bool
     */
    public function getValue()
    {
        if ($this->value === null) {
            if ($this->getProduct()->getData(LinkEnum::USE_REGULAR_PRICE) == 1 && $this->config->isEnabled()) {
                return parent::getValue();
            }

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
