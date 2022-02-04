<?php
/**
 * Oander_DisabledProductPage
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

namespace Oander\DisabledProductPage\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Oander\DisabledProductPage\Enum\Config as ConfigEnum;

/**
 * Class Config
 * @package Oander\DisabledProductPage\Helper
 */
class Config extends AbstractHelper
{
    /**
     * @var array
     */
    protected $general;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;

        $this->general = (array)$this->scopeConfig->getValue(
            ConfigEnum::GENERAL_SETTINGS_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$value = $this->general[ConfigEnum::GENERAL_SETTINGS_ENABLED] ?? false;
    }

    /**
     * @return string
     */
    public function getSubstituteProductsTitle(): string
    {
        return (string)$value = $this->general[ConfigEnum::GENERAL_SETTINGS_SUBSTITUTE_PRODUCTS_TITLE] ?? '';
    }

    /**
     * @return string
     */
    public function getOutOfStockText(): string
    {
        return (string)$value = $this->general[ConfigEnum::GENERAL_SETTINGS_OUT_OF_STOCK_TEXT] ?? '';
    }

    /**
     * @return string
     */
    public function getIndexingRule(): string
    {
        return (string)$value = $this->general[ConfigEnum::GENERAL_SETTINGS_INDEXING_RULE] ?? 'INDEX,FOLLOW';
    }
}