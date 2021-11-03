<?php
/**
 * Oander_News
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types = 1);

namespace Oander\SalesforceLoyalty\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Oander\News\Helper
 */
class Config extends AbstractHelper
{
    const SETTINGS_PATH_SPENDING            = 'oander_salesforce/salesforce_loyalty_spending';
    const SETTINGS_PATH_REGISTRATION        = 'oander_salesforce/salesforce_loyalty_registration';
    const SETTINGS_PATH_LOYALTY_SERVICE     = 'oander_salesforce/salesforce_loyalty_serivce';
    const SETTINGS_SPENDING_ENABLED         = 'enabled';
    const SETTINGS_SERVICE_ENABLED          = 'enabled';
    const SETTINGS_SPENDING_CARTINFO        = 'cart_info';
    const SETTINGS_SPENDING_MAXPERCENT      = 'max_percent';
    const SETTINGS_SPENDING_POINTVALUE      = 'point_value';
    const SETTINGS_REGISTRATION_TYPE        = 'type';

    /**
     * @var array
     */
    private $spending;
    /**
     * @var array
     */
    private $registration;
    /**
     * @var array
     */
    private $loyaltyService;
    /**
     * Config constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->spending = (array)$this->scopeConfig->getValue(
            self::SETTINGS_PATH_SPENDING,
            ScopeInterface::SCOPE_STORE
        );
        $this->registration = (array)$this->scopeConfig->getValue(
            self::SETTINGS_PATH_REGISTRATION,
            ScopeInterface::SCOPE_STORE
        );
        $this->loyaltyService = (array)$this->scopeConfig->getValue(
            self::SETTINGS_PATH_LOYALTY_SERVICE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isSpendingEnabled(): bool
    {
        return (bool)$value = $this->spending[self::SETTINGS_SPENDING_ENABLED] ?? false;
    }

    /**
     * @return string
     */
    public function getCartInfo(): string
    {
        return (string)$value = $this->spending[self::SETTINGS_SPENDING_CARTINFO] ?? '';
    }

    /**
     * @return int
     */
    public function getMaxPercent(): int
    {
        return (int)$value = $this->spending[self::SETTINGS_SPENDING_MAXPERCENT] ?? 0;
    }

    /**
     * @return float
     */
    public function getPointValue(): float
    {
        return $value = $this->spending[self::SETTINGS_SPENDING_POINTVALUE] ? floatval($this->spending[self::SETTINGS_SPENDING_POINTVALUE]) : 1.0;
    }

    /**
     * @return bool
     */
    public function getRegistrationTermType() : bool
    {
        return (bool)$value = $this->registration[self::SETTINGS_REGISTRATION_TYPE] ?? false;
    }

    /**
     * @return bool
     */
    public function getLoyaltyServiceEnabled() : bool
    {
        return (bool)$value = $this->loyaltyService[self::SETTINGS_SERVICE_ENABLED] ?? false;
    }
}
