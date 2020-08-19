<?php

namespace Oander\CofidisPayment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Oander\CofidisPayment\Enum\Config as ConfigEnum;

/**
 * Class Config
 * @package Oander\MultiSlider\Helper
 */
class Config extends AbstractHelper
{
    const REDIRECT_URL = "cofidis/checkout/index";
    /**
     * @var array
     */
    protected $config;

    /**
     * Config constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);

        $this->config['current'] = (array)$this->scopeConfig->getValue(
            ConfigEnum::PAYMENT_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    private function getConfig($storeId = null)
    {
        if(is_numeric($storeId)) {
            if (!isset($this->config[$storeId])) {
                $this->config[$storeId] = (array)$this->scopeConfig->getValue(
                    ConfigEnum::PAYMENT_PATH,
                    ScopeInterface::SCOPE_STORE
                );
            }
            return $this->config[$storeId];
        }
        return $this->config['current'];
    }

    /**
     * @return bool
     */
    public function isEnabled($storeId = null): bool
    {
        return (bool)($this->getConfig($storeId)[ConfigEnum::ACTIVE] ?? false);
    }

    /**
     * @return bool
     */
    public function isCommandLine($storeId = null): bool
    {
        return (bool)($this->getConfig($storeId)[ConfigEnum::COMMANDLINE] ?? false);
    }

    /**
     * @return string
     */
    public function getInstructions($storeId = null): string
    {
        return (string)($this->getConfig($storeId)[ConfigEnum::INSTRUCTIONS] ?? "");
    }

    /**
     * @return int|null
     */
    public function getMinimumTotal($storeId = null)
    {
        return $this->getConfig($storeId)[ConfigEnum::MINIMUM_TOTAL];
    }

    /**
     * @return int|null
     */
    public function getMaximumTotal($storeId = null)
    {
        return $this->getConfig($storeId)[ConfigEnum::MAXIMUM_TOTAL];
    }

    /**
     * @return bool
     */
    public function isLive($storeId = null): bool
    {
        return (bool)(($this->getConfig($storeId)[ConfigEnum::ENVIRONMENT]==1) ?? false);
    }

    /**
     * @return string|null
     */
    public function getShopId($storeId = null)
    {
        if($this->isLive())
        {
            return $this->getConfig($storeId)[ConfigEnum::SHOP_ID];
        }
        else
        {
            return $this->getConfig($storeId)[ConfigEnum::SHOP_ID_TEST];
        }
    }

    /**
     * @return string|null
     */
    public function getIVCode($storeId = null)
    {
        if($this->isLive())
        {
            return $this->getConfig($storeId)[ConfigEnum::IV_CODE];
        }
        else
        {
            return $this->getConfig($storeId)[ConfigEnum::IV_CODE_TEST];
        }
    }

    /**
     * @return string|null
     */
    public function getKey($storeId = null)
    {
        if($this->isLive())
        {
            return $this->getConfig($storeId)[ConfigEnum::KEY];
        }
        else
        {
            return $this->getConfig($storeId)[ConfigEnum::KEY_TEST];
        }
    }

    /**
     * @return string|null
     */
    public function getConstructionGroup($storeId = null)
    {
        if($this->isLive())
        {
            return $this->getConfig($storeId)[ConfigEnum::CONSTRUCTION_GROUP];
        }
        else
        {
            return $this->getConfig($storeId)[ConfigEnum::CONSTRUCTION_GROUP_TEST];
        }
    }

    /**
     * @return string|null
     */
    public function getStatusUrl($storeId = null)
    {
        if($this->isLive())
        {
            return $this->getConfig($storeId)[ConfigEnum::STATUS_URL];
        }
        else
        {
            return $this->getConfig($storeId)[ConfigEnum::STATUS_URL_TEST];
        }
    }

    /**
     * @return string|null
     */
    public function getTermsUrl($storeId = null)
    {
        return $this->getConfig($storeId)[ConfigEnum::TERMS_URL];
    }

    /**
     * @return string|null
     */
    public function getRedirectPath($storeId = null)
    {
        return self::REDIRECT_URL;
    }
}