<?php

namespace Oander\AppleServices\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Oander\AppleServices\Helper\Config;

class WidgetTypeOptions implements ArrayInterface
{
    /**
     * @var Config
     */
    protected $helperConfig;

    /**
     * @param Config $helperConfig
     */
    public function __construct(
        Config $helperConfig
    )
    {
        $this->helperConfig = $helperConfig;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        if ($this->helperConfig->musicIsEnabled()) {
            $options[] = [
                'value' => 'music',
                'label' => __('Apple Music')
            ];
        }

        if ($this->helperConfig->tvIsEnabled()) {
            $options[] = [
                'value' => 'tv',
                'label' => __('Apple Tv')
            ];
        }

        if ($this->helperConfig->icloudIsEnabled()) {
            $options[] = [
                'value' => 'icloud',
                'label' => __('Apple Icloud')
            ];
        }

        if ($this->helperConfig->arcadeIsEnabled()) {
            $options[] = [
                'value' => 'arcade',
                'label' => __('Apple Arcade')
            ];
        }

        return $options;
    }
}