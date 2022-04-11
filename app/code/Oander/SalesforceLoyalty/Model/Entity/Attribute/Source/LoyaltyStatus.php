<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Oander\SalesforceLoyalty\Model\Entity\Attribute\Source;

use Oander\SalesforceLoyalty\Enum\LoyaltyStatus as LoyaltyStatusEnum;

class LoyaltyStatus extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('Not Registered'), 'value' => LoyaltyStatusEnum::VALUE_NOT_REGISTERED],
                ['label' => __('Need SF Registration'), 'value' => LoyaltyStatusEnum::VALUE_NEED_SF_REGISTRATION],
                ['label' => __('Pending Registration'), 'value' => LoyaltyStatusEnum::VALUE_PENDING_REGISTRATION],
                ['label' => __('Registered'), 'value' => LoyaltyStatusEnum::VALUE_REGISTERED],
            ];
        }
        return $this->_options;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|int $value
     * @return string|false
     */
    public function getOptionText($value)
    {
        $options = $this->getAllOptions();
        foreach ($options as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}
