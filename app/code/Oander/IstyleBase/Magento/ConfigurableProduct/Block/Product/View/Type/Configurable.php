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

namespace Oander\IstyleBase\Magento\ConfigurableProduct\Block\Product\View\Type;

/**
 * Class Configurable
 *
 * @package Oander\IstyleBase\Magento\ConfigurableProduct\Block\Product\View\Type
 */
class Configurable extends \Oander\ConfigurableProductAttribute\Magento\Swatches\Block\Product\Renderer\Configurable
{
    /**
     * @return array
     */
    protected function getOptionPrices()
    {
        $prices = [];
        foreach ($this->getAllowProducts() as $product) {
            $priceInfo = $product->getPriceInfo();
            $oldPrice = $priceInfo->getPrice('old_price')->getAmount()->getValue();

            $prices[$product->getId()] =
                [
                    'oldPrice' => [
                        'amount' => $this->_registerJsPrice(
                            $oldPrice ?: $priceInfo->getPrice('regular_price')->getAmount()->getValue()
                        ),
                    ],
                    'basePrice' => [
                        'amount' => $this->_registerJsPrice(
                            $priceInfo->getPrice('final_price')->getAmount()->getBaseAmount()
                        ),
                    ],
                    'finalPrice' => [
                        'amount' => $this->_registerJsPrice(
                            $priceInfo->getPrice('final_price')->getAmount()->getValue()
                        ),
                    ]
                ];
        }
        return $prices;
    }
}