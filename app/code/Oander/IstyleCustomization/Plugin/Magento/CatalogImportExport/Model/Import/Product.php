<?php
/**
 * A Magento 2 module named Oander/IstyleCustomization
 * Copyright (C) 2019
 *
 * This file included in Oander/IstyleCustomization is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Oander\IstyleCustomization\Plugin\Magento\CatalogImportExport\Model\Import;

class Product
{
    protected $_importkey = [];
    public function afterValidateData(
        \Magento\CatalogImportExport\Model\Import\Product $subject,
        $result
    ) {
        try{
            $source = $subject->getSource();
            $source->rewind();
            while ($source->valid()) {
                try {
                    $rowData = $source->current();
                    $storecode = isset($rowData["store_view_code"])?$rowData["store_view_code"]:"";
                    $sku = isset($rowData["sku"])?strtoupper($rowData["sku"]):"";
                    if(isset($this->_importkey[$storecode . "_" . $sku]))
                    {
                        $subject->addRowError("Duplicated row for storecode " . $storecode . " and sku " . $sku, $source->key());
                    }
                    else
                    {
                        $this->_importkey[$storecode . "_" . $sku] = 1;
                    }
                } catch (\InvalidArgumentException $e) {
                }
                $source->next();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e)
        {
            $subject->addRowError($e->getMessage(), 0);
        }
        return $result;
    }
}