<?php
/**
 * Apesyto Sales
 * Copyright (C) 2019
 *
 * This file included in Oander/Autorelated is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Oander\Autorelated\Plugin\Backend\Magento\Framework\Reflection;

class DataObjectProcessor
{

    public function beforeBuildOutputDataArray(
        \Magento\Framework\Reflection\DataObjectProcessor $subject,
        $dataObject,
        $dataObjectType
    ) {
        if($dataObjectType==\Aheadworks\Autorelated\Api\Data\RuleInterface::class)
        {
            $dataObjectType = \Oander\Autorelated\Api\Data\RuleInterface::class;
        }
        return [$dataObject,$dataObjectType];
    }
}