<?php

namespace Oander\OneyThreeByFourExtender\Helper;

use Magento\Framework\App\Helper\Context;
use Oander\BundlePriceSwitcher\Helper\Selection;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Selection
     */
    protected $selection;

    /**
     * @param Selection $selection
     * @param Context $context
     */
    public function __construct(
        Selection $selection,
        Context $context
    ) {
        parent::__construct($context);
        $this->selection = $selection;
    }

    /**
     * @param $product
     * @param $params
     * @return mixed
     */
    public function getProductFinalPrice($product, $params = [])
    {
        $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getValue();

        if ($product->getTypeId() == 'bundle') {
            if (!empty($params)) {
                $optionInfo = $this->getOptionInfo($params);
                $finalPrice = 0;

                $product->addCustomOption('bundle_option_ids',serialize(array_keys($optionInfo)),$product);
                $product->addCustomOption('bundle_selection_ids',serialize($optionInfo),$product);
                foreach ($optionInfo as $optionId => $selectionId) {
                    $selection = $this->selection->getSelection($product->getId(), $optionId, $selectionId);
                    $optionPriceAmount = $product->getPriceInfo()
                        ->getPrice('bundle_option')
                        ->getOptionSelectionAmount($selection);

                    $finalPrice += $optionPriceAmount->getValue();
                }
            } else {
                $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();
            }
        }

        return $finalPrice;
    }

    /**
     * @param $params
     * @return array
     */
    protected function getOptionInfo($params)
    {
        $optionInfo = [];
        if (isset($params['data'])) {
            foreach ($params['data'] as $data) {
                if (isset($data['bundle_selections'])) {
                    foreach ($data['bundle_selections'] as $optionId => $selectionId) {
                        $optionInfo[$optionId] = $selectionId;
                    }
                }
            }
        }

        return $optionInfo;
    }
}