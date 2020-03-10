<?php

namespace Oander\OtpCalculator\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Oander\OtpCalculator\Helper
 */
class Config extends AbstractHelper
{
    const CONFIG_PATH = 'otp_calculator/general/';
    const PAYMENT_CONFIG_PATH = 'payment/bigfishpaymentgateway_pmgw_otparuhitel/';

    /**
     * @return bool
     */
    public function isProductEnabled(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::CONFIG_PATH.'product_enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getMinPrice(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_PATH.'min_price',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getMaxPrice(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_PATH.'max_price',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getRetailerId(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::PAYMENT_CONFIG_PATH.'retailer_id',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getTerm(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::PAYMENT_CONFIG_PATH.'term',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getConstructionGroup(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::PAYMENT_CONFIG_PATH.'construction_group',
            ScopeInterface::SCOPE_STORE
        );
    }
}
