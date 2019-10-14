<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\IstyleCustomization\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class GetBundleProductStock
 * @package Oander\BundleIgnoreStockQuantity\Observer
 */
class GetBundleProductAttributes implements ObserverInterface
{
    /**
     * @var \Oander\BundlePriceSwitcher\Helper\Selection
     */
    protected $selectionHelper;

    /**
     * GetBundleProductAttributes constructor.
     * @param \Oander\BundlePriceSwitcher\Helper\Selection $selectionHelper
     */
    public function __construct(
        \Oander\BundlePriceSwitcher\Helper\Selection $selectionHelper
    ) {
        $this->selectionHelper = $selectionHelper;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getData('product');
        $productAttributes = $observer->getData('product_attributes');
        $isDefaultSelections = $observer->getData('default_selections');
        $defaultSelections = $this->selectionHelper->getDefaultSelectionIds($product);
        $options = $product->getTypeInstance(true)->getOptions($product);


        foreach ($productAttributes as $optionId => $productId) {
            if (array_key_exists($optionId, $defaultSelections)
                && array_key_exists($optionId, $options)
                && $options[$optionId]->getBaseOption() == "0"
                && $isDefaultSelections
            ) {
               $optionsSelections = $options[$optionId]->getSelections();
               if (!empty($optionsSelections)) {
                   foreach ($optionsSelections as $optionsSelection) {
                       if ($optionsSelection->getSelectionId() != $defaultSelections[$optionId]
                           && $optionsSelection->getProductId() == $productId
                           && $optionsSelection->getIsDefault() == "0"
                       ) {
                           $isDefaultSelections = false;
                           break;
                       }
                   }
               }
            }
        }

        $observer->setData('default_selections', $isDefaultSelections);
    }
}