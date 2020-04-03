<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Plugin\Reports\Observer;

/**
 * Class CatalogProductViewObserver
 * @package Oander\IstyleCustomization\Plugin\Reports\Observer
 */
class CatalogProductViewObserver
{
    /**
     * @param \Magento\Reports\Observer\CatalogProductViewObserver $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function aroundExecute(
        \Magento\Reports\Observer\CatalogProductViewObserver $subject,
        \Closure $proceed,
        \Magento\Framework\Event\Observer $observer
    ) {
        try {
            $proceed($observer);
        } catch (\Exception $exception) {
            /**
             * @TODO cron / queue try next time
             */
        }
    }
}
