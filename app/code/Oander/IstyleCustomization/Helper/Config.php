<?php
/**
 * Oander_IstyleCustomization
 *
 * @author  Tamas Vegvari <tamas.vegvari@oander.hu>
 * @license Oander Media Kft. (http://www.oander.hu)
 */

declare(strict_types=1);

namespace Oander\IstyleCustomization\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 *
 * @package Oander\IstyleCustomization\Helper
 */
class Config extends AbstractHelper
{
    /**
     * @return bool
     */
    public function useTopmenuBlock()
    {
        return (bool)$this->scopeConfig->getValue(
            'oander_categories/topmenu/use_topmenu_block',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isSessionCheckerEnabled()
    {
        return (bool) $this->scopeConfig->getValue(
            'oander_session_checker/general/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return array
     */
    public function getSessionCheckerEmailReceivers(): array
    {
        $value = (string) $this->scopeConfig->getValue(
            'oander_session_checker/general/email_receivers',
            ScopeInterface::SCOPE_STORE
        );
        $value = explode(';', $value);

        return (array) array_filter($value);
    }

    /**
     * @return bool
     */
    public function isBasicDescriptionLazyLoadEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'oander_product_description_lazy_load/basic/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getBasicDescriptionMaxChars()
    {
        return (int)$this->scopeConfig->getValue(
            'oander_product_description_lazy_load/basic/max_chars',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getBasicDescriptionPostfix()
    {
        return (string)$this->scopeConfig->getValue(
            'oander_product_description_lazy_load/basic/postfix',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isRiverDescriptionLazyLoadEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'oander_product_description_lazy_load/river/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getRiverDescriptionEndTag()
    {
        return (string)$this->scopeConfig->getValue(
            'oander_product_description_lazy_load/river/river_end',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isWidgetDescriptionLazyLoadEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'oander_product_description_lazy_load/widget/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getWidgetDescriptionMaxBlocks()
    {
        return (int)$this->scopeConfig->getValue(
            'oander_product_description_lazy_load/widget/max_widget',
            ScopeInterface::SCOPE_STORE
        );
    }
}
