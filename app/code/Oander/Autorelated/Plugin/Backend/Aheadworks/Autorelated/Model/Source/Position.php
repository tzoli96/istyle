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

namespace Oander\Autorelated\Plugin\Backend\Aheadworks\Autorelated\Model\Source;

class Position
{

    public function afterToOptionArray(
        \Aheadworks\Autorelated\Model\Source\Position $subject,
        $result
    ) {
        $result[] = [
            'value' => \Oander\Autorelated\Enum\BlockPosition::DROPDOWN_PROMO_BOTTOM,
            'label' => __('Dropdown products. Promo page bottom')
        ];
        return $result;
    }

    public function afterGetProductPositions(
        \Aheadworks\Autorelated\Model\Source\Position $subject,
        $result
    ) {
        $result[] = \Oander\Autorelated\Enum\BlockPosition::DROPDOWN_PROMO_BOTTOM;
        return $result;
    }
}