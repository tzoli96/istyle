<?php

namespace Oander\CustomerExtend\Model;

use \Magento\Checkout\Model\ConfigProviderInterface;
use Oander\CustomerExtend\Enum\Config as ConfigEnum;

class CheckoutConfigVars implements ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
    }


    public function getConfig()
    {
        if($this->_scopeConfig->isSetFlag(ConfigEnum::PATH_CUSTOMER_REPLACE_POSTCODE_REGION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return ["disablePostcodeWarning" => true];
        }
        else {
            return ["disablePostcodeWarning" => false];
        }
    }
}