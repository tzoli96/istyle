<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Popup\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Position
 * @package Aheadworks\Popup\Model\Source
 */
class Position implements OptionSourceInterface
{
    const TOP_LEFT = 'top-left';
    const TOP_CENTER = 'top-center';
    const TOP_RIGHT = 'top-right';
    const MIDDLE_LEFT = 'middle-left';
    const MIDDLE_CENTER = 'middle-center';
    const MIDDLE_RIGHT = 'middle-right';
    const BOTTOM_LEFT = 'bottom-left';
    const BOTTOM_CENTER = 'bottom-center';
    const BOTTOM_RIGHT = 'bottom-right';

    const DEFAULT_VALUE = 'middle-center';

    /**
     * Get option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $newArray = [];
        $positions = $this->toOptionArray();

        foreach ($positions as $position) {
            $newArray[$position['value']] = $position['label'];
        }
        return $newArray;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TOP_LEFT,  'label' => __('Top left')],
            ['value' => self::TOP_CENTER,  'label' => __('Top center')],
            ['value' => self::TOP_RIGHT,  'label' => __('Top right')],
            ['value' => self::MIDDLE_LEFT,  'label' => __('Middle left')],
            ['value' => self::MIDDLE_CENTER,  'label' => __('Middle center')],
            ['value' => self::MIDDLE_RIGHT,  'label' => __('Middle right')],
            ['value' => self::BOTTOM_LEFT,  'label' => __('Bottom left')],
            ['value' => self::BOTTOM_CENTER,  'label' => __('Bottom center')],
            ['value' => self::BOTTOM_RIGHT,  'label' => __('Bottom right')],
        ];
    }
}
