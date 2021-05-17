<?php

namespace Oander\CustomerAddressValidation\Model;

use Oander\CustomerAddressValidation\Enum\Config;

class CustomerAddressValidationConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * CustomerAddressValidationConfigProvider constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfig()
    {
        $autofill = $this->scopeConfig->getValue(
            Config::AUTOFILL_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $output['isAutoFillCity'] = $autofill ? true : false;
        return $output;
    }
}