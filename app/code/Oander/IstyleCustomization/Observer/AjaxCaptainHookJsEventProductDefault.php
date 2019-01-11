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
 * Oander_IstyleCustomization
 *
 * @author  János Pinczés <janos.pinczes.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\IstyleCustomization\Observer;

use Magento\Framework\Event\ObserverInterface;
use Oander\IstyleCustomization\Observer\AjaxCaptainHookEventProductDefault;

/**
 * Class AjaxCaptainHookJsEventProductDefault
 * @package Oander\IstyleCustomization\Observer
 */
class AjaxCaptainHookJsEventProductDefault implements ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $output = $observer->getData('output');

        $output->setData('js',
            array_merge($output->getData('js'),
                [AjaxCaptainHookEventProductDefault::OUTPUT_NAME =>
                    'if(response[\'' . AjaxCaptainHookEventProductDefault::OUTPUT_NAME . '\'] !== undefined)
                        {            
                             jQuery(\'[data-role=swatch-options]\').data(\'mageOanderSwatchRenderer\').setDefaultOption(response[\'' . AjaxCaptainHookEventProductDefault::OUTPUT_NAME . '\'].' . AjaxCaptainHookEventProductDefault::OUTPUT_NAME2 . ', response[\'' . AjaxCaptainHookEventProductDefault::OUTPUT_NAME . '\'].' . AjaxCaptainHookEventProductDefault::OUTPUT_NAME1 . ');
                        }'
                ]
            )
        );
    }
}