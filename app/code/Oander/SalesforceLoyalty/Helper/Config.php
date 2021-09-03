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
    const SETTINGS_PATH_SPENDING = 'oander_salesforce/salesforce_loyalty_spending';
    const SETTINGS_SPENDING_ENABLED = 'enabled';
    const SETTINGS_SPENDING_CARTINFO = 'cart_info';
    const SETTINGS_SPENDING_MAXPERCENT = 'max_percent';
    const SETTINGS_SPENDING_POINTVALUE = 'point_value';

    /**
     * @var array
     */
    private $spending;

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
}