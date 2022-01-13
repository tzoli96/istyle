<?php

namespace Oander\OneyThreeByFourExtender\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class AjaxCaptainHookJsEvent
 * @package Oander\CartButton\Observer
 */
class AjaxCaptainHookJsEvent implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $output = $observer->getData('output');

        $output->setData('js',
            array_merge($output->getData('js'),
                [
                    \Oander\OneyThreeByFourExtender\Observer\AjaxCaptainHookEvent::OUTPUT_NAME_PRODUCT  =>
                        "$(\".oney-widget\").replaceWith(response['" . \Oander\OneyThreeByFourExtender\Observer\AjaxCaptainHookEvent::OUTPUT_NAME_PRODUCT  . "']);",
                    \Oander\OneyThreeByFourExtender\Observer\AjaxCaptainHookEvent::OUTPUT_NAME_SIMULATION  =>
                        "$(\".oney-popup\").replaceWith(response['" . \Oander\OneyThreeByFourExtender\Observer\AjaxCaptainHookEvent::OUTPUT_NAME_SIMULATION  . "']);"
                ]
            )
        );

        $output->setData('commonfunctionjs',
            array_merge($output->getData('commonfunctionjs'),
                ['bundle_selections' => "if(typeof window.bundle_selections !== 'undefined'){otherdata.push({bundle_selections: window.bundle_selections});}"]
            )
        );



    }
}