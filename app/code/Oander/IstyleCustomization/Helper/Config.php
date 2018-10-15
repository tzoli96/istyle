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
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

}
