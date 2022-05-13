<?php

namespace Oander\ExternalRoundingUnit\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Oander\ExternalRoundingUnit\Helper\Config;

class DataConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Config
     */
    private $helperConfig;

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
    public function getConfig()
    {
        $config = [];
        $config['external_rounding_enabled'] = $this->helperConfig->IsEnabled();
        return $config;
    }
}