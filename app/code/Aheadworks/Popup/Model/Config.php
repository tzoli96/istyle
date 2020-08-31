<?php
namespace Aheadworks\Popup\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Aheadworks\Acr\Model
 */
class Config
{
    /**#@+
     * Constants defined for scope config
     */
    const XML_PATH_HIDE_POPUP_FOR_SEARCH_ENGINES = 'aw_popup/general/hide_for_search_engines';
    const XML_PATH_HIDE_POPUP_FOR_MOBILE_DEVICES = 'aw_popup/general/hide_for_mobile_devices';
    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is hide popups for search engines
     *
     * @param null $storeId
     * @return bool
     */
    public function getHidePopupForSearchEngines($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_HIDE_POPUP_FOR_SEARCH_ENGINES,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is hide popups for mobile devices
     *
     * @param null $storeId
     * @return bool
     */
    public function getHidePopupForMobileDevices($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_HIDE_POPUP_FOR_MOBILE_DEVICES,
            ScopeInterface::SCOPE_STORE
        );
    }
}
