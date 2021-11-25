<?php

namespace Oander\Cleaner\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ExecuteTime implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '0 0 * * *', 'label' => __('Horuly')],
            ['value' => '0 * * * *', 'label' => __('Daily')],
            ['value' => '0 0 1 * *', 'label' => __('Monthly')],
            ['value' => '0 0 1 */2 *', 'label' => __('2 Monthly')],
            ['value' => '0 0 1 */3 *', 'label' => __('3 Monthly')],
            ['value' => '0 0 1 */4 *', 'label' => __('4 Monthly')]
        ];
    }
}