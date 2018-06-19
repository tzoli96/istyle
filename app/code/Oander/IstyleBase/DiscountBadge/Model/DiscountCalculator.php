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

namespace Oander\IstyleBase\DiscountBadge\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Oander\DiscountBadge\Model\Source\Rules;
use Oander\IstyleBase\Pricing\OldPrice;

/**
 * Class DiscountCalculator
 *
 * @package Oander\IstyleBase\DiscountBadge\Model
 */
class DiscountCalculator extends \Oander\DiscountBadge\Model\DiscountCalculator
{
    /**
     * @param Product $product
     *
     * @return array
     */
    protected function getAppliedBadgeRules(Product $product)
    {
        $finalPrice = $product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();
        $regularPrice = $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getValue();
        $oldPrice = $product->getPriceInfo()->getPrice(OldPrice::PRICE_CODE)->getValue();
        $regularPrice = $oldPrice && $regularPrice !== $oldPrice ? $oldPrice : $regularPrice;
        $catalogRulePrice = $this->getCatalogRulePrice($product);
        $rules = [];

        if ($finalPrice > -1
            && ($finalPrice <= $catalogRulePrice || $catalogRulePrice == -1)
        ) {
            $this->setDiscountPercent($product->getId(), $regularPrice, $finalPrice);
            if ($this->getDiscountPercent($product->getId())) {
                $rules[Rules::SPECIAL_PRICE] = true;
            }
        }

        if ($catalogRulePrice > -1
            && $catalogRulePrice <= $finalPrice
        ) {
            $this->setDiscountPercent($product->getId(), $regularPrice, $catalogRulePrice);
            if ($this->getDiscountPercent($product->getId())) {
                $rules[Rules::CATALOG_PRICE] = true;
            }
        }

        return $rules;
    }
}