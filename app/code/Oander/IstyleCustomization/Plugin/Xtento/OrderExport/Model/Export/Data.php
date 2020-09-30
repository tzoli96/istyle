<?php

namespace Oander\IstyleCustomization\Plugin\Xtento\OrderExport\Model\Export;

/**
 * Class Data
 * @package Oander\IstyleCustomization\Plugin\Xtento\OrderExport\Model\Export
 */
class Data
{
    public function afterGetExportData(\Xtento\OrderExport\Model\Export\Data $subject, $result)
    {
        if (isset($result['items']) && is_array($result['items'])) {
            foreach ($result['items'] as $key => $item) {
                if (isset($item['product_attributes'])) {
                    if (isset($item['product_attributes']['external_stock_disable'])) {
                        $result['items'][$key]['product_attributes']['external_stock_disable'] = (int)$item['product_attributes']['external_stock_disable'];
                    } else {
                        $result['items'][$key]['product_attributes']['external_stock_disable'] = 0;
                    }
                }
            }
        }

        return $result;
    }
}