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
 * Oander_AjaxCaptianHook
 *
 * @author  Róbert Betlen <robert.betlen@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\IstyleCustomization\Observer;

use Magento\Framework\Event\ObserverInterface;
use Oander\IstyleCustomization\Observer\AjaxCaptainHookEvent;

/**
 * Class AjaxCaptianHookJsEvent
 * @package Oander\IstyleCustomization\Observer
 */
class AjaxCaptainHookJsEvent implements ObserverInterface
{
    const HAS_PRICE = 'window.productview.hasPrice';

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $output = $observer->getData('output');
        /**
         * @var $product \Magento\Catalog\Model\Product
         */
        $product = $observer->getData('product');

        if ($product->getTypeId() == 'simple') {
            $output->setData('dependences',
                array_merge($output->getData('dependences'),
                    ['priceUtils' => 'Magento_Catalog/js/price-utils']
                )
            );
            $output->setData('js',
                array_merge($output->getData('js'),
                    [AjaxCaptainHookEvent::OUTPUT_NAME =>
                        'if(response[\'' . AjaxCaptainHookEvent::OUTPUT_NAME . '\']!== undefined)
                        {
                            var finalPrice = response[\'' . AjaxCaptainHookEvent::OUTPUT_NAME . '\'].price;
                            var oldPrice = response[\'' . AjaxCaptainHookEvent::OUTPUT_NAME . '\'].oldprice;
                            var productInfoPrice = jQuery("#oander-product-info-price");
                            var stickyHeader = jQuery("#product-sticky-header");
                            
                            // Base price (product-info-right)
                            if (finalPrice < oldPrice) {
                                productInfoPrice.find("#old-price").show();
                                productInfoPrice.find("#final-price").removeClass("regular-price").addClass("special-price");
                            }
                            
                            productInfoPrice.find(\'#final-price\').html(priceUtils.formatPrice(finalPrice, {}));
                            productInfoPrice.find(\'#old-price\').html(priceUtils.formatPrice(oldPrice, {}));
                            
                            // Sticky header prices
                            if (jQuery("#product-sticky-header").length > 0) {
                                if (finalPrice < oldPrice) {
                                    stickyHeader.find("#sticky-old-price").show();
                                    stickyHeader.find("#sticky-final-price").removeClass("regular-price").addClass("special-price");                                
                                }
                                
                                stickyHeader.find(\'#sticky-final-price\').html(priceUtils.formatPrice(finalPrice, {}));
                                stickyHeader.find(\'#sticky-old-price\').html(priceUtils.formatPrice(oldPrice, {}));
                            }
                        }'
                    ]
                )
            );
        } elseif ($product->getTypeId() == 'bundle') {
        } else {
            $output->setData('dependences',
                array_merge($output->getData('dependences'),
                    [
                        '$ms' => 'Magento_Swatches/js/swatch-renderer',
                        '$os' => 'oanderSwatchRenderer'
                    ]
                )
            );

            $output->setData('js',
                array_merge($output->getData('js'),
                    [AjaxCaptainHookEvent::OUTPUT_NAME =>
                        'if(response[\'' . AjaxCaptainHookEvent::OUTPUT_NAME . '\'] !== undefined)
                        {
                             jQuery(\'[data-role=priceBox]\').data(\'magePriceBox\').setConfig(response[\'' . AjaxCaptainHookEvent::OUTPUT_NAME . '\'][\''.AjaxCaptainHookEvent::OUTPUT_NAME4.'\']);
                             jQuery(\'[data-role=swatch-options]\').data(\'mageOanderSwatchRenderer\').setConfig(response[\'' . AjaxCaptainHookEvent::OUTPUT_NAME . '\']);
                             ' . self::HAS_PRICE . ' = true;
                        }'
                    ]
                )
            );
        }

        $output->setData('commonjs',
            array_merge($output->getData('commonjs'),
                [AjaxCaptainHookEvent::OUTPUT_NAME => "window.productview=[];"]
            )
        );

        $output->setData('commonfunctionjs',
            array_merge($output->getData('commonfunctionjs'),
                [AjaxCaptainHookEvent::OUTPUT_NAME =>
                    "if(" . self::HAS_PRICE . "!== undefined)
                        {
                            otherdata.push('" . AjaxCaptainHookEvent::INPUT_DISABLEPRICE . "');
                        }"
                ]
            )
        );
    }
}
