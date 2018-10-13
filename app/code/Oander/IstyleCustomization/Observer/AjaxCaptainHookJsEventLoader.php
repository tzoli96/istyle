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
 * Class AjaxCaptainHookJsEventLoader
 * @package Oander\IstyleCustomization\Observer
 */
class AjaxCaptainHookJsEventLoader implements ObserverInterface
{
    const HAS_PRICE = 'window.productview.hasPrice';

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $output = $observer->getData('output');

        $output->setData('commonfunctionjs',
            array_merge($output->getData('commonfunctionjs'),
                ['Loader' =>
                    'jQuery(".product-info-right").addClass("is-loading");'
                ]
            )
        );


        $output->setData('js',
            array_merge($output->getData('js'),
                ['Loader' =>
                    'jQuery(".product-info-right").removeClass("is-loading");'
                ]
            )
        );
    }
}
