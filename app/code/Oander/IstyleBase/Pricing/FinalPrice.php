<?php

namespace Oander\IstyleBase\Pricing;

use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Oander\BundlePriceSwitcher\Enum\Link as LinkEnum;
use Oander\BundlePriceSwitcher\Helper\Config;

/**
 * Class FinalPrice
 */
class FinalPrice extends \Magento\Catalog\Pricing\Price\FinalPrice
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * FinalPrice constructor.
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
     * Get Value
     *
     * @return float|bool
     */
    public function getValue()
    {
        if ($this->getProduct()->getData(LinkEnum::USE_REGULAR_PRICE) == 1 && $this->config->isEnabled()) {
            return parent::getValue();
        }

        $finalPrice = max(0, $this->getBasePrice()->getValue());

        $price = $this->product->getPrice();
        $oldPrice = $this->product->getOldPrice();
        $finalPriceTmp = $this->product->getFinalPrice();

        if ((float)$oldPrice > (float)$price
            && (float)$finalPriceTmp === (float)$price
        ) {
            $finalPrice = $price;
        }

        return $finalPrice;
    }
}
