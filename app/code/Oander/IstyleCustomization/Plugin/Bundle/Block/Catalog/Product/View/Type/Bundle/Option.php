<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Plugin\Bundle\Block\Catalog\Product\View\Type\Bundle;

/**
 * Bundle option renderer
 */
class Option
{
    /**
     * Get title price for selection product
     *
     * @param \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject
     * @param callable                                                      $proceed
     * @param                                                               $selection
     * @param bool                                                          $includeContainer
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetSelectionTitlePrice(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject,
        callable $proceed,
        $selection,
        $includeContainer = true
    ) {
        $price = $subject->getProduct()->getPriceInfo()->getPrice('bundle_option');
        $amount = $price->getOptionSelectionAmount($selection);
        $regularPrice = $selection->getPriceInfo()->getPrice('regular_price');
        $regularAmount = $regularPrice->getAmount();

        $priceTitle = '<span class="product-name">' . $subject->escapeHtml($selection->getName()) . '</span>';
        $priceTitle .= ' &nbsp; ' . ($includeContainer ? '<span class="price-notice">' : '') . '+'
            . $this->renderPriceString($subject, $selection, $includeContainer, $price,
                $amount) . ($includeContainer ? '</span>' : '');

        if ($amount->getValue() < $regularAmount->getValue()) {
            $priceTitle .= ' &nbsp; ' . ($includeContainer ? '<span data-price-type="oldPrice" class="old-price">' : '')
                . $this->renderRegularPriceString($subject, $selection, $regularPrice, $regularAmount,
                    $includeContainer) . ($includeContainer ? '</span>' : '');
        }

        return $priceTitle;
    }

    /**
     * Format price string
     *
     * @param      $subject
     * @param      $selection
     * @param bool $includeContainer
     * @param null $price
     * @param null $amount
     *
     * @return mixed
     */
    public function renderPriceString($subject, $selection, $includeContainer = true, $price = null, $amount = null)
    {
        if ($price === null || $amount === null) {
            /** @var \Magento\Bundle\Pricing\Price\BundleOptionPrice $price */
            $price = $subject->getProduct()->getPriceInfo()->getPrice('bundle_option');
            $amount = $price->getOptionSelectionAmount($selection);
        }

        $priceHtml = $subject->getLayout()->getBlock('product.price.render.default')->renderAmount(
            $amount,
            $price,
            $selection,
            [
                'include_container' => $includeContainer
            ]
        );

        return $priceHtml;
    }

    /**
     * Format price string
     *
     * @param      $subject
     * @param      $selection
     * @param      $price
     * @param      $amount
     * @param bool $includeContainer
     *
     * @return mixed
     */
    public function renderRegularPriceString($subject, $selection, $price, $amount, $includeContainer = true)
    {
        $priceHtml = $subject->getLayout()->getBlock('product.price.render.default')->renderAmount(
            $amount,
            $price,
            $selection,
            [
                'include_container' => $includeContainer
            ]
        );

        return $priceHtml;
    }
}
