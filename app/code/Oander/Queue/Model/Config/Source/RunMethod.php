<?php

namespace Oander\Queue\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class RunMethod implements OptionSourceInterface
{
    const RUN_METHOD_CRON = 0;
    const RUN_METHOD_CONSOLE = 1;

    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            [
                "value" => self::RUN_METHOD_CRON,
                "label" => __("Cron")
            ],
            [
                "value" => self::RUN_METHOD_CONSOLE,
                "label" => __("Console")
            ]
        ];
    }
}