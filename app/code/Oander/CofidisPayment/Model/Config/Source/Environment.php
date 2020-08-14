<?php
/**
 * Loan Payment modul for Cofidis
 * Copyright (C) 2019
 *
 * This file included in Oander/CofidisPayment is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Oander\CofidisPayment\Model\Config\Source;
use \Magento\Config\Model\Config\Source\Yesno;

class Environment implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 0, 'label' => __('Test')], ['value' => 1, 'label' => __('Live')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Test'), 1 => __('Live')];
    }
}