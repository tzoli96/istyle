<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */


namespace Aheadworks\Popup\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 * @package Aheadworks\Popup\Model\Source
 */
class Status implements OptionSourceInterface
{
    /**
     * Status values
     */
    const STATUS_ENABLED = 1;

    const STATUS_DISABLED = 0;

    /**
     * Get option array
     *
     * @return array
     */
    public static function getOptionArray()
    {
        return [self::STATUS_ENABLED => __('Enable'), self::STATUS_DISABLED => __('Disable')];
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::STATUS_ENABLED,  'label' => __('Enabled')],
            ['value' => self::STATUS_DISABLED,  'label' => __('Disabled')],
        ];
    }

    /**
     * To option array for mass status
     *
     * @return array
     */
    public function toOptionArrayForMassStatus()
    {
        return [
            ['value' => self::STATUS_ENABLED,  'label' => __('Enable')],
            ['value' => self::STATUS_DISABLED,  'label' => __('Disable')],
        ];
    }
}
